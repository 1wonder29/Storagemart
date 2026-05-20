<?php
require_once __DIR__ . '/../../../config/config.php';

class OMTicketRatingModel
{
    protected $db;

    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
    }

    public function hasRated($ticketId, $omId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM ticket_ratings
            WHERE ticket_id = ? AND om_id = ?
        ");
        $stmt->execute([(int)$ticketId, (int)$omId]);

        return $stmt->fetchColumn() > 0;
    }

    public function create($ticketId, $omId, $itId, $rating, $comment = '')
    {
        $stmt = $this->db->prepare("
            INSERT INTO ticket_ratings
                (ticket_id, om_id, it_id, rating, comment, created_at)
            VALUES
                (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            (int)$ticketId,
            (int)$omId,
            (int)$itId,
            (int)$rating,
            trim($comment)
        ]);
    }
}
