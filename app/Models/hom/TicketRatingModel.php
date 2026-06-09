<?php
require_once __DIR__ . '/../../../config/config.php';

class HOMTicketRatingModel
{
    protected $db;

    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
    }

    public function hasRated($ticketId, $homId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM ticket_ratings
            WHERE ticket_id = ? AND employee_id = ?
        ");
        $stmt->execute([(int)$ticketId, (int)$homId]);

        return $stmt->fetchColumn() > 0;
    }

    public function create($ticketId, $homId, $itId, $rating, $comment = '')
    {
        $stmt = $this->db->prepare("
            INSERT INTO ticket_ratings
                (ticket_id, employee_id, it_id, rating, comment, created_at)
            VALUES
                (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            (int)$ticketId,
            (int)$homId,
            (int)$itId,
            (int)$rating,
            trim($comment)
        ]);
    }
}
