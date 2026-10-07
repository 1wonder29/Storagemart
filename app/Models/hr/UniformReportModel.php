<?php

require_once __DIR__ . '/UniformModel.php';

/**
 * Employee reports of lost or damaged issued items (uniforms, ID badges, etc.).
 *
 * Flow: employee files a PENDING report -> HR either CONFIRMS it (the quantity is
 * moved to the item's lost/damaged counts and the issuance is closed or reduced) or
 * marks the item OK (the issuance stays active with the employee).
 */
class UniformReportModel extends UniformModel {

    protected $tblreports = 'tbluniform_reports';

    public const TYPES = ['LOST', 'DAMAGED'];

    public function __construct() {
        parent::__construct();
        $this->ensureReportTable();
    }

    private function ensureReportTable(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS {$this->tblreports} (
            report_id INT(11) NOT NULL AUTO_INCREMENT,
            assignment_id INT(11) NOT NULL,
            uniform_id INT(11) NOT NULL,
            employee_id INT(11) NOT NULL,
            report_type ENUM('LOST','DAMAGED') NOT NULL,
            quantity INT(11) NOT NULL DEFAULT 1,
            description TEXT,
            status ENUM('PENDING','CONFIRMED','ITEM_OK') NOT NULL DEFAULT 'PENDING',
            hr_remarks TEXT,
            reviewed_by INT(11) DEFAULT NULL,
            reviewed_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (report_id),
            KEY idx_status (status),
            KEY idx_employee (employee_id),
            KEY idx_assignment (assignment_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    /** Items currently issued to the employee (not yet returned), with any open report. */
    public function getActiveIssuancesForEmployee(int $employeeId): array
    {
        $sql = "SELECT ua.assignment_id, ua.uniform_id, ua.quantity_issued, ua.date_issued,
                       ua.condition_upon_issue, ui.uniform_type, ui.size, ui.color,
                       (SELECT r.report_type FROM {$this->tblreports} r
                         WHERE r.assignment_id = ua.assignment_id AND r.status = 'PENDING'
                         ORDER BY r.report_id DESC LIMIT 1) AS pending_report_type
                FROM {$this->tbluniform_assignment} ua
                JOIN {$this->tbluniform_inventory} ui ON ui.uniform_id = ua.uniform_id
                WHERE ua.employee_id = ? AND ua.date_returned IS NULL AND ua.quantity_issued > 0
                ORDER BY ua.date_issued DESC, ua.assignment_id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getReportsForEmployee(int $employeeId, int $limit = 50): array
    {
        $sql = "SELECT r.*, ui.uniform_type, ui.size
                FROM {$this->tblreports} r
                JOIN {$this->tbluniform_inventory} ui ON ui.uniform_id = r.uniform_id
                WHERE r.employee_id = ?
                ORDER BY r.report_id DESC
                LIMIT " . max(1, min($limit, 200));
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array{0: bool, 1: string, 2: int} [ok, message, reportId]
     */
    public function createReport(int $employeeId, int $assignmentId, string $type, int $quantity, string $description): array
    {
        $type = strtoupper(trim($type));
        if (!in_array($type, self::TYPES, true)) {
            return [false, 'Please choose Lost or Damaged.', 0];
        }

        $assignment = $this->getAssignmentById($assignmentId);
        if (!$assignment || (int) $assignment['employee_id'] !== $employeeId) {
            return [false, 'This item is not issued to you.', 0];
        }
        if (!empty($assignment['date_returned'])) {
            return [false, 'This item has already been returned.', 0];
        }

        $issued = (int) ($assignment['quantity_issued'] ?? 0);
        if ($quantity < 1 || $quantity > $issued) {
            return [false, "Quantity must be between 1 and {$issued}.", 0];
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$this->tblreports} WHERE assignment_id = ? AND status = 'PENDING'");
        $stmt->execute([$assignmentId]);
        if ((int) $stmt->fetchColumn() > 0) {
            return [false, 'You already have a pending report for this item. Please wait for HR to review it.', 0];
        }

        $stmt = $this->pdo->prepare("INSERT INTO {$this->tblreports}
                (assignment_id, uniform_id, employee_id, report_type, quantity, description, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'PENDING', NOW())");
        $ok = $stmt->execute([
            $assignmentId,
            (int) $assignment['uniform_id'],
            $employeeId,
            $type,
            $quantity,
            trim($description),
        ]);

        return $ok
            ? [true, 'Report sent to HR.', (int) $this->pdo->lastInsertId()]
            : [false, 'Could not save the report. Please try again.', 0];
    }

    public function getReports(?string $status = null, ?int $reportId = null, ?int $employeeId = null): array
    {
        $params = [];
        $conditions = [];
        if ($status !== null && $status !== '') {
            $conditions[] = 'r.status = ?';
            $params[] = strtoupper($status);
        }
        if ($reportId !== null) {
            $conditions[] = 'r.report_id = ?';
            $params[] = $reportId;
        }
        if ($employeeId !== null) {
            $conditions[] = 'r.employee_id = ?';
            $params[] = $employeeId;
        }
        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $sql = "SELECT r.*, ui.uniform_type, ui.size, ui.color,
                       ua.quantity_issued, ua.date_returned,
                       CONCAT(e.firstname, ' ', e.lastname) AS employee_name,
                       e.department, e.email AS employee_email, e.account_id AS employee_account_id,
                       acc.usertype AS employee_usertype
                FROM {$this->tblreports} r
                JOIN {$this->tbluniform_inventory} ui ON ui.uniform_id = r.uniform_id
                LEFT JOIN {$this->tbluniform_assignment} ua ON ua.assignment_id = r.assignment_id
                LEFT JOIN {$this->tblemployee} e ON e.employee_id = r.employee_id
                LEFT JOIN {$this->tblaccounts} acc ON acc.account_id = e.account_id
                {$where}
                ORDER BY (r.status = 'PENDING') DESC, r.report_id DESC
                LIMIT 300";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function countPendingReports(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM {$this->tblreports} WHERE status = 'PENDING'")->fetchColumn();
    }

    public function getReportById(int $reportId): ?array
    {
        $rows = $this->getReports(null, $reportId);
        return $rows[0] ?? null;
    }

    /**
     * HR decision on a pending report.
     * CONFIRMED: moves the reported quantity to lost/damaged and closes/reduces the issuance.
     * ITEM_OK:   leaves the issuance active with the employee.
     *
     * The report is claimed (PENDING -> decision) before anything else happens, so when two
     * reviewers act at once (HR and the General Manager both get the notification) only one
     * of them applies it; if the inventory update then fails the claim is undone.
     *
     * @return array{0: bool, 1: string, 2: int} [ok, message, quantity written off]
     */
    public function resolveReport(int $reportId, string $decision, string $hrRemarks, int $reviewedBy): array
    {
        $decision = strtoupper(trim($decision));
        if (!in_array($decision, ['CONFIRMED', 'ITEM_OK'], true)) {
            return [false, 'Invalid decision.', 0];
        }

        $report = $this->getReportById($reportId);
        if (!$report) {
            return [false, 'Report not found.', 0];
        }

        $claim = $this->pdo->prepare("UPDATE {$this->tblreports}
                SET status = ?, hr_remarks = ?, reviewed_by = ?, reviewed_at = NOW()
                WHERE report_id = ? AND status = 'PENDING'");
        $claim->execute([$decision, trim($hrRemarks), $reviewedBy, $reportId]);
        if ($claim->rowCount() !== 1) {
            return [false, 'This report was already reviewed.', 0];
        }

        if ($decision === 'ITEM_OK') {
            return [true, 'Report closed. The item stays active with the employee.', 0];
        }

        $failure = null;
        $qty = 0;
        if (!empty($report['date_returned'])) {
            $failure = 'The item was already returned; mark the report as OK instead.';
        } else {
            $qty = min((int) $report['quantity'], (int) ($report['quantity_issued'] ?? 0));
            if ($qty < 1) {
                $failure = 'Nothing left on this issuance to mark as ' . strtolower($report['report_type']) . '.';
            }
        }

        if ($failure === null) {
            $remarks = sprintf(
                'Employee reported %d %s on %s. Confirmed by HR.%s',
                $qty,
                strtolower($report['report_type']),
                date('F j, Y', strtotime((string) $report['created_at'])),
                $hrRemarks !== '' ? ' ' . $hrRemarks : ''
            );
            // returnAssignment runs its own transaction (locking the issuance row) and updates inventory counts.
            if (!$this->returnAssignment(
                (int) $report['assignment_id'],
                $reviewedBy,
                $report['report_type'],
                $remarks,
                [$report['report_type'] => $qty]
            )) {
                $failure = 'Could not update the inventory for this report.';
            }
        }

        if ($failure !== null) {
            $undo = $this->pdo->prepare("UPDATE {$this->tblreports}
                    SET status = 'PENDING', hr_remarks = NULL, reviewed_by = NULL, reviewed_at = NULL
                    WHERE report_id = ? AND status = ?");
            $undo->execute([$reportId, $decision]);
            return [false, $failure, 0];
        }

        if ($qty !== (int) $report['quantity']) {
            // Keep the report truthful when fewer units were left on the issuance than were reported.
            $this->pdo->prepare("UPDATE {$this->tblreports} SET quantity = ? WHERE report_id = ?")
                ->execute([$qty, $reportId]);
        }

        return [true, 'Report confirmed. Inventory updated.', $qty];
    }

    public function hasPendingReport(int $assignmentId): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$this->tblreports} WHERE assignment_id = ? AND status = 'PENDING'");
        $stmt->execute([$assignmentId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Active items HR can hand out as a replacement, with stock on hand. */
    public function getReplacementStock(): array
    {
        $stmt = $this->pdo->query("SELECT uniform_id, uniform_type, size, color, quantity_in_stock
                FROM {$this->tbluniform_inventory}
                WHERE status = 'ACTIVE' AND quantity_in_stock > 0
                ORDER BY uniform_type, size");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Issue a replacement to the reporter after HR confirmed a report.
     *
     * @return array{0: bool, 1: string} [ok, label or reason]
     */
    public function issueReplacement(array $report, int $uniformId, int $quantity, int $issuedBy): array
    {
        $uniform = $this->getUniformById($uniformId);
        if (!$uniform) {
            return [false, 'the replacement item was not found'];
        }
        $label = trim($uniform['uniform_type'] . ' (' . $uniform['size'] . ')');
        if (strtoupper((string) ($uniform['status'] ?? 'ACTIVE')) !== 'ACTIVE') {
            return [false, $label . ' is discontinued'];
        }
        if ($quantity < 1) {
            return [false, 'the replacement quantity must be at least 1'];
        }
        if ((int) $uniform['quantity_in_stock'] < $quantity) {
            return [false, 'only ' . (int) $uniform['quantity_in_stock'] . ' x ' . $label . ' in stock'];
        }

        $ok = $this->assignUniform(
            (int) $report['employee_id'],
            $uniformId,
            $quantity,
            'GOOD',
            sprintf('Replacement for %s report #%d.', strtolower((string) $report['report_type']), (int) $report['report_id']),
            $issuedBy
        );
        return $ok ? [true, $quantity . ' x ' . $label] : [false, $label . ' could not be issued (stock changed)'];
    }

    /** Active HR accounts plus full-access admins (General Manager), who review reports. */
    public function getReviewerAccountIds(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT account_id FROM {$this->tblaccounts}
                    WHERE UPPER(status) = 'ACTIVE'
                      AND (UPPER(usertype) = 'HR' OR (UPPER(usertype) = 'ADMIN' AND is_superuser = 1))");
        } catch (\Throwable $e) {
            // is_superuser not migrated yet: HR only.
            $stmt = $this->pdo->query("SELECT account_id FROM {$this->tblaccounts}
                    WHERE UPPER(usertype) = 'HR' AND UPPER(status) = 'ACTIVE'");
        }
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    /** The reporter's own "My Issued Items" page for their role. */
    public static function itemsPathForUsertype(?string $usertype): string
    {
        $prefix = [
            'HEAD' => 'head', 'AOM' => 'aom', 'HOM' => 'hom', 'OM' => 'om', 'ADMIN' => 'admin', 'HR' => 'hr', 'IT' => 'it',
        ][strtoupper((string) $usertype)] ?? 'employee';
        return '/' . $prefix . '/items';
    }
}
