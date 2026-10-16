<?php

require_once __DIR__ . '/../Helpers/SecretBox.php';

/**
 * Company Gmail account of an employee (tblcompany_gmail): the address, the Gmail password
 * (encrypted; Admin and HR can see it, the TMS password is never shown) and the set-up checklist
 * from the IT Excel sheet (account made, password given, phone/recovery verified, 2-step on).
 *
 * Google does not let other systems create @gmail.com accounts, so the account itself is made on
 * Google's sign-up page (the Company Gmail page shows a QR code that opens it on a phone).
 */
class CompanyGmailModel
{
    public const CHECKS = [
        'created_ok'     => 'Gmail account created (the QR code or text-message phone check by Google is done)',
        'password_given' => 'Password given to the employee',
        'phone_verified' => 'Phone number and recovery email verified',
        'two_step'       => '2-step verification turned on',
    ];

    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? $GLOBALS['pdo'];
    }

    public function ready(): bool
    {
        try {
            $this->pdo->query('SELECT employee_id FROM tblcompany_gmail LIMIT 1');
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /** storagemart.<first name><last name>@gmail.com, letters only (e.g. storagemart.annmercyabueva@gmail.com). */
    public static function suggestEmail(string $firstname, string $lastname): string
    {
        $slug = static function (string $s): string {
            $s = strtr(mb_strtolower($s, 'UTF-8'), ['ñ' => 'n', 'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a', 'é' => 'e', 'è' => 'e',
                'ê' => 'e', 'ë' => 'e', 'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o',
                'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u']);
            return preg_replace('/[^a-z]/', '', $s);
        };
        $local = $slug($firstname) . $slug($lastname);
        return $local === '' ? '' : 'storagemart.' . $local . '@gmail.com';
    }

    /**
     * The default Gmail password from the corporate email list: middle name + FORMAT
     * (Excel =CONCATENATE(middle name, FORMAT)); for new hires FORMAT is the Employee ID,
     * e.g. "C." + 2600192 = "C.2600192". Without a middle name the last name is used.
     */
    public static function formulaPassword(string $middlename, $employeeId, string $lastname = ''): string
    {
        $first = trim($middlename) !== '' ? trim($middlename) : trim($lastname);
        $id = trim((string) $employeeId);
        return ($first === '' || $id === '' || $id === '0') ? '' : $first . $id;
    }

    /** Employee row with the Gmail record (gmail_* columns), or null. */
    public function employee(int $employeeId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT e.employee_id, e.account_id, e.firstname, e.middlename, e.lastname, e.email, e.position, e.department,
                    b.branchName, a.username, a.status AS account_status
             FROM tblemployee e
             LEFT JOIN tblaccounts a ON a.account_id = e.account_id
             LEFT JOIN tblbranch b ON b.branch_id = e.branch_id
             WHERE e.employee_id = ? LIMIT 1"
        );
        $stmt->execute([$employeeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        $row['gmail'] = $this->get($employeeId);
        return $row;
    }

    public function get(int $employeeId): ?array
    {
        if (!$this->ready()) {
            return null;
        }
        $stmt = $this->pdo->prepare('SELECT * FROM tblcompany_gmail WHERE employee_id = ? LIMIT 1');
        $stmt->execute([$employeeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        $row['has_password'] = (string) ($row['password_enc'] ?? '') !== '';
        unset($row['password_enc']);
        return $row;
    }

    /** The saved Gmail password, or null (none saved, or it cannot be read any more). */
    public function password(int $employeeId): ?string
    {
        if (!$this->ready()) {
            return null;
        }
        $stmt = $this->pdo->prepare('SELECT password_enc FROM tblcompany_gmail WHERE employee_id = ? LIMIT 1');
        $stmt->execute([$employeeId]);
        return SecretBox::decrypt($stmt->fetchColumn() ?: null);
    }

    /**
     * Saves the address, the checklist and (when $password is not null) the Gmail password.
     * An empty $password keeps the saved one. Returns an error message or null.
     */
    public function save(int $employeeId, string $email, ?string $password, array $checks, string $notes, int $by): ?string
    {
        if (!$this->ready()) {
            return 'Please run the database update (update P) first.';
        }
        $email = trim($email);
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Enter the full Gmail address with @, for example storagemart.juandelacruz@gmail.com.';
        }
        $encrypted = null;
        if ($password !== null && $password !== '') {
            if (mb_strlen($password) < 8 || mb_strlen($password) > 100) {
                return 'The Gmail password must be 8 to 100 characters (Google needs at least 8).';
            }
            $encrypted = SecretBox::encrypt($password);
            if ($encrypted === null) {
                return 'The password could not be saved securely on this server (encryption is not available).';
            }
        }
        $flags = [];
        foreach (array_keys(self::CHECKS) as $key) {
            $flags[$key] = !empty($checks[$key]) ? 1 : 0;
        }
        $sql = 'INSERT INTO tblcompany_gmail (employee_id, email, password_enc, created_ok, password_given, phone_verified, two_step, notes, updated_by, updated_at)
                VALUES (:id, :email, :pw, :c1, :c2, :c3, :c4, :notes, :by, NOW())
                ON DUPLICATE KEY UPDATE email = VALUES(email),
                    password_enc = COALESCE(VALUES(password_enc), password_enc),
                    created_ok = VALUES(created_ok), password_given = VALUES(password_given),
                    phone_verified = VALUES(phone_verified), two_step = VALUES(two_step),
                    notes = VALUES(notes), updated_by = VALUES(updated_by), updated_at = NOW()';
        $this->pdo->prepare($sql)->execute([
            ':id' => $employeeId, ':email' => $email !== '' ? $email : null, ':pw' => $encrypted,
            ':c1' => $flags['created_ok'], ':c2' => $flags['password_given'], ':c3' => $flags['phone_verified'], ':c4' => $flags['two_step'],
            ':notes' => mb_substr(trim($notes), 0, 255) ?: null, ':by' => $by ?: null,
        ]);
        return null;
    }

    /** Right after an account is created: keep the address and the Gmail password typed on the form. */
    public function remember(int $employeeId, string $email, string $password, int $by): void
    {
        if (!$this->ready() || $this->get($employeeId)) {
            return;
        }
        $this->save($employeeId, $email, $password !== '' ? $password : null, [], '', $by);
    }

    /** Name of the person who last saved the record (for "Done by"). */
    public function updatedByName(?int $accountId): string
    {
        if (!$accountId) {
            return '';
        }
        $stmt = $this->pdo->prepare(
            "SELECT TRIM(CONCAT(COALESCE(e.firstname, ''), ' ', COALESCE(e.lastname, ''))) AS name, a.username
             FROM tblaccounts a LEFT JOIN tblemployee e ON e.account_id = a.account_id WHERE a.account_id = ? LIMIT 1"
        );
        $stmt->execute([$accountId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (trim((string) $row['name']) ?: (string) $row['username']) : '';
    }
}
