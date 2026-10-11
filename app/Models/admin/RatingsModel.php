<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/../../Helpers/AdminAccountPerson.php';

class RatingsModel extends BaseModel {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get all ratings with full details
     */
    public function getAllRatings($filters = []) {
        $query = "
            SELECT 
                tr.id,
                tr.ticket_id,
                tr.rating,
                tr.comment,
                tr.created_at,
                t.ticket_number,
                t.concern_details,
                t.category,
                rater.firstname as rater_firstname,
                rater.lastname as rater_lastname,
                rater.department as rater_department,
                tech.firstname as tech_firstname,
                tech.lastname as tech_lastname
            FROM ticket_ratings tr
            JOIN tbltickets t ON tr.ticket_id = t.ticket_id
            JOIN tblemployee rater ON tr.employee_id = rater.employee_id
            JOIN tblemployee tech ON tech.employee_id = (CASE WHEN tr.it_id = ? THEN ? ELSE tr.it_id END)
            WHERE 1=1
        ";
        
        // Ratings of the shared admin account count for the person who handles it.
        [$adminEmp, $personEmp] = AdminAccountPerson::link($this->pdo);
        $params = [$adminEmp ?: -1, $personEmp ?: -1];
        
        if (!empty($filters['start_date'])) {
            $query .= " AND DATE(tr.created_at) >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $query .= " AND DATE(tr.created_at) <= ?";
            $params[] = $filters['end_date'];
        }
        
        if (!empty($filters['it_id'])) {
            $query .= " AND (tr.it_id = ? OR (? > 0 AND ? = ? AND tr.it_id = ?))";
            array_push($params, (int)$filters['it_id'], $personEmp, (int)$filters['it_id'], $personEmp, $adminEmp ?: -1);
        }
        
        if (!empty($filters['rating'])) {
            $query .= " AND tr.rating = ?";
            $params[] = (int)$filters['rating'];
        }
        
        $query .= " ORDER BY tr.created_at DESC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get overall statistics
     */
    public function getOverallStats() {
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_ratings,
                ROUND(AVG(rating), 2) as avg_rating,
                COUNT(CASE WHEN rating = 5 THEN 1 END) as count_5star,
                COUNT(CASE WHEN rating = 4 THEN 1 END) as count_4star,
                COUNT(CASE WHEN rating = 3 THEN 1 END) as count_3star,
                COUNT(CASE WHEN rating = 2 THEN 1 END) as count_2star,
                COUNT(CASE WHEN rating = 1 THEN 1 END) as count_1star
            FROM ticket_ratings
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get IT staff performance ranking
     */
    public function getItStaffPerformance() {
        // When the shared admin account is linked to a person, its ratings are theirs
        // and "Admin ADMIN" is not listed on its own.
        [$adminEmp, $personEmp] = AdminAccountPerson::link($this->pdo);
        $stmt = $this->pdo->prepare("
            SELECT
                e.employee_id,
                e.firstname,
                e.lastname,
                COUNT(tr.id) as total_ratings,
                ROUND(AVG(tr.rating), 2) as avg_rating,
                COUNT(CASE WHEN tr.rating = 5 THEN 1 END) as count_5star
            FROM tblemployee e
            LEFT JOIN ticket_ratings tr ON (e.employee_id = tr.it_id OR (e.employee_id = ? AND tr.it_id = ?))
            WHERE UPPER(e.department) = 'IT' AND e.employee_id <> ?
            GROUP BY e.employee_id, e.firstname, e.lastname
            ORDER BY avg_rating DESC
        ");
        $stmt->execute([$personEmp ?: -1, $adminEmp ?: -1, $adminEmp ?: -1]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all IT staff for filter dropdown
     */
    public function getAllItStaff() {
        [$adminEmp] = AdminAccountPerson::link($this->pdo);
        $stmt = $this->pdo->prepare("
            SELECT employee_id, firstname, lastname
            FROM tblemployee
            WHERE UPPER(department) = 'IT' AND employee_id <> ?
            ORDER BY firstname, lastname
        ");
        $stmt->execute([$adminEmp ?: -1]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
