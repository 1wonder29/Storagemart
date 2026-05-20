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
            WHERE ticket_id = ? AND aom_id = ?
        ");
        $stmt->execute([(int)$ticketId, (int)$aomId]);

        return $stmt->fetchColumn() > 0;
    }

    public function create($ticketId, $aomId, $itId, $rating, $comment = '')
    {
        $stmt = $this->db->prepare("
            INSERT INTO ticket_ratings
                (ticket_id, aom_id, it_id, rating, comment, created_at)
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
