<?php

require_once __DIR__ . '/../Models/employee/Employee.php';
require_once __DIR__ . '/../Services/PdfGeneratorService.php';

/**
 * DocumentController
 *
 * Handles downloading and streaming resolution documents for tickets.
 */
class DocumentController
{
    private $pdo;
    private $employeeModel;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
        $this->employeeModel = new Employee();
    }

    /**
     * Download ticket resolution document.
     *
     * GET /documents/download-ticket-pdf?id={ticket_id}
     *
     * @return void
     */
    public function downloadTicketPdf()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id'])) {
            header('Content-Type: text/plain');
            http_response_code(401);
            die('Unauthorized: Please log in first');
        }

        $ticketId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($ticketId <= 0) {
            header('Content-Type: text/plain');
            http_response_code(400);
            die('Error: Ticket ID not provided');
        }

        try {
            $ticket = $this->getTicketData($ticketId);

            if (!$ticket) {
                header('Content-Type: text/plain');
                http_response_code(404);
                die('Error: Ticket not found');
            }

            if (!$this->userCanViewTicket($ticket)) {
                header('Content-Type: text/plain');
                http_response_code(403);
                die('Error: Access denied to this ticket');
            }

            $pdfRecord = $this->getLatestPdf($ticketId);

            if ($pdfRecord) {
                $filePath = $this->resolveSafeFilePath($pdfRecord['pdf_path']);

                if ($filePath !== null && file_exists($filePath)) {
                    $this->logDownload($ticketId, (int) $_SESSION['account_id']);
                    $this->streamFile($filePath, $pdfRecord['pdf_filename']);
                    exit;
                }

                error_log("Stored document missing on disk for ticket {$ticketId}: {$pdfRecord['pdf_path']}");
            }

            $accountId = (int) $_SESSION['account_id'];
            $userRole = strtoupper((string) ($_SESSION['usertype'] ?? ''));
            $requesterId = (int) ($this->employeeModel->getEmployeeIdByAccountId($accountId) ?? 0);

            if ($requesterId <= 0 && $userRole === 'EMPLOYEE') {
                header('Content-Type: text/plain');
                http_response_code(401);
                die('Error: Employee record not found');
            }

            if ($requesterId <= 0) {
                $requesterId = (int) ($ticket['employee_id'] ?? 0);
            }

            $allowOverride = $userRole !== 'EMPLOYEE';
            $pdfService = new PdfGeneratorService();
            $result = $pdfService->generateTechnicalRecordDocx($ticketId, $requesterId, $allowOverride);

            if (!$result || empty($result['success'])) {
                header('Content-Type: text/plain');
                http_response_code(404);
                die('Error: No document found for this ticket. Please ensure the ticket is resolved.');
            }

            $filePath = $this->resolveGeneratedFilePath($result['filepath'] ?? '');
            $filename = $result['filename'] ?? 'technical_record.docx';

            if ($filePath === null || !file_exists($filePath)) {
                header('Content-Type: text/plain');
                http_response_code(404);
                die('Error: Document file not found on server');
            }

            $this->logDownload($ticketId, $accountId);
            $this->streamFile($filePath, $filename);
            exit;
        } catch (\Exception $e) {
            header('Content-Type: text/plain');
            http_response_code(500);
            error_log('Exception in DocumentController::downloadTicketPdf: ' . $e->getMessage());
            die('Error: Unable to download document. Please try again later.');
        }
    }

    /**
     * @param int $ticketId
     * @return array|false
     */
    private function getTicketData($ticketId)
    {
        $sql = "SELECT ticket_id, ticket_number, employee_id, assigned_to, status, department, branch_id, created_by
                FROM tbltickets WHERE ticket_id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$ticketId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ?: false;
        } catch (PDOException $e) {
            error_log('Database error in getTicketData: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param int $ticketId
     * @return array|false
     */
    private function getLatestPdf($ticketId)
    {
        $sql = "SELECT pdf_id, pdf_filename, pdf_path, date_generated
                FROM tblticket_pdfs
                WHERE ticket_id = ? AND is_active = 1
                ORDER BY date_generated DESC
                LIMIT 1";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$ticketId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ?: false;
        } catch (PDOException $e) {
            error_log('Database error in getLatestPdf: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Permission rules aligned with RealtimeModel ticket scope.
     *
     * @param array $ticket
     * @return bool
     */
    private function userCanViewTicket(array $ticket)
    {
        $accountId = (int) $_SESSION['account_id'];
        $userRole = strtoupper((string) ($_SESSION['usertype'] ?? ''));

        if (in_array($userRole, ['ADMIN', 'IT'], true)) {
            return true;
        }

        if ($userRole === 'EMPLOYEE') {
            $employeeId = $this->employeeModel->getEmployeeIdByAccountId($accountId);
            if ($employeeId === null) {
                return false;
            }

            return (int) $ticket['employee_id'] === $employeeId
                || (int) ($ticket['created_by'] ?? 0) === $accountId;
        }

        if (in_array($userRole, ['HEAD', 'HR'], true)) {
            $department = $this->getDepartmentByAccountId($accountId);
            if ($department === null || $department === '') {
                return false;
            }

            return strcasecmp((string) ($ticket['department'] ?? ''), $department) === 0;
        }

        if (in_array($userRole, ['OM', 'HOM'], true)) {
            return (int) ($ticket['created_by'] ?? 0) === $accountId;
        }

        if ($userRole === 'AOM') {
            return $this->aomCanViewTicket($accountId, $ticket);
        }

        return false;
    }

    /**
     * @param int $accountId
     * @return string|null
     */
    private function getDepartmentByAccountId(int $accountId): ?string
    {
        try {
            $stmt = $this->pdo->prepare('SELECT department FROM tblemployee WHERE account_id = ? LIMIT 1');
            $stmt->execute([$accountId]);
            $val = $stmt->fetchColumn();

            return $val !== false ? (string) $val : null;
        } catch (PDOException $e) {
            error_log('Database error in getDepartmentByAccountId: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @param int $accountId
     * @param array $ticket
     * @return bool
     */
    private function aomCanViewTicket(int $accountId, array $ticket): bool
    {
        $aomEmployeeId = $this->employeeModel->getEmployeeIdByAccountId($accountId);
        if ($aomEmployeeId === null) {
            return false;
        }

        $sql = "SELECT 1
                FROM tbltickets t
                WHERE t.ticket_id = ?
                  AND (
                    t.branch_id IN (
                        SELECT branch_id FROM tblbranch_assignments
                        WHERE aom_employee_id = ? AND is_active = 1
                    )
                    OR t.employee_id IN (
                        SELECT employee_id FROM tblhom_employee_assignments
                        WHERE aom_id = ? AND is_active = 1
                    )
                  )
                LIMIT 1";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int) $ticket['ticket_id'], $aomEmployeeId, $aomEmployeeId]);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Database error in aomCanViewTicket: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Restrict downloads to files under /assets/tickets/pdfs/.
     *
     * @param string $relativePath
     * @return string|null
     */
    private function resolveSafeFilePath(string $relativePath): ?string
    {
        $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
        $relativePath = '/' . ltrim(str_replace('\\', '/', $relativePath), '/');

        if (strpos($relativePath, '/assets/tickets/pdfs/') !== 0) {
            return null;
        }

        $fullPath = $docRoot . $relativePath;
        $realPath = realpath($fullPath);
        $allowedDir = realpath($docRoot . '/assets/tickets/pdfs');

        if ($realPath === false || $allowedDir === false) {
            return null;
        }

        if (strpos(str_replace('\\', '/', $realPath), str_replace('\\', '/', $allowedDir)) !== 0) {
            return null;
        }

        return $realPath;
    }

    /**
     * Restrict on-demand generated files to the tickets PDF output directory.
     *
     * @param string $absolutePath
     * @return string|null
     */
    private function resolveGeneratedFilePath(string $absolutePath): ?string
    {
        if ($absolutePath === '') {
            return null;
        }

        $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
        $allowedDir = realpath($docRoot . '/assets/tickets/pdfs');
        $realPath = realpath($absolutePath);

        if ($realPath === false || $allowedDir === false) {
            return null;
        }

        if (strpos(str_replace('\\', '/', $realPath), str_replace('\\', '/', $allowedDir)) !== 0) {
            return null;
        }

        return $realPath;
    }

    /**
     * @param int $ticketId
     * @param int $userId
     * @return void
     */
    private function logDownload($ticketId, $userId)
    {
        $logDir = dirname(__DIR__) . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/pdf_downloads.log';
        $timestamp = date('Y-m-d H:i:s');
        error_log("[{$timestamp}] User {$userId} - Ticket {$ticketId}\n", 3, $logFile);
    }

    /**
     * @param string $filePath
     * @param string $filename
     * @return void
     */
    private function streamFile($filePath, $filename)
    {
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        $contentType = $this->getContentType($fileExtension);
        $fileSize = filesize($filePath);

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . $fileSize);
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');

        if (ob_get_level()) {
            ob_end_clean();
        }

        readfile($filePath);
    }

    /**
     * @param string $extension
     * @return string
     */
    private function getContentType($extension)
    {
        $types = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc' => 'application/msword',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        return $types[strtolower($extension)] ?? 'application/octet-stream';
    }
}
