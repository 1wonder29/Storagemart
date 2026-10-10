<?php

/**
 * "Confirm received" for items HR issues (tbluniform_assignment.received_at).
 * The employee confirms in My Issued Items; HR sees the date in the assignment lists.
 * Everything here is quiet when the database update has not been imported yet.
 */
class UniformReceipts
{
    private static ?bool $ready = null;

    public static function ready(): bool
    {
        if (self::$ready === null) {
            global $pdo;
            try {
                $pdo->query('SELECT received_at FROM tbluniform_assignment LIMIT 1');
                self::$ready = true;
            } catch (Throwable $e) {
                self::$ready = false;
            }
        }
        return self::$ready;
    }

    /**
     * @param int[] $assignmentIds
     * @return array<int, array{received_at: ?string, received_note: ?string}> by assignment id
     */
    public static function forAssignments(array $assignmentIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $assignmentIds))));
        if (!$ids || !self::ready()) {
            return [];
        }
        global $pdo;
        $out = [];
        foreach (array_chunk($ids, 500) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            $stmt = $pdo->prepare("SELECT assignment_id, received_at, received_note FROM tbluniform_assignment WHERE assignment_id IN ($in)");
            $stmt->execute($chunk);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $out[(int) $row['assignment_id']] = ['received_at' => $row['received_at'], 'received_note' => $row['received_note']];
            }
        }
        return $out;
    }

    /** The employee confirms receipt of one of their own active items. Returns false when not allowed. */
    public static function confirm(int $assignmentId, int $employeeId): bool
    {
        if (!self::ready()) {
            return false;
        }
        global $pdo;
        $stmt = $pdo->prepare('UPDATE tbluniform_assignment SET received_at = NOW()
                WHERE assignment_id = ? AND employee_id = ? AND received_at IS NULL AND date_returned IS NULL');
        $stmt->execute([$assignmentId, $employeeId]);
        return $stmt->rowCount() > 0;
    }

    /** Small HTML badge for HR lists: "Received Oct 10, 2026" or "Not confirmed yet". */
    public static function badge(?array $receipt): string
    {
        if (!self::ready() || $receipt === null) {
            return '';
        }
        if (!empty($receipt['received_at'])) {
            $title = !empty($receipt['received_note']) ? ' title="' . htmlspecialchars((string) $receipt['received_note']) . '"' : '';
            return '<span class="badge badge-success"' . $title . '><i class="fas fa-check mr-1"></i>Received '
                . htmlspecialchars(date('M j, Y', strtotime((string) $receipt['received_at']))) . '</span>';
        }
        return '<span class="badge badge-warning">Not confirmed yet</span>';
    }
}
