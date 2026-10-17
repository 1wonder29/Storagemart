<?php

/**
 * Digital signatures on a ticket's Technical Summary Report (tblticket_signatures).
 * Boxes: ACKNOWLEDGED (the requester), REPORTED (the IT person), VERIFIED (Admin / IT Officer).
 * A signature is a small JPEG (data URL) placed inside its box at offset_x / offset_y with a
 * width of BASE_WIDTH * scale_pct %, the same on screen and in the PDF.
 */
class TicketSignatures
{
    public const SLOTS = [
        'ACKNOWLEDGED' => 'Acknowledge By',
        'REPORTED'     => 'Reported By',
        'VERIFIED'     => 'Verified By',
    ];

    public const AREA_W = 200;
    public const AREA_H = 64;
    public const BASE_WIDTH = 150;
    public const MAX_BYTES = 400 * 1024;

    public static function tableExists(PDO $pdo): bool
    {
        static $exists = null;
        if ($exists === null) {
            $exists = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tblticket_signatures'")->fetchColumn() === 1;
        }
        return $exists;
    }

    /** @return array<string, array> slot => signature row */
    public static function forTicket(PDO $pdo, int $ticketId): array
    {
        if (!self::tableExists($pdo)) {
            return [];
        }
        $stmt = $pdo->prepare('SELECT * FROM tblticket_signatures WHERE ticket_id = ?');
        $stmt->execute([$ticketId]);
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $out[$row['slot']] = $row;
        }
        return $out;
    }

    /** Ticket facts needed to decide who signs which box. */
    public static function ticketFacts(PDO $pdo, int $ticketId): ?array
    {
        $stmt = $pdo->prepare("SELECT t.ticket_id, t.ticket_number, t.status, t.employee_id, t.created_by, t.assigned_to,
                    req.account_id AS requester_account_id,
                    TRIM(CONCAT(COALESCE(req.firstname, ''), ' ', COALESCE(req.lastname, ''))) AS requester_name,
                    (SELECT tt.performed_by FROM tblticket_technical tt WHERE tt.ticket_id = t.ticket_id
                      ORDER BY tt.date_performed DESC, tt.tech_id DESC LIMIT 1) AS performed_by
                FROM tbltickets t
                LEFT JOIN tblemployee req ON req.employee_id = t.employee_id
                WHERE t.ticket_id = ? LIMIT 1");
        $stmt->execute([$ticketId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Boxes the signed-in account may sign on this ticket. */
    public static function slotsFor(PDO $pdo, array $facts, int $accountId, string $realRole): array
    {
        $stmt = $pdo->prepare('SELECT employee_id FROM tblemployee WHERE account_id = ? LIMIT 1');
        $stmt->execute([$accountId]);
        $employeeId = (int) $stmt->fetchColumn();

        $slots = [];
        if ((int) $facts['requester_account_id'] === $accountId
            || ($employeeId > 0 && (int) $facts['employee_id'] === $employeeId)) {
            $slots[] = 'ACKNOWLEDGED';
        }
        $itPerson = (int) ($facts['performed_by'] ?: $facts['assigned_to']);
        if ($employeeId > 0 && $itPerson === $employeeId) {
            $slots[] = 'REPORTED';
        }
        if ($realRole === 'ADMIN') {
            $slots[] = 'VERIFIED';
        }
        return $slots;
    }

    /** Validates a data URL from the signing pad; returns the clean data URL or null. */
    public static function cleanImage(string $dataUrl): ?string
    {
        if (!preg_match('#^data:image/(jpeg|png);base64,([A-Za-z0-9+/=]+)$#', $dataUrl, $m)) {
            return null;
        }
        $binary = base64_decode($m[2], true);
        if ($binary === false || strlen($binary) > self::MAX_BYTES) {
            return null;
        }
        $info = @getimagesizefromstring($binary);
        if (!$info || !in_array($info['mime'], ['image/jpeg', 'image/png'], true) || $info[0] < 20 || $info[1] < 10) {
            return null;
        }
        return 'data:' . $info['mime'] . ';base64,' . base64_encode($binary);
    }

    /** Clamp the stored placement so the signature stays inside its box. */
    public static function placement(int $x, int $y, int $scale): array
    {
        $scale = max(50, min(140, $scale));
        $width = (int) round(self::BASE_WIDTH * $scale / 100);
        return [
            'scale' => $scale,
            'width' => $width,
            'x' => max(0, min(self::AREA_W - $width, $x)),
            'y' => max(0, min(self::AREA_H - 16, $y)),
        ];
    }

    /** <img> for a box (PDF and screen use the same markup). */
    public static function imageHtml(?array $signature): string
    {
        if (!$signature) {
            return '';
        }
        $p = self::placement((int) $signature['offset_x'], (int) $signature['offset_y'], (int) $signature['scale_pct']);
        return '<img class="sign-img" alt="Signature" src="' . htmlspecialchars((string) $signature['image_data'], ENT_QUOTES, 'UTF-8')
            . '" style="display:block;width:' . $p['width'] . 'px;margin-left:' . $p['x'] . 'px;margin-top:' . $p['y'] . 'px">';
    }
}
