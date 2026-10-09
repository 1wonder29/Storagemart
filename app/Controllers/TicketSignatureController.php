<?php

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Helpers/TicketSignatures.php';
require_once __DIR__ . '/../Helpers/TicketAccess.php';
require_once __DIR__ . '/../Helpers/ActivityLogger.php';
require_once __DIR__ . '/../Models/NotificationModel.php';
require_once __DIR__ . '/../Services/PdfGeneratorService.php';

/**
 * /tickets/signature: sign the Technical Summary Report digitally (draw or upload a signature,
 * place it in your box), download the signed PDF, or print it to sign by hand.
 */
class TicketSignatureController extends AuthController
{
    private const SIGNABLE_STATUSES = ['resolved', 'closed'];

    /** GET /tickets/signature?id= */
    public function show(): void
    {
        [$facts, $slots] = $this->load((int) ($_GET['id'] ?? 0));
        global $pdo;
        $report = (new PdfGeneratorService($pdo))->renderTechnicalRecord((int) $facts['ticket_id']);
        if (!$report) {
            $this->fail('The technical report is not available for this ticket yet.');
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $csrf_token = $_SESSION['csrf_token'];
        $usertype = strtoupper((string) ($_SESSION['usertype'] ?? 'EMPLOYEE'));
        $backUrl = rtrim(BASE_URL, '/') . (new NotificationModel())->getTicketViewUrlForRole($usertype, (int) $facts['ticket_id']);
        $tableReady = TicketSignatures::tableExists($pdo);
        $signatures = $report['signatures'];
        require __DIR__ . '/../Views/ticket/signature.php';
    }

    /** POST /tickets/signature/save (JSON): ticket_id, slot, image, x, y, scale */
    public function save(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->requireCsrfJson();
        [$facts, $slots] = $this->load((int) ($_POST['ticket_id'] ?? 0), true);
        global $pdo;
        $slot = strtoupper((string) ($_POST['slot'] ?? ''));
        if (!in_array($slot, $slots, true)) {
            $this->json(403, 'You cannot sign this box.');
        }
        $image = TicketSignatures::cleanImage((string) ($_POST['image'] ?? ''));
        if ($image === null) {
            $this->json(400, 'The signature image could not be read. Draw it again or upload a clear photo.');
        }
        $place = TicketSignatures::placement((int) ($_POST['x'] ?? 0), (int) ($_POST['y'] ?? 0), (int) ($_POST['scale'] ?? 100));
        $name = $this->signerName($pdo, (int) $_SESSION['account_id'], $slot, $facts);
        try {
            $pdo->prepare('INSERT INTO tblticket_signatures (ticket_id, slot, account_id, signer_name, image_data, offset_x, offset_y, scale_pct, signed_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE account_id = VALUES(account_id), signer_name = VALUES(signer_name), image_data = VALUES(image_data),
                        offset_x = VALUES(offset_x), offset_y = VALUES(offset_y), scale_pct = VALUES(scale_pct), signed_at = NOW()')
                ->execute([(int) $facts['ticket_id'], $slot, (int) $_SESSION['account_id'], $name, $image, $place['x'], $place['y'], $place['scale']]);
        } catch (Throwable $e) {
            error_log('Signature save failed: ' . $e->getMessage());
            $this->json(500, 'The signature could not be saved. Please run the database update first.');
        }
        ActivityLogger::update('Tickets', (string) $facts['ticket_id'],
            'Signed the technical report (' . TicketSignatures::SLOTS[$slot] . ') for ' . $facts['ticket_number'], $_SESSION['username'] ?? 'Unknown');
        $this->notifyOthers($pdo, $facts, $slot, $name);
        echo json_encode(['success' => true, 'message' => 'Signature saved.']);
        exit;
    }

    /** POST /tickets/signature/remove (JSON): ticket_id, slot */
    public function remove(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->requireCsrfJson();
        [$facts, $slots] = $this->load((int) ($_POST['ticket_id'] ?? 0), true);
        global $pdo;
        $slot = strtoupper((string) ($_POST['slot'] ?? ''));
        if (!in_array($slot, $slots, true)) {
            $this->json(403, 'You cannot change this box.');
        }
        $pdo->prepare('DELETE FROM tblticket_signatures WHERE ticket_id = ? AND slot = ?')->execute([(int) $facts['ticket_id'], $slot]);
        echo json_encode(['success' => true, 'message' => 'Signature removed.']);
        exit;
    }

    /** GET /tickets/signature/pdf?id= */
    public function pdf(): void
    {
        [$facts] = $this->load((int) ($_GET['id'] ?? 0));
        global $pdo;
        $result = (new PdfGeneratorService($pdo))->generateSignedPdf((int) $facts['ticket_id']);
        if (!$result) {
            $this->fail('The PDF could not be created. Use Print instead.');
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
        header('Content-Length: ' . strlen($result['content']));
        echo $result['content'];
        exit;
    }

    /** @return array{0: array, 1: string[]} ticket facts and the boxes this user may sign */
    private function load(int $ticketId, bool $json = false): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $accountId = (int) ($_SESSION['account_id'] ?? 0);
        if ($accountId <= 0) {
            $json ? $this->json(401, 'Please log in again.') : $this->redirect('/login');
            exit;
        }
        global $pdo;
        $facts = $ticketId > 0 ? TicketSignatures::ticketFacts($pdo, $ticketId) : null;
        $realRole = strtoupper((string) ($_SESSION['superuser_real_usertype'] ?? $_SESSION['usertype'] ?? ''));
        $canView = false;
        if ($facts) {
            $row = TicketAccess::fetchTicketRow($pdo, $ticketId);
            $canView = ($row && TicketAccess::canViewTicket($pdo, $row, $accountId, strtoupper((string) ($_SESSION['usertype'] ?? ''))))
                || (int) $facts['requester_account_id'] === $accountId
                || (int) $facts['created_by'] === $accountId
                || $realRole === 'ADMIN';
        }
        if (!$facts || !$canView) {
            $json ? $this->json(404, 'Ticket not found.') : $this->fail('Ticket not found.');
        }
        if (!in_array(strtolower((string) $facts['status']), self::SIGNABLE_STATUSES, true)) {
            $json ? $this->json(400, 'The report can be signed once the ticket is resolved.')
                : $this->fail('The technical report can be signed once the ticket is resolved.');
        }
        return [$facts, TicketSignatures::slotsFor($pdo, $facts, $accountId, $realRole)];
    }

    private function signerName(PDO $pdo, int $accountId, string $slot, array $facts): string
    {
        $stmt = $pdo->prepare("SELECT TRIM(CONCAT(COALESCE(firstname, ''), ' ', COALESCE(lastname, ''))) FROM tblemployee WHERE account_id = ? LIMIT 1");
        $stmt->execute([$accountId]);
        $name = trim((string) $stmt->fetchColumn());
        if ($name === '' || stripos($name, 'admin') === 0) {
            $name = $slot === 'VERIFIED' ? 'Kenneth Dador' : ($slot === 'ACKNOWLEDGED' ? (string) $facts['requester_name'] : 'IT');
        }
        return mb_substr($name, 0, 150);
    }

    /** Tell the other people on the report that a box was signed. */
    private function notifyOthers(PDO $pdo, array $facts, string $slot, string $name): void
    {
        $ticketId = (int) $facts['ticket_id'];
        $targets = [];
        if ($slot !== 'ACKNOWLEDGED' && (int) $facts['requester_account_id'] > 0) {
            $targets[(int) $facts['requester_account_id']] = 'EMPLOYEE';
        }
        $itEmployee = (int) ($facts['performed_by'] ?: $facts['assigned_to']);
        if ($slot !== 'REPORTED' && $itEmployee > 0) {
            $stmt = $pdo->prepare('SELECT account_id FROM tblemployee WHERE employee_id = ? LIMIT 1');
            $stmt->execute([$itEmployee]);
            $acc = (int) $stmt->fetchColumn();
            if ($acc > 0) {
                $targets[$acc] = 'IT';
            }
        }
        unset($targets[(int) $_SESSION['account_id']]);
        $notifications = new NotificationModel();
        foreach ($targets as $acc => $unused) {
            $notifications->create($acc, $name . ' signed the technical report of ticket ' . $facts['ticket_number'] . ' (' . TicketSignatures::SLOTS[$slot] . ').',
                'fa-signature', 'success', '/tickets/signature?id=' . $ticketId, $ticketId);
        }
    }

    private function requireCsrfJson(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $posted = (string) ($_POST['csrf_token'] ?? '');
        if ($posted === '' || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $posted)) {
            $this->json(403, 'Your session expired. Reload the page and try again.');
        }
    }

    private function json(int $code, string $message): void
    {
        http_response_code($code);
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }

    private function fail(string $message): void
    {
        http_response_code(404);
        echo '<!DOCTYPE html><meta charset="utf-8"><title>Technical report</title><body style="font-family:sans-serif;padding:40px">'
            . '<p>' . htmlspecialchars($message) . '</p><p><a href="javascript:history.back()">Go back</a></p></body>';
        exit;
    }
}
