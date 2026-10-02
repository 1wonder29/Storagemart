<?php
require_once __DIR__ . '/../../../config/config.php';

class AOMTicketRatingModel
{
    protected $db;

    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
    }

    public function hasRated($ticketId, $aomId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM ticket_ratings
            WHERE ticket_id = ? AND employee_id = ?
        ");
        $stmt->execute([(int)$ticketId, (int)$aomId]);

        return $stmt->fetchColumn() > 0;
    }

    public function getByTicketAndEmployee($ticketId, $aomId)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM ticket_ratings WHERE ticket_id = ? AND employee_id = ? LIMIT 1"
        );
        $stmt->execute([(int)$ticketId, (int)$aomId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateById($id, $rating, $comment = '')
    {
        $stmt = $this->db->prepare(
            "UPDATE ticket_ratings SET rating = ?, comment = ? WHERE id = ?"
        );
        return $stmt->execute([(int)$rating, trim($comment), (int)$id]);
    }

    public function create($ticketId, $aomId, $itId, $rating, $comment = '')
    {
        $stmt = $this->db->prepare("
            INSERT INTO ticket_ratings
                (ticket_id, employee_id, it_id, rating, comment, created_at)
            VALUES
                (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            (int)$ticketId,
            (int)$aomId,
            (int)$itId,
            (int)$rating,
            trim($comment)
        ]);
    }
}
