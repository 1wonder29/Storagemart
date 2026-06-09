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

    public function getTicketCountsByCategory(): array
    {
        $sql = "
            SELECT
                SUM(CASE WHEN UPPER(TRIM(category)) = 'NETWORK' THEN 1 ELSE 0 END) AS network,
                SUM(CASE WHEN UPPER(TRIM(category)) = 'SOFTWARE' THEN 1 ELSE 0 END) AS software,
                SUM(CASE WHEN UPPER(TRIM(category)) = 'HARDWARE' THEN 1 ELSE 0 END) AS hardware
            FROM {$this->tbltickets}
        ";

        $stmt = $this->pdo->query($sql);
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;

        return [
            'network'  => (int)($row['network'] ?? 0),
            'software' => (int)($row['software'] ?? 0),
            'hardware' => (int)($row['hardware'] ?? 0),
        ];
    }

    public function getTicketCountsByStatus(): array
    {
        $sql = "
            SELECT status, COUNT(*) AS count
            FROM {$this->tbltickets}
            GROUP BY status
            ORDER BY count DESC
        ";

        $stmt = $this->pdo->query($sql);
        if (!$stmt) {
            return [];
        }

        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $status = trim((string)($row['status'] ?? ''));
            if ($status === '') {
                continue;
            }
            $result[$status] = (int)$row['count'];
        }

        return $result;
    }

}
