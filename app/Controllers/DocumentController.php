<?php

require_once __DIR__ . '/../Models/admin/Ticket.php';

/**
 * DocumentController
 * 
 * Handles downloading and streaming PDF/document files for tickets
 */
class DocumentController
{
    private $ticketModel;
    private $pdo;
    
    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
        $this->ticketModel = new Ticket();
    }
    
    /**
     * Download ticket resolution PDF
     * 
     * GET /documents/download-ticket-pdf/{ticket_id}
     * 
     * Validates user has permission to view the ticket, then streams the PDF file
     * 
     * @return void
     */
    public function downloadTicketPdf()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check authentication
        if (empty($_SESSION['account_id'])) {
            header('Content-Type: text/plain');
            http_response_code(401);
            die('Unauthorized: Please log in first');
        }
        
        // Get ticket ID from request
        $ticketId = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        if (!$ticketId) {
            header('Content-Type: text/plain');
            http_response_code(400);
            die('Error: Ticket ID not provided');
        }
        
        try {
            // Fetch ticket to validate access
            $ticket = $this->getTicketData($ticketId);
            
            if (!$ticket) {
                header('Content-Type: text/plain');
                http_response_code(404);
                die('Error: Ticket not found');
            }
            
            // Validate user access to this ticket
            if (!$this->userCanViewTicket($ticketId)) {
                header('Content-Type: text/plain');
                http_response_code(403);
                die('Error: Access denied to this ticket');
            }
            
            // Get latest PDF for this ticket
            $pdfRecord = $this->getLatestPdf($ticketId);
            
            if (!$pdfRecord) {
                header('Content-Type: text/plain');
                http_response_code(404);
                die('Error: No PDF found for this ticket. Please ensure the ticket is resolved and a PDF has been generated.');
            }
            
            // Build full file path
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $pdfRecord['pdf_path'];
            
            // Verify file exists
            if (!file_exists($filePath)) {
                header('Content-Type: text/plain');
                http_response_code(404);
                error_log("PDF file not found at path: {$filePath}");
                die('Error: PDF file not found on server at path: ' . $pdfRecord['pdf_path']);
            }
            
            // Log download event
            $this->logDownload($ticketId, $_SESSION['account_id']);
            
            // Stream the file
            $this->streamFile($filePath, $pdfRecord['pdf_filename']);
            exit;
            
        } catch (\Exception $e) {
            header('Content-Type: text/plain');
            http_response_code(500);
            error_log("Exception in DocumentController::downloadTicketPdf: " . $e->getMessage());
            die('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Get ticket data from database
     * 
     * @param int $ticketId Ticket ID
     * @return array|false Ticket data or false
     */
    private function getTicketData($ticketId)
    {
        $sql = "SELECT ticket_id, ticket_number, employee_id, assigned_to, status 
                FROM tbltickets WHERE ticket_id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$ticketId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ?: false;
        } catch (PDOException $e) {
            error_log("Database error in getTicketData: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get latest PDF record for a ticket
     * 
     * @param int $ticketId Ticket ID
     * @return array|false PDF record data or false
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
            error_log("Database error in getLatestPdf: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if current user can view/download this ticket
     * 
     * Permission rules:
     * - Admin can view all tickets
     * - IT staff can view tickets assigned to them
     * - Employee can view their own tickets
     * - Head can view tickets from their department
     * 
     * @param int $ticketId Ticket ID
     * @return bool True if user can access, false otherwise
     */
    private function userCanViewTicket($ticketId)
    {
        $userId = $_SESSION['account_id'];
        $userRole = strtoupper($_SESSION['usertype'] ?? '');  // Normalize to uppercase
        
        // Get ticket info
        $ticket = $this->getTicketData($ticketId);
        if (!$ticket) return false;
        
        // Admin can view all
        if ($userRole === 'ADMIN') {
            return true;
        }
        
        // IT staff - can view all tickets or tickets assigned to them
        if ($userRole === 'IT') {
            // Allow if assigned to this IT staff, or allow IT to view all
            return true;  // Simplified: all IT staff can download
        }
        
        // Employee - can view their own tickets
        if ($userRole === 'EMPLOYEE') {
            return (intval($ticket['employee_id']) == intval($userId));
        }
        
        // Head - can view tickets from employees in their department
        if ($userRole === 'HEAD') {
            $sql = "SELECT e.employee_id FROM tblemployee e
                    JOIN tblemployee head_emp ON e.branch_id = head_emp.branch_id
                    WHERE head_emp.employee_id = ? AND e.employee_id = ?";
            
            try {
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$userId, $ticket['employee_id']]);
                $canView = $stmt->rowCount() > 0;
                return $canView;
            } catch (PDOException $e) {
                error_log("Database error in userCanViewTicket: " . $e->getMessage());
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Log PDF download for audit trail
     * 
     * @param int $ticketId Ticket ID
     * @param int $userId User ID who downloaded
     * @return void
     */
    private function logDownload($ticketId, $userId)
    {
        global $link;
        
        $action = 'PDF_DOWNLOADED';
        $logMessage = "User {$userId} downloaded PDF for ticket {$ticketId}";
        
        // Optional: Insert into audit log table if you have one
        // For now, just log to file
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/../logs/pdf_downloads.log';
        $timestamp = date('Y-m-d H:i:s');
        error_log("[{$timestamp}] User {$userId} - Ticket {$ticketId}\n", 3, $logFile);
    }
    
    /**
     * Stream file to browser for download
     * 
     * @param string $filePath Full path to file
     * @param string $filename Filename to show in download dialog
     * @return void
     */
    private function streamFile($filePath, $filename)
    {
        // Determine content type
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        $contentType = $this->getContentType($fileExtension);
        
        // Get file size
        $fileSize = filesize($filePath);
        
        // Send headers
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . $fileSize);
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        
        // Clear output buffer and read file
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        readfile($filePath);
    }
    
    /**
     * Get MIME content type for file extension
     * 
     * @param string $extension File extension
     * @return string MIME type
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
?>
