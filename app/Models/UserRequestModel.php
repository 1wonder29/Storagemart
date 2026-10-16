<?php
require_once __DIR__ . '/admin/Account.php';
require_once __DIR__ . '/../Helpers/AppSettings.php';
require_once __DIR__ . '/../Helpers/EmployeePersonal.php';
require_once __DIR__ . '/../Helpers/PasswordPolicy.php';
require_once __DIR__ . '/../Helpers/RoleLabel.php';

/**
 * New user accounts requested by HR (tbluser_requests).
 * Steps: HR fills the form -> the HR Head approves (skipped when the HR Head made the request)
 * -> an Admin approves, and only then the login and employee record are created.
 * Status: HR_HEAD (waiting for the HR Head), ADMIN (waiting for the Admin), APPROVED, REJECTED, CANCELLED.
 */
class UserRequestModel
{
    /** Roles HR may request. IT and Admin accounts are made by the Admin only. */
    public const ROLES = ['EMPLOYEE', 'HEAD', 'HR', 'AOM', 'OM', 'HOM'];
    public const HR_HEAD_SETTING = 'hr_head_account_id';
    public const STATUS_LABELS = [
        'HR_HEAD'   => ['Waiting for the HR Head', 'warning'],
        'ADMIN'     => ['Waiting for the Admin', 'info'],
        'APPROVED'  => ['Approved, account created', 'success'],
        'REJECTED'  => ['Rejected', 'danger'],
        'CANCELLED' => ['Cancelled', 'secondary'],
    ];

    private PDO $pdo;

    public function __construct(?PDO $connection = null)
    {
        $this->pdo = $connection ?? $GLOBALS['pdo'];
    }

