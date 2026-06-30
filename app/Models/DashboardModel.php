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
                    ROUND(TIMESTAMPDIFF(MINUTE, date_filed, last_updated) / 60, 2) AS resolution_hours,
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
                    ROUND(TIMESTAMPDIFF(MINUTE, date_filed, last_updated) / 60, 2) AS resolution_hours,
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

    public function getItTicketResolutionTimes(?int $assignedToEmployeeId = null)
    {
        $params = [];
        $assigneeFilter = '';

        if ($assignedToEmployeeId !== null && $assignedToEmployeeId > 0) {
            $assigneeFilter = ' AND assigned_to = :assigned_to';
            $params['assigned_to'] = $assignedToEmployeeId;
        }

        $sql = "
            SELECT *
            FROM (
                SELECT 
                    ticket_number,
                    ROUND(TIMESTAMPDIFF(MINUTE, date_filed, last_updated) / 60, 2) AS resolution_hours,
                    last_updated
                FROM {$this->tbltickets}
                WHERE status = 'Resolved'
                AND last_updated IS NOT NULL
                {$assigneeFilter}
                ORDER BY last_updated DESC
                LIMIT 10
            ) t
            ORDER BY last_updated ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTicketCountsByCategory(): array
    {
        $sql = "
            SELECT TRIM(category) AS category, COUNT(*) AS ticket_count
            FROM {$this->tbltickets}
            WHERE category IS NOT NULL AND TRIM(category) <> ''
            GROUP BY TRIM(category)
            ORDER BY ticket_count DESC, category ASC
        ";

        $stmt = $this->pdo->query($sql);
        if (!$stmt) {
            return [];
        }

        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $category = trim((string) ($row['category'] ?? ''));
            if ($category === '') {
                continue;
            }
            $result[$category] = (int) ($row['ticket_count'] ?? 0);
        }

        return $result;
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

    public function getTicketCountsByBranch(): array
    {
        $sql = "
            SELECT
                COALESCE(NULLIF(TRIM(b.branchName), ''), 'Unassigned') AS branch_name,
                COUNT(*) AS ticket_count
            FROM {$this->tbltickets} t
            JOIN tblemployee e ON t.employee_id = e.employee_id
            LEFT JOIN tblbranch b ON b.branch_id = COALESCE(NULLIF(t.branch_id, 0), e.branch_id)
            GROUP BY COALESCE(NULLIF(TRIM(b.branchName), ''), 'Unassigned')
            ORDER BY ticket_count DESC, branch_name ASC
        ";

        $stmt = $this->pdo->query($sql);
        if (!$stmt) {
            return [];
        }

        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $branch = trim((string) ($row['branch_name'] ?? ''));
            if ($branch === '') {
                continue;
            }
            $result[$branch] = (int) ($row['ticket_count'] ?? 0);
        }

        return $result;
    }

    public function getTopReportedIssues(int $limit = 5): array
    {
        $limit = max(1, min(10, $limit));

        $sql = "
            SELECT
                TRIM(tt.technical_purpose) AS issue_name,
                COUNT(DISTINCT tt.ticket_id) AS ticket_count
            FROM tblticket_technical tt
            WHERE tt.technical_purpose IS NOT NULL
              AND TRIM(tt.technical_purpose) <> ''
            GROUP BY TRIM(tt.technical_purpose)
            ORDER BY ticket_count DESC, issue_name ASC
            LIMIT {$limit}
        ";

        $stmt = $this->pdo->query($sql);
        $result = [];

        if ($stmt) {
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $issue = trim((string) ($row['issue_name'] ?? ''));
                if ($issue === '') {
                    continue;
                }
                $result[$issue] = (int) ($row['ticket_count'] ?? 0);
            }
        }

        if (!empty($result)) {
            return $result;
        }

        $fallbackSql = "
            SELECT TRIM(category) AS issue_name, COUNT(*) AS ticket_count
            FROM {$this->tbltickets}
            WHERE category IS NOT NULL AND TRIM(category) <> ''
            GROUP BY TRIM(category)
            ORDER BY ticket_count DESC, issue_name ASC
            LIMIT {$limit}
        ";

        $fallbackStmt = $this->pdo->query($fallbackSql);
        if (!$fallbackStmt) {
            return [];
        }

        foreach ($fallbackStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $issue = trim((string) ($row['issue_name'] ?? ''));
            if ($issue === '') {
                continue;
            }
            $result[$issue] = (int) ($row['ticket_count'] ?? 0);
        }

        return $result;
    }

    public function getTicketCountsByPriority(): array
    {
        $sql = "
            SELECT TRIM(priority) AS priority, COUNT(*) AS ticket_count
            FROM {$this->tbltickets}
            WHERE priority IS NOT NULL AND TRIM(priority) <> ''
            GROUP BY TRIM(priority)
        ";

        $stmt = $this->pdo->query($sql);
        $counts = [];

        if ($stmt) {
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $priority = strtolower(trim((string) ($row['priority'] ?? '')));
                if ($priority === '') {
                    continue;
                }
                $counts[$priority] = (int) ($row['ticket_count'] ?? 0);
            }
        }

        return [
            'High (P2)' => (int) ($counts['high'] ?? 0),
            'Medium (P3)' => (int) ($counts['medium'] ?? 0),
            'Low (P4)' => (int) ($counts['low'] ?? 0),
        ];
    }

    public function getItPersonnelWorkload(int $limit = 5): array
    {
        $limit = max(1, min(10, $limit));

        $overdueCondition = "
            t.status IN ('Open', 'In Progress', 'Pending')
            AND t.date_filed < DATE_SUB(
                NOW(),
                INTERVAL CASE UPPER(TRIM(t.priority))
                    WHEN 'HIGH' THEN 2
                    WHEN 'MEDIUM' THEN 5
                    ELSE 7
                END DAY
            )
        ";

        $sql = "
            SELECT
                workload.employee_id,
                workload.personnel_name,
                workload.assigned_count,
                workload.resolved_count,
                workload.pending_count,
                workload.overdue_count
            FROM (
                SELECT
                    it.employee_id,
                    TRIM(CONCAT(it.firstname, ' ', it.lastname)) AS personnel_name,
                    SUM(
                        CASE
                            WHEN t.ticket_id IS NOT NULL
                                 AND t.status IN ('Open', 'In Progress')
                                 AND NOT ({$overdueCondition})
                            THEN 1 ELSE 0
                        END
                    ) AS assigned_count,
                    SUM(
                        CASE
                            WHEN t.status IN ('Resolved', 'Closed') THEN 1 ELSE 0
                        END
                    ) AS resolved_count,
                    SUM(
                        CASE
                            WHEN t.status = 'Pending' AND NOT ({$overdueCondition}) THEN 1 ELSE 0
                        END
                    ) AS pending_count,
                    SUM(
                        CASE
                            WHEN ({$overdueCondition}) THEN 1 ELSE 0
                        END
                    ) AS overdue_count
                FROM tblemployee it
                LEFT JOIN {$this->tbltickets} t ON t.assigned_to = it.employee_id
                WHERE UPPER(TRIM(it.department)) = 'IT'
                GROUP BY it.employee_id, it.firstname, it.lastname
            ) AS workload
            ORDER BY (
                workload.assigned_count
                + workload.resolved_count
                + workload.pending_count
                + workload.overdue_count
            ) DESC, workload.personnel_name ASC
            LIMIT {$limit}
        ";

        $stmt = $this->pdo->query($sql);
        if (!$stmt) {
            return [];
        }

        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $name = trim((string) ($row['personnel_name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $result[] = [
                'name' => $name,
                'assigned' => (int) ($row['assigned_count'] ?? 0),
                'resolved' => (int) ($row['resolved_count'] ?? 0),
                'pending' => (int) ($row['pending_count'] ?? 0),
                'overdue' => (int) ($row['overdue_count'] ?? 0),
            ];
        }

        return $result;
    }

}
