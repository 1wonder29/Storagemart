<?php

/**
 * Birthdays from the HR employee list (tblemployee.birthdate): today's celebrants and the
 * ones coming in the next days (countdown), plus the colleagues' greetings
 * (tblbirthday_greetings). Shown on the dashboards, not in the notification bell.
 * Feb 29 birthdays are celebrated on Feb 28 in other years.
 */
class Birthdays
{
    public const COUNTDOWN_DAYS = 3;

    /**
     * Active people whose birthday is today or within COUNTDOWN_DAYS days, soonest first.
     * @return array<int, array{account_id:int, name:string, firstname:string, department:string, branch:string, date:string, days_left:int}>
     */
    public static function upcoming(PDO $pdo, ?DateTimeImmutable $today = null): array
    {
        $today = ($today ?? new DateTimeImmutable('today'))->setTime(0, 0);
        try {
            $rows = $pdo->query("SELECT e.account_id, e.firstname, e.lastname, e.department, e.birthdate, b.branchName
                    FROM tblemployee e
                    JOIN tblaccounts a ON a.account_id = e.account_id
                    LEFT JOIN tblbranch b ON b.branch_id = e.branch_id
                    WHERE e.birthdate IS NOT NULL AND UPPER(a.status) = 'ACTIVE'")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return []; // birthdate column not added yet
        }
        $out = [];
        foreach ($rows as $row) {
            $next = self::nextBirthday((string) $row['birthdate'], $today);
            if ($next === null) {
                continue;
            }
            $days = (int) $today->diff($next)->format('%a');
            if ($days > self::COUNTDOWN_DAYS) {
                continue;
            }
            $out[] = [
                'account_id' => (int) $row['account_id'],
                'name' => trim($row['firstname'] . ' ' . $row['lastname']),
                'firstname' => trim((string) $row['firstname']),
                'department' => trim((string) $row['department']),
                'branch' => trim((string) ($row['branchName'] ?? '')),
                'date' => $next->format('Y-m-d'),
                'days_left' => $days,
            ];
        }
        usort($out, static function ($a, $b) {
            return [$a['days_left'], $a['name']] <=> [$b['days_left'], $b['name']];
        });
        return $out;
    }

    /** The next birthday on or after $today. */
    public static function nextBirthday(string $birthdate, DateTimeImmutable $today): ?DateTimeImmutable
    {
        if (!preg_match('/^\d{4}-(\d{2})-(\d{2})/', $birthdate, $m)) {
            return null;
        }
        foreach ([(int) $today->format('Y'), (int) $today->format('Y') + 1] as $year) {
            $month = (int) $m[1];
            $day = (int) $m[2];
            if ($month === 2 && $day === 29 && !checkdate(2, 29, $year)) {
                $day = 28;
            }
            if (!checkdate($month, $day, $year)) {
                return null;
            }
            $date = new DateTimeImmutable(sprintf('%04d-%02d-%02d', $year, $month, $day));
            if ($date >= $today) {
                return $date;
            }
        }
        return null;
    }

    /** Display name of an account (employee name, or the username for shared accounts). */
    public static function nameOf(PDO $pdo, int $accountId): string
    {
        $stmt = $pdo->prepare("SELECT TRIM(CONCAT(COALESCE(e.firstname, ''), ' ', COALESCE(e.lastname, ''))) AS name, a.username
                FROM tblaccounts a LEFT JOIN tblemployee e ON e.account_id = a.account_id WHERE a.account_id = ? LIMIT 1");
        $stmt->execute([$accountId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return 'A colleague';
        }
        return $row['name'] !== '' ? $row['name'] : (string) $row['username'];
    }
}