    public function ready(): bool
    {
        try {
            $this->pdo->query('SELECT 1 FROM tbluser_requests LIMIT 1');
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /** @return array<string, string> role code => label */
    public static function roleOptions(): array
    {
        $out = [];
        foreach (self::ROLES as $code) {
            $out[$code] = RoleLabel::of($code);
        }
        return $out;
    }

    /**
     * The HR Head(s) who approve first: the account the Admin chose, otherwise active HR accounts
     * whose position says Manager / Head / Supervisor, otherwise every active HR account.
     * @return int[] account ids
     */
    public function hrHeadAccountIds(): array
    {
        $rows = $this->pdo->query("SELECT a.account_id, e.position FROM tblaccounts a
                LEFT JOIN tblemployee e ON e.account_id = a.account_id
                WHERE a.usertype = 'HR' AND UPPER(a.status) = 'ACTIVE'")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $all = array_map(static fn($r) => (int) $r['account_id'], $rows);
        $chosen = (int) AppSettings::get(self::HR_HEAD_SETTING, '0');
        if ($chosen > 0 && in_array($chosen, $all, true)) {
            return [$chosen];
        }
        $heads = array_map(static fn($r) => (int) $r['account_id'], array_filter($rows, static function ($r) {
            return (bool) preg_match('/\b(manager|head|supervisor|director)\b/i', (string) ($r['position'] ?? ''));
        }));
        return $heads ?: $all;
    }

    /** Active HR accounts (to choose the HR Head). */
    public function hrAccounts(): array
    {
        return $this->pdo->query("SELECT a.account_id, CONCAT(e.firstname, ' ', e.lastname) AS name, e.position
                FROM tblaccounts a JOIN tblemployee e ON e.account_id = a.account_id
                WHERE a.usertype = 'HR' AND UPPER(a.status) = 'ACTIVE' ORDER BY e.firstname")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function isHrHead(int $accountId): bool
    {
        return in_array($accountId, $this->hrHeadAccountIds(), true);
    }

    /** @return int[] active Admin account ids */
    public function adminAccountIds(): array
    {
        return array_map('intval', $this->pdo->query("SELECT account_id FROM tblaccounts
                WHERE usertype = 'ADMIN' AND UPPER(status) = 'ACTIVE'")->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    /**
     * Checks the form. Returns [data to store, error or null]. The password is stored hashed only.
     * @return array{0: array, 1: ?string}
     */
    public function validate(array $post): array
    {
        $d = [
            'username'    => trim((string) ($post['username'] ?? '')),
            'usertype'    => strtoupper(trim((string) ($post['usertype'] ?? ''))),
            'employee_id' => trim((string) ($post['employee_id'] ?? '')),
            'branch_id'   => (int) ($post['branch_id'] ?? 0),
            'lastname'    => trim((string) ($post['lastname'] ?? '')),
            'firstname'   => trim((string) ($post['firstname'] ?? '')),
            'middlename'  => trim((string) ($post['middlename'] ?? '')),
            'department'  => trim((string) ($post['department'] ?? '')),
            'email'       => trim((string) ($post['email'] ?? '')),
            'position'    => trim((string) ($post['position'] ?? '')),
            'reason'      => mb_substr(trim((string) ($post['reason'] ?? '')), 0, 255),
        ];
        $password = (string) ($post['password'] ?? '');
        [$personal, $personalError] = EmployeePersonal::fromPost($post);
        $d['personal'] = $personal;

        if ($d['username'] === '' || $password === '' || $d['lastname'] === '' || $d['firstname'] === '') {
            return [$d, 'Username, first password, first name and last name are required.'];
        }
        if (!in_array($d['usertype'], self::ROLES, true)) {
            return [$d, 'Choose the user type. IT and Admin accounts are made by the Admin.'];
        }
        if (!ctype_digit($d['employee_id']) || (int) $d['employee_id'] <= 0) {
            return [$d, 'Employee ID is required and must be a positive number with no spaces.'];
        }
        if ($d['branch_id'] <= 0 || $d['department'] === '' || $d['position'] === '') {
            return [$d, 'Choose the branch and department, and type the position.'];
        }
        if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
            return [$d, 'Enter a full email address with @, for example storagemart.juandelacruz@gmail.com.'];
        }
        // Company Gmail password (optional): kept encrypted until the account is created.
        $gmailPassword = (string) ($post['gmail_password'] ?? '');
        if ($gmailPassword === '') {
            require_once __DIR__ . '/CompanyGmailModel.php';
            $gmailFormula = CompanyGmailModel::formulaPassword($d['middlename'], $d['employee_id'], $d['lastname']);
            $gmailPassword = mb_strlen($gmailFormula) >= 8 ? $gmailFormula : '';
        }
        if ($gmailPassword !== '') {
            if (mb_strlen($gmailPassword) < 8 || mb_strlen($gmailPassword) > 100) {
                return [$d, 'The Gmail password must be 8 to 100 characters (Google needs at least 8).'];
            }
            require_once __DIR__ . '/../Helpers/SecretBox.php';
            $d['gmail_password_enc'] = SecretBox::encrypt($gmailPassword);
        }
        if (($problem = PasswordPolicy::adminSetProblem($password)) !== null) {
            return [$d, $problem];
        }
        if ($personalError !== null) {
            return [$d, $personalError];
        }
        if ($problem = $this->conflict($d['username'], (int) $d['employee_id'])) {
            return [$d, $problem];
        }
        $d['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        return [$d, null];
    }

    /** Username or employee ID already used by an account or by another open request. */
    public function conflict(string $username, int $employeeId, int $exceptRequestId = 0): ?string
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM tblaccounts WHERE LOWER(username) = LOWER(?)');
        $stmt->execute([$username]);
        if ((int) $stmt->fetchColumn() > 0) {
            return 'This username already has an account.';
        }
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM tblemployee WHERE employee_id = ?');
        $stmt->execute([$employeeId]);
        if ((int) $stmt->fetchColumn() > 0) {
            return 'This Employee ID is already used by another employee.';
        }
        $stmt = $this->pdo->prepare("SELECT data_json FROM tbluser_requests
                WHERE status IN ('HR_HEAD', 'ADMIN') AND request_id <> ?");
        $stmt->execute([$exceptRequestId]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $json) {
            $other = json_decode((string) $json, true) ?: [];
            if (strcasecmp((string) ($other['username'] ?? ''), $username) === 0) {
                return 'There is already a request waiting for this username.';
            }
            if ((int) ($other['employee_id'] ?? 0) === $employeeId) {
                return 'There is already a request waiting for this Employee ID.';
            }
        }
        return null;
    }

    public function create(array $data, int $requestedBy): int
    {
        $isHead = $this->isHrHead($requestedBy);
        $stmt = $this->pdo->prepare('INSERT INTO tbluser_requests
                (data_json, full_name, username, usertype, status, requested_by, requested_at, hr_head_by, hr_head_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ' . ($isHead ? 'NOW()' : 'NULL') . ')');
        $stmt->execute([
            json_encode($data, JSON_UNESCAPED_UNICODE),
            trim($data['firstname'] . ' ' . $data['lastname']),
            $data['username'],
            $data['usertype'],
            $isHead ? 'ADMIN' : 'HR_HEAD',
            $requestedBy,
            $isHead ? $requestedBy : null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function get(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tbluser_requests WHERE request_id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        $row['data'] = json_decode((string) $row['data_json'], true) ?: [];
        return $this->withNames([$row])[0];
    }

    public function recent(int $limit = 100): array
    {
        $rows = $this->pdo->query("SELECT * FROM tbluser_requests
                ORDER BY FIELD(status, 'HR_HEAD', 'ADMIN') DESC, request_id DESC LIMIT " . (int) $limit)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$row) {
            $row['data'] = json_decode((string) $row['data_json'], true) ?: [];
        }
        unset($row);
        return $this->withNames($rows);
    }

    public function countWaiting(string $status): int
    {
        try {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM tbluser_requests WHERE status = ?');
            $stmt->execute([$status]);
            return (int) $stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    /** Moves a request from one status to the next only if it is still in $from (two people clicking at once). */
    public function move(int $id, string $from, string $to, array $set = []): bool
    {
        $cols = '';
        $params = [':to' => $to, ':id' => $id, ':from' => $from];
        foreach ($set as $col => $value) {
            if (!preg_match('/^[a-z_]+$/', $col)) {
                continue;
            }
            $cols .= ", $col = " . ($value === 'NOW()' ? 'NOW()' : ":$col");
            if ($value !== 'NOW()') {
                $params[":$col"] = $value;
            }
        }
        $stmt = $this->pdo->prepare("UPDATE tbluser_requests SET status = :to$cols WHERE request_id = :id AND status = :from");
        $stmt->execute($params);
        return $stmt->rowCount() === 1;
    }

    /**
     * Admin approval: creates the login and the employee record (same as Users > Add User).
     * @return array{0: bool, 1: string} [ok, message]
     */
    public function createAccount(array $request, int $adminAccountId, string $adminUsername): array
    {
        $d = $request['data'];
        if ($problem = $this->conflict((string) $d['username'], (int) $d['employee_id'], (int) $request['request_id'])) {
            return [false, $problem];
        }
        if (!$this->move((int) $request['request_id'], 'ADMIN', 'CREATING')) {
            return [false, 'This request was already handled.'];
        }
        $accountModel = new Account();
        try {
            $this->pdo->beginTransaction();
            $accountId = $accountModel->createAccount([
                'username'    => $d['username'],
                'password'    => $d['password_hash'],
                'usertype'    => $d['usertype'],
                'status'      => 'ACTIVE',
                'createdby'   => $adminUsername,
                'datecreated' => date('Y-m-d H:i:s'),
            ]);
            if (!$accountId) {
                throw new RuntimeException('The login could not be created.');
            }
            $employeeId = $accountModel->createEmployee([
                'employee_id' => (int) $d['employee_id'],
                'account_id'  => (int) $accountId,
                'lastname'    => $d['lastname'],
                'firstname'   => $d['firstname'],
                'middlename'  => $d['middlename'] ?? '',
                'department'  => $d['department'],
                'branch_id'   => (int) $d['branch_id'] ?: null,
                'email'       => $d['email'] ?? '',
                'position'    => $d['position'],
                'createdby'   => $adminUsername,
                'datecreated' => date('Y-m-d H:i:s'),
            ]);
            if (!$employeeId) {
                throw new RuntimeException('The employee record could not be created.');
            }
            if (EmployeePersonal::ready($this->pdo)) {
                EmployeePersonal::save($this->pdo, (int) $d['employee_id'], (array) ($d['personal'] ?? []));
            }
            $this->move((int) $request['request_id'], 'CREATING', 'APPROVED', [
                'admin_by' => $adminAccountId, 'admin_at' => 'NOW()', 'created_account_id' => (int) $accountId,
            ]);
            $this->pdo->commit();
            // Company Gmail: the address and the Gmail password HR typed.
            try {
                require_once __DIR__ . '/CompanyGmailModel.php';
                $gmailPassword = !empty($d['gmail_password_enc']) ? (string) SecretBox::decrypt($d['gmail_password_enc']) : '';
                $isGmail = (bool) preg_match('/@gmail\.com$/i', (string) ($d['email'] ?? ''));
                if ($isGmail || $gmailPassword !== '') {
                    (new CompanyGmailModel($this->pdo))->remember((int) $d['employee_id'], $isGmail ? (string) $d['email'] : '', $gmailPassword, $adminAccountId);
                }
            } catch (Throwable $e) {
                error_log('Company Gmail not saved for request ' . $request['request_id'] . ': ' . $e->getMessage());
            }
            return [true, 'Account created for ' . $request['full_name'] . '.'];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->move((int) $request['request_id'], 'CREATING', 'ADMIN');
            error_log('UserRequestModel::createAccount failed: ' . $e->getMessage());
            return [false, 'Could not create the account: ' . $e->getMessage()];
        }
    }

    /** Adds requested_name / hr_head_name / admin_name. */
    private function withNames(array $rows): array
    {
        $ids = [];
        foreach ($rows as $r) {
            foreach (['requested_by', 'hr_head_by', 'admin_by'] as $col) {
                if (!empty($r[$col])) {
                    $ids[(int) $r[$col]] = true;
                }
            }
        }
        $names = [];
        if ($ids) {
            $in = implode(',', array_map('intval', array_keys($ids)));
            foreach ($this->pdo->query("SELECT a.account_id, TRIM(CONCAT(COALESCE(e.firstname, ''), ' ', COALESCE(e.lastname, ''))) AS name, a.username
                    FROM tblaccounts a LEFT JOIN tblemployee e ON e.account_id = a.account_id WHERE a.account_id IN ($in)") as $n) {
                $names[(int) $n['account_id']] = $n['name'] !== '' ? $n['name'] : $n['username'];
            }
        }
        foreach ($rows as &$r) {
            foreach (['requested_by' => 'requested_name', 'hr_head_by' => 'hr_head_name', 'admin_by' => 'admin_name'] as $col => $key) {
                $r[$key] = !empty($r[$col]) ? ($names[(int) $r[$col]] ?? '#' . $r[$col]) : '';
            }
        }
        unset($r);
        return $rows;
    }
}
