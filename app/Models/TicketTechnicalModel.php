<?php

require_once __DIR__ . '/admin/BaseModel.php';

class TicketTechnicalModel extends BaseModel
{
    public function getLatestByTicketId(int $ticketId): ?array
    {
        if ($ticketId <= 0) {
            return null;
        }

        $sql = "
            SELECT action_taken, result, remarks, technical_purpose, date_performed
            FROM tblticket_technical
            WHERE ticket_id = :ticket_id
            ORDER BY date_performed DESC, tech_id DESC
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':ticket_id' => $ticketId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}
