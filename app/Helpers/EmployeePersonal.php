<?php

/**
 * Personal details of an employee (tblemployee.birthdate, gender, date_hired, phone), entered by
 * the Admin in Users & Employees > Add User / Edit. They feed the birthday greetings and the
 * Personal Details card on the profile. Other users only ever see the day and month of a birthday.
 */
class EmployeePersonal
{
    public const GENDERS = ['Male', 'Female'];

    /** False until the personal details columns exist (database update). */
    public static function ready(PDO $pdo): bool
    {
        try {
            $pdo->query('SELECT birthdate, gender, date_hired, phone FROM tblemployee LIMIT 1');
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Reads and checks the form fields (birthdate, gender, date_hired, phone). Empty = not set.
     * @return array{0: array, 1: ?string} [data, error message]
     */
    public static function fromPost(array $post): array
    {
        $data = [
            'birthdate'  => trim((string) ($post['birthdate'] ?? '')),
            'gender'     => trim((string) ($post['gender'] ?? '')),
            'date_hired' => trim((string) ($post['date_hired'] ?? '')),
            'phone'      => trim((string) ($post['phone'] ?? '')),
        ];
        $today = date('Y-m-d');
        if ($data['birthdate'] !== '' && (!self::validDate($data['birthdate']) || $data['birthdate'] > $today || $data['birthdate'] < '1930-01-01')) {
            return [$data, 'Birthday must be a real date in the past.'];
        }
        if ($data['birthdate'] !== '' && $data['birthdate'] > date('Y-m-d', strtotime('-15 years'))) {
            return [$data, 'Birthday: the employee must be at least 15 years old. Check the year.'];
        }
        if ($data['date_hired'] !== '' && (!self::validDate($data['date_hired']) || $data['date_hired'] < '1990-01-01'
                || $data['date_hired'] > date('Y-m-d', strtotime('+3 months')))) {
            return [$data, 'Start date must be a real date, at most 3 months from today.'];
        }
        if ($data['birthdate'] !== '' && $data['date_hired'] !== '' && $data['date_hired'] <= $data['birthdate']) {
            return [$data, 'The start date must be after the birthday.'];
        }
        if ($data['gender'] !== '' && !in_array($data['gender'], self::GENDERS, true)) {
            return [$data, 'Choose Male or Female for the gender, or leave it empty.'];
        }
        $digits = preg_replace('/\D/', '', $data['phone']);
        if ($data['phone'] !== '' && (!preg_match('/^\+?[0-9()\-\s]+$/', $data['phone']) || strlen($digits) < 7 || strlen($digits) > 13)) {
            return [$data, 'Phone number: numbers only, 7 to 13 digits (for example 09171234567).'];
        }
        return [$data, null];
    }

    /** @return array{birthdate: string, gender: string, date_hired: string, phone: string} */
    public static function get(PDO $pdo, int $employeeId): array
    {
        $empty = ['birthdate' => '', 'gender' => '', 'date_hired' => '', 'phone' => ''];
        if ($employeeId <= 0 || !self::ready($pdo)) {
            return $empty;
        }
        $stmt = $pdo->prepare('SELECT birthdate, gender, date_hired, phone FROM tblemployee WHERE employee_id = ? LIMIT 1');
        $stmt->execute([$employeeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        return [
            'birthdate'  => !empty($row['birthdate']) ? date('Y-m-d', strtotime((string) $row['birthdate'])) : '',
            'gender'     => (string) ($row['gender'] ?? ''),
            'date_hired' => !empty($row['date_hired']) ? date('Y-m-d', strtotime((string) $row['date_hired'])) : '',
            'phone'      => (string) ($row['phone'] ?? ''),
        ];
    }

    public static function save(PDO $pdo, int $employeeId, array $data): void
    {
        if ($employeeId <= 0 || !self::ready($pdo)) {
            return;
        }
        $null = static fn($v) => $v === '' ? null : $v;
        $pdo->prepare('UPDATE tblemployee SET birthdate = ?, gender = ?, date_hired = ?, phone = ? WHERE employee_id = ?')
            ->execute([$null($data['birthdate']), $null($data['gender']), $null($data['date_hired']), $null($data['phone']), $employeeId]);
    }

    private static function validDate(string $value): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $value);
        return $d !== false && $d->format('Y-m-d') === $value;
    }
}
