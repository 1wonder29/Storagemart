<?php
require_once __DIR__ . '/../admin/BaseModel.php';

class ItRatingsModel extends BaseModel {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get all ratings received by an IT person
     */
    public function getRatingsForItPerson($itId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                tr.id,
                tr.ticket_id,
                tr.rating,
                tr.comment,
                tr.created_at,
                t.ticket_number,
                t.concern_details,
                t.category,
                e.firstname,
                e.lastname,
                e.department
            FROM ticket_ratings tr
            JOIN tbltickets t ON tr.ticket_id = t.ticket_id
            JOIN tblemployee e ON tr.employee_id = e.employee_id
            WHERE tr.it_id = ?
            ORDER BY tr.created_at DESC
        ");
        $stmt->execute([(int)$itId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get rating statistics for IT person
     */
    public function getStatsForItPerson($itId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_ratings,
                ROUND(AVG(rating), 2) as avg_rating,
                MIN(rating) as min_rating,
                MAX(rating) as max_rating
            FROM ticket_ratings
            WHERE it_id = ?
        ");
        $stmt->execute([(int)$itId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get rating distribution (breakdown by stars)
     */
    public function getRatingDistribution($itId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                rating,
                COUNT(*) as count
            FROM ticket_ratings
            WHERE it_id = ?
            GROUP BY rating
            ORDER BY rating DESC
        ");
        $stmt->execute([(int)$itId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
