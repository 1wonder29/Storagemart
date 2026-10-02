<?php 
    require_once __DIR__ . '/admin/BaseModel.php';
class DashboardModel extends BaseModel
{
    protected $tbltickets = 'tbltickets';
    public function getEmployeeTicketResolutionTimes(int $employeeId)
    {
        $sql = "
            SELECT *
            FROM (
                SELECT 
                    ticket_number,
                    TIMESTAMPDIFF(HOUR, date_filed, last_updated) AS resolution_hours,
                    last_updated
                FROM {$this->tbltickets}
                WHERE status = 'Resolved'
                AND employee_id = :employee_id
                AND last_updated IS NOT NULL
                ORDER BY last_updated DESC
                LIMIT 10
            ) t
            ORDER BY last_updated ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['employee_id' => $employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDepartmentTicketResolutionTimes(string $department)
    {
        $sql = "
            SELECT *
            FROM (
                SELECT 
                    ticket_number,
                    TIMESTAMPDIFF(HOUR, date_filed, last_updated) AS resolution_hours,
                    last_updated
                FROM {$this->tbltickets}
                WHERE status = 'Resolved'
                AND department = :department
                AND last_updated IS NOT NULL
                ORDER BY last_updated DESC
                LIMIT 10
            ) t
            ORDER BY last_updated ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['department' => $department]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getItTicketResolutionTimes()
    {
        $sql = "
            SELECT *
            FROM (
                SELECT 
                    ticket_number,
                    TIMESTAMPDIFF(HOUR, date_filed, last_updated) AS resolution_hours,
                    last_updated
                FROM {$this->tbltickets}
                WHERE status = 'Resolved'
                AND last_updated IS NOT NULL
                ORDER BY last_updated DESC
                LIMIT 10
            ) t
            ORDER BY last_updated ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(); // ✅ NO PARAMS
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
