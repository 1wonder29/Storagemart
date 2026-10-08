<?php
require_once 'BaseModel.php';

class Account extends BaseModel {

    protected $table = 'tblaccounts';
    protected $tblemployee = 'tblemployee';
    protected $tbltickets = 'tbltickets';
    protected $tblassets = 'tblassets_inventory';
    protected $tblbranch = 'tblbranch';
    protected $tblgroup = 'tblassets_group';
    protected $tblassign = 'tblassets_assignment';

    // -----------------------
    // Simple lookups
    // -----------------------
    public function findByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Account for a registered email (accounts sign in with their email, so the username may match too). */
    public function findByEmail(string $email): ?array {
        $sql = "SELECT a.*
                FROM {$this->table} a
                INNER JOIN {$this->tblemployee} e ON e.account_id = a.account_id
                WHERE LOWER(e.email) = LOWER(:email) OR LOWER(a.username) = LOWER(:username_email)
                ORDER BY (LOWER(e.email) = LOWER(:email_rank)) DESC, a.account_id ASC
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':username_email' => $email,
            ':email_rank' => $email,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updatePasswordByAccountId(int $accountId, string $passwordHash): bool {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET password = :password WHERE account_id = :account_id LIMIT 1");
        return $stmt->execute([
            ':password' => $passwordHash,
            ':account_id' => $accountId,
        ]);
    }

    public function loginByUsernameAndPassword(string $username, string $passwordInput): ?array {
        $user = $this->findByUsername($username);
        if (!$user) return null;
        $stored = $user['password'] ?? '';
        if ($stored === '') return null;
        if (password_verify($passwordInput, $stored)) return $user;
        return null;
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE account_id = ? LIMIT 1");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function fetchUserDetails(int $accountID): ?array {
        $sql = "SELECT e.employee_id, e.firstname, e.position, a.usertype
                FROM {$this->table} a
                LEFT JOIN {$this->tblemployee} e ON a.account_id = e.account_id
                WHERE a.account_id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$accountID]);
        require_once __DIR__ . '/../../Helpers/SuperUser.php';
        return SuperUser::withActingRole($stmt->fetch(PDO::FETCH_ASSOC) ?: null, (int) $accountID);
    }

    // -----------------------
    // Counts / list
    // -----------------------
    public function countUser(){
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as countUser FROM {$this->table} WHERE UPPER(status) = 'ACTIVE'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['countUser'] : 0;
    }

    public function countTicket(){
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as countTicket FROM {$this->tbltickets}");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['countTicket'] : 0;
    }

    public function countAssets(){
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as countAssets FROM {$this->tblassets}");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['countAssets'] : 0;
    }

    public function countOngoingTickets(){
        require_once __DIR__ . '/../../Helpers/TicketStatus.php';
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as countOngoingTickets FROM {$this->tbltickets} WHERE status = :status");
        $stmt->execute([':status' => TicketStatus::IN_PROGRESS]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['countOngoingTickets'] : 0;
    }

    public function countInProgressTickets(): int
    {
        return $this->countOngoingTickets();
    }

    public function countOpenTickets(): int
    {
        require_once __DIR__ . '/../../Helpers/TicketStatus.php';
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as countOpenTickets FROM {$this->tbltickets} WHERE status = :status");
        $stmt->execute([':status' => TicketStatus::OPEN]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int) $result['countOpenTickets'] : 0;
    }

    public function fetchAll(): array {
        $sql = "SELECT a.*, e.department, e.position
                FROM {$this->table} a
                LEFT JOIN {$this->tblemployee} e ON e.account_id = a.account_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * One row per person: every account (with its employee record, if any) plus
     * any employee record whose account no longer exists.
     */
    public function fetchUsersDirectory(): array {
        $sql = "SELECT a.account_id, a.username, a.usertype, a.secondary_usertype, a.status,
                       e.employee_id, e.firstname, e.middlename, e.lastname, e.department, e.position,
                       e.email, e.createdby, COALESCE(e.datecreated, a.datecreated) AS datecreated,
                       b.branchName
                FROM {$this->table} a
                LEFT JOIN {$this->tblemployee} e ON e.account_id = a.account_id
                LEFT JOIN {$this->tblbranch} b ON b.branch_id = e.branch_id
                UNION ALL
                SELECT NULL, NULL, NULL, NULL, NULL,
                       e.employee_id, e.firstname, e.middlename, e.lastname, e.department, e.position,
                       e.email, e.createdby, e.datecreated,
                       b.branchName
                FROM {$this->tblemployee} e
                LEFT JOIN {$this->table} a ON a.account_id = e.account_id
                LEFT JOIN {$this->tblbranch} b ON b.branch_id = e.branch_id
                WHERE a.account_id IS NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function isSystemAccount(array $row): bool {
        return strtolower(trim((string) ($row['username'] ?? ''))) === 'admin';
    }

    /**
     * Ticket / inventory history per employee. A person with any history is
     * deactivated rather than deleted, so company records stay intact.
     *
     * @return array<int, array{tickets: int, items: int, assets: int}>
     */
    public function fetchHistoryCounts(): array {
        $queries = [
            'tickets' => "SELECT employee_id AS id, COUNT(*) AS n FROM {$this->tbltickets} GROUP BY employee_id
                          UNION ALL SELECT assigned_to, COUNT(*) FROM {$this->tbltickets} WHERE assigned_to IS NOT NULL GROUP BY assigned_to
                          UNION ALL SELECT performed_by, COUNT(*) FROM tblticket_technical GROUP BY performed_by
                          UNION ALL SELECT uploaded_by, COUNT(*) FROM tblticket_uploads GROUP BY uploaded_by",
            'items'   => "SELECT employee_id AS id, COUNT(*) AS n FROM tbluniform_assignment GROUP BY employee_id
                          UNION ALL SELECT employee_id, COUNT(*) FROM tbluniform_returns GROUP BY employee_id",
            'assets'  => "SELECT employee_id AS id, COUNT(*) AS n FROM {$this->tblassets} WHERE employee_id IS NOT NULL GROUP BY employee_id",
        ];

        $counts = [];
        foreach ($queries as $key => $sql) {
            foreach ($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $id = (int) $row['id'];
                $counts[$id] ??= ['tickets' => 0, 'items' => 0, 'assets' => 0];
                $counts[$id][$key] += (int) $row['n'];
            }
        }
        return $counts;
    }

    public function setAccountStatus(int $accountId, string $status): bool {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET status = ? WHERE account_id = ? LIMIT 1");
        return $stmt->execute([$status, $accountId]);
    }

    /**
     * Removes a person's login and employee record in one step. Refuses when they
     * have ticket or inventory history. Assets still assigned to them are returned
     * to inventory first (the inventory FK cascades, which would otherwise delete them).
     *
     * @return array{ok: bool, message: string}
     */
    public function deleteUser(int $accountId, int $employeeId): array {
        if ($accountId > 0 && $employeeId <= 0) {
            $stmt = $this->pdo->prepare("SELECT employee_id FROM {$this->tblemployee} WHERE account_id = ? LIMIT 1");
            $stmt->execute([$accountId]);
            $employeeId = (int) ($stmt->fetchColumn() ?: 0);
        }
        if ($employeeId > 0 && $accountId <= 0) {
            $stmt = $this->pdo->prepare("SELECT account_id FROM {$this->tblemployee} WHERE employee_id = ? LIMIT 1");
            $stmt->execute([$employeeId]);
            $accountId = (int) ($stmt->fetchColumn() ?: 0);
        }
        if ($accountId <= 0 && $employeeId <= 0) {
            return ['ok' => false, 'message' => 'User not found.'];
        }

        if ($employeeId > 0) {
            $history = $this->fetchHistoryCounts()[$employeeId] ?? ['tickets' => 0, 'items' => 0];
            if ($history['tickets'] > 0 || $history['items'] > 0) {
                return ['ok' => false, 'message' => 'This person has ticket or inventory history, so they can only be deactivated.'];
            }
        }

        try {
            $this->pdo->beginTransaction();

            if ($employeeId > 0) {
                $today = date('Y-m-d');
                $this->pdo->prepare(
                    "UPDATE {$this->tblassign} a
                     JOIN {$this->tblassets} i ON i.assignment_id = a.assignment_id
                     SET a.dateReturned = ?, a.transferDetails = 'Returned to inventory - employee record deleted'
                     WHERE i.employee_id = ? AND a.dateReturned IS NULL"
                )->execute([$today, $employeeId]);
                $this->pdo->prepare(
                    "UPDATE {$this->tblassets} SET employee_id = NULL, status = 'RETURNED' WHERE employee_id = ?"
                )->execute([$employeeId]);
                $this->pdo->prepare("UPDATE {$this->tblassign} SET employee_id = NULL WHERE employee_id = ?")
                    ->execute([$employeeId]);
                $this->pdo->prepare("DELETE FROM {$this->tblemployee} WHERE employee_id = ? LIMIT 1")
                    ->execute([$employeeId]);
            }

            if ($accountId > 0) {
                $this->pdo->prepare("DELETE FROM notifications WHERE user_id = ?")->execute([$accountId]);
                $this->pdo->prepare("DELETE FROM {$this->table} WHERE account_id = ? LIMIT 1")->execute([$accountId]);
            }

            $this->pdo->commit();
            return ['ok' => true, 'message' => 'User deleted.'];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('Account::deleteUser error: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Could not delete this user: ' . $e->getMessage()];
        }
    }

    public function deleteById(int $id): bool {
        $id = (int)$id;
        if ($id <= 0) return false;
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE account_id = ? LIMIT 1");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Account::deleteById error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete employee by employee_id and linked account after clearing related records.
     */
    public function deleteEmployeeByEmployeeId(int $employeeId): bool {
        $employeeId = (int)$employeeId;
        if ($employeeId <= 0) return false;

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT account_id FROM {$this->tblemployee} WHERE employee_id = ? LIMIT 1");
            $stmt->execute([$employeeId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                $this->pdo->rollBack();
                return false;
            }
            $accountId = $result['account_id'] ?? null;

            // Unlink employee from tickets they were assigned to or referenced on
            $unlinkSql = "UPDATE {$this->tbltickets}
                          SET assigned_to = NULL
                          WHERE assigned_to = ?";
            $this->pdo->prepare($unlinkSql)->execute([$employeeId]);

            foreach (['approved_by', 'declined_by', 'created_by'] as $column) {
                $sql = "UPDATE {$this->tbltickets} SET {$column} = NULL WHERE {$column} = ?";
                $this->pdo->prepare($sql)->execute([$employeeId]);
            }

            // Delete tickets filed by this employee and their child records
            $stmt = $this->pdo->prepare("SELECT ticket_id FROM {$this->tbltickets} WHERE employee_id = ?");
            $stmt->execute([$employeeId]);
            $ticketIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($ticketIds as $ticketId) {
                $this->pdo->prepare("DELETE FROM ticket_ratings WHERE ticket_id = ?")->execute([$ticketId]);
                $this->pdo->prepare("DELETE FROM tblticket_technical WHERE ticket_id = ?")->execute([$ticketId]);
                $this->pdo->prepare("DELETE FROM tblticket_history WHERE ticket_id = ?")->execute([$ticketId]);
                $this->pdo->prepare("DELETE FROM {$this->tbltickets} WHERE ticket_id = ?")->execute([$ticketId]);
            }

            $this->pdo->prepare("DELETE FROM ticket_ratings WHERE employee_id = ?")->execute([$employeeId]);
            $this->pdo->prepare("DELETE FROM tblticket_technical WHERE performed_by = ?")->execute([$employeeId]);

            $stmt = $this->pdo->prepare("DELETE FROM {$this->tblemployee} WHERE employee_id = ? LIMIT 1");
            $ok = $stmt->execute([$employeeId]);

            if ($accountId) {
                $this->pdo->prepare("DELETE FROM {$this->table} WHERE account_id = ? LIMIT 1")->execute([$accountId]);
            }

            $this->pdo->commit();
            return $ok;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Account::deleteEmployeeByEmployeeId error: " . $e->getMessage());
            return false;
        }
    }

    // -----------------------
    // Update methods
    // -----------------------

    /**
     * Update account using associative array that includes account_id.
     * - Expects 'password' to already be the final value to store (hashed or preserved).
     */
    public function updateAccount(array $data): bool {
        $id = (int)($data['account_id'] ?? 0);
        if ($id <= 0) return false;

        $sql = "UPDATE {$this->table}
                SET username = ?, password = ?, usertype = ?, status = ?
                WHERE account_id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);

        $ok = $stmt->execute([
            $data['username'] ?? '',
            $data['password'] ?? '',
            $data['usertype'] ?? '',
            $data['status'] ?? '',
            $id
        ]);

        if (!$ok) {
            error_log('Account::updateAccount execute failed: ' . json_encode($stmt->errorInfo()));
            return false;
        }

        // Not an error if rowCount() === 0 — could be unchanged values.
        return true;
    }

    /**
     * Update employee using associative array that includes employee_id.
     */
    public function updateEmployee(array $data): bool {
        $id = (int)($data['employee_id'] ?? 0);
        if ($id <= 0) return false;
        $sql = "UPDATE {$this->tblemployee}
                SET lastname = ?, firstname = ?, middlename = ?, department = ?, branch_id = ?, email = ?
                WHERE employee_id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        if (!$stmt->execute([
            $data['lastname'] ?? '',
            $data['firstname'] ?? '',
            $data['middlename'] ?? '',
            $data['department'] ?? '',
            (int)($data['branch_id'] ?? 0),
            $data['email'] ?? '',
            $id
        ])) {
            $err = $stmt->errorInfo();
            error_log('Account::updateEmployee execute failed: ' . json_encode($err));
            return false;
        }
        return true;
    }

    public function fetchBranches(): array {
        $stmt = $this->pdo->prepare("SELECT branch_id, branchName FROM {$this->tblbranch} ORDER BY branchName ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function fetchAccountById(int $accountId): ?array {
        $sql = "SELECT a.*, e.*, b.branch_id AS branch_id, b.branchName
                FROM {$this->table} a
                LEFT JOIN {$this->tblemployee} e ON a.account_id = e.account_id
                LEFT JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id
                WHERE a.account_id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$accountId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // -----------------------
    // Create helpers
    // -----------------------
    public function isUsernameExists($username) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$this->table} WHERE username = ?");
        $stmt->execute([$username]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    }

    public function createAccount(array $data): ?int {
        $sql = "INSERT INTO {$this->table} (username, password, usertype, status, createdby, datecreated)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $ok = $stmt->execute([
            $data['username'] ?? '',
            $data['password'] ?? '',
            $data['usertype'] ?? '',
            $data['status'] ?? 'ACTIVE',
            $data['createdby'] ?? 'SYSTEM',
            $data['datecreated'] ?? date('Y-m-d H:i:s'),
        ]);
        if (!$ok) {
            error_log('Account::createAccount execute failed: ' . json_encode($stmt->errorInfo()));
            return null;
        }
        return (int)$this->pdo->lastInsertId();
    }


    public function createEmployee(array $data): ?int
    {
        $sql = "INSERT INTO {$this->tblemployee}
                (employee_id, account_id, lastname, firstname, middlename, department, branch_id, email, position, createdby, datecreated)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);

        $ok = $stmt->execute([
            (int)$data['employee_id'],          // 🔴 MANUAL PRIMARY KEY
            (int)$data['account_id'],
            $data['lastname'] ?? '',
            $data['firstname'] ?? '',
            $data['middlename'] ?? '',
            $data['department'] ?? '',
            $data['branch_id'] ?? null,
            $data['email'] ?? '',
            $data['position'] ?? '',
            $data['createdby'] ?? 'SYSTEM',
            $data['datecreated'] ?? date('Y-m-d H:i:s'),
        ]);

        if (!$ok) {
            error_log('Account::createEmployee execute failed: ' . json_encode($stmt->errorInfo()));
            return null;
        }

        // MANUAL PK → return the same ID
        return (int)$data['employee_id'];
    }

            // Fetch employee list with branch names
    public function fetchEmployee(): array {
        $stmt = $this->pdo->prepare("SELECT e.employee_id, e.account_id, e.lastname, e.firstname, e.middlename, e.department, e.position, e.email, e.createdby, e.datecreated, b.branchName FROM {$this->tblemployee} e LEFT JOIN {$this->tblbranch} b ON e.branch_id = b.branch_id ORDER BY firstname ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function fetchAssetsByEmployeeId(int $employeeId): array {
        $stmt = $this->pdo->prepare("SELECT i.group_id, i.inventory_id, i.assetNumber, i.status, g.groupName, g.description, i.itemInfo, i.serialNumber
            FROM {$this->tblassets} i
            JOIN {$this->tblgroup} g ON i.group_id = g.group_id
            WHERE i.employee_id = ? AND i.status = 'ASSIGNED'
            ORDER BY i.inventory_id ASC");
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // ========================
    // LOGIN ATTEMPT TRACKING
    // ========================
    
    /**
     * Counts a failed login for the record. Locking is handled by LoginThrottle
     * (15 minutes), so failed logins never deactivate an account.
     */
    public function recordFailedAttempt(string $username): bool {
        $stmt = $this->pdo->prepare("UPDATE {$this->table}
            SET failed_attempts = failed_attempts + 1,
                last_attempt_time = NOW()
            WHERE username = ? LIMIT 1");
        return $stmt->execute([$username]);
    }

    /**
     * Resets failed login attempts on successful login
     */
    public function resetFailedAttempts(string $username): bool {
        $stmt = $this->pdo->prepare("UPDATE {$this->table}
            SET failed_attempts = 0, last_attempt_time = NULL
            WHERE username = ? LIMIT 1");
        return $stmt->execute([$username]);
    }

    /** Admin unlock / reactivation / new password: start the failed-login count over. */
    public function resetFailedAttemptsById(int $accountId): bool {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET failed_attempts = 0, last_attempt_time = NULL WHERE account_id = ? LIMIT 1");
        return $stmt->execute([$accountId]);
    }

    //Admin Account Model ends here

    //Employee Account  Model Starts here 


}
