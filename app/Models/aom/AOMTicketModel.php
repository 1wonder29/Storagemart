<?php
// app/Models/aom/AOMTicketModel.php

require_once __DIR__ . '/../admin/BaseModel.php';

/**
 * AOMTicketModel - Manages tickets created and managed by AOMs
 * Handles ticket creation, updates, and branch-specific ticket operations
 */
class AOMTicketModel extends BaseModel
{
    private const OPERATIONS_DEPARTMENT = 'Operations';

    protected $tbltickets = 'tbltickets';
    protected $tblticket_history = 'tblticket_history';
    protected $tblemployee = 'tblemployee';
    protected $tblbranch = 'tblbranch';
    protected $tbllogs = 'tbllogs';

    /**
     * Create a new ticket for a specific branch
     * 
     * @param array $data Ticket data
     * @return int|false Ticket ID or false on failure
     */
    public function createTicket($data)
    {
        try {
            $sql = "
                INSERT INTO {$this->tbltickets} (
                    employee_id,
                    branch_id,
                    department,
                    category,
                    concern_details,
                    priority,
                    status,
                    aom_id,
                    created_by_role,
                    created_by,
                    date_filed,
                    inventory_id
                ) VALUES (
                    :employee_id,
                    :branch_id,
                    :department,
                    :category,
                    :concern_details,
                    :priority,
                    'Pending',
                    :aom_id,
                    'AOM',
                    :created_by,
                    NOW(),
                    :inventory_id
                )
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'employee_id' => $data['employee_id'] ?? null,
                'branch_id' => $data['branch_id'],
                'department' => $data['department'] ?? null,
                'category' => $data['category'] ?? null,
                'concern_details' => $data['concern_details'] ?? null,
                'priority' => $data['priority'] ?? 'Low',
                'aom_id' => $data['aom_id'],
                'created_by' => $data['created_by'],
                'inventory_id' => $data['inventory_id'] ?? null,
            ]);

            $ticketId = $this->pdo->lastInsertId();
            
            // Generate ticket number
            $this->generateTicketNumber($ticketId);

            // Insert initial ticket history row (match Employee/IT pattern)
            $performedBy = (int)($data['performed_by'] ?? 0); // employee_id if available
            if ($performedBy <= 0) {
                // fallback to AOM employee id if caller didn't pass performed_by
                $performedBy = (int)($data['aom_id'] ?? 0);
            }
            $this->logTicketHistory(
                (int)$ticketId,
                'Created',
                'Ticket filed by AOM',
                null,
                'Pending',
                $performedBy,
                'AOM'
            );
            
            return $ticketId;
        } catch (Exception $e) {
            error_log('Error creating ticket: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a unique ticket number
     * 
     * @param int $ticketId The ticket ID
     * @return string The generated ticket number
     */
    private function generateTicketNumber($ticketId)
    {
        $date = date('Ymd');
        $ticketNumber = "STM-{$date}-" . str_pad($ticketId, 4, '0', STR_PAD_LEFT);
        
        $sql = "UPDATE {$this->tbltickets} SET ticket_number = :ticket_number WHERE ticket_id = :ticket_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['ticket_number' => $ticketNumber, 'ticket_id' => $ticketId]);
        
        return $ticketNumber;
    }

    /**
     * Get ticket by ID with authorization check
     * 
     * @param int $ticketId The ticket ID
     * @param int $aom_employee_id The AOM's employee ID
     * @return array|false Ticket data or false if unauthorized
     */
    public function getTicketByIdForAOM($ticketId, $aom_employee_id)
    {
        $sql = "
            SELECT 
                t.*,
                e.firstname as employee_firstname,
                e.lastname as employee_lastname,
                e.email as employee_email,
                b.branchName,
                b.branchCode
            FROM {$this->tbltickets} t
            LEFT JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
            JOIN {$this->tblbranch} b ON t.branch_id = b.branch_id
            WHERE t.ticket_id = :ticket_id
            AND (
                (
                    t.branch_id IN (
                        SELECT branch_id FROM tblbranch_assignments
                        WHERE aom_employee_id = :aom_employee_id AND is_active = 1
                    )
                    AND COALESCE(e.department, t.department) = :operations_dept
                )
                OR t.employee_id IN (
                    SELECT oea.employee_id
                    FROM tblhom_employee_assignments oea
                    JOIN {$this->tblemployee} emp ON oea.employee_id = emp.employee_id
                    WHERE oea.aom_id = :aom_employee_id_2
                      AND oea.is_active = 1
                      AND emp.department = :operations_dept_2
                )
            )
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'ticket_id' => $ticketId,
            'aom_employee_id' => $aom_employee_id,
            'aom_employee_id_2' => $aom_employee_id,
            'operations_dept' => self::OPERATIONS_DEPARTMENT,
            'operations_dept_2' => self::OPERATIONS_DEPARTMENT,
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update ticket status
     * 
     * @param int $ticketId The ticket ID
     * @param string $newStatus The new status
     * @param int $updated_by User ID who updated
     * @param string $remarks Update remarks
     * @return bool Success
     */
    public function updateTicketStatus($ticketId, $newStatus, $updated_by, $remarks = null)
    {
        try {
            // Get current status
            $sql = "SELECT status FROM {$this->tbltickets} WHERE ticket_id = :ticket_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['ticket_id' => $ticketId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $oldStatus = $result['status'] ?? null;

            // Update status
            $sql = "
                UPDATE {$this->tbltickets} 
                SET status = :status, 
                    remarks = :remarks,
                    last_updated = NOW()
                WHERE ticket_id = :ticket_id
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'status' => $newStatus,
                'remarks' => $remarks,
                'ticket_id' => $ticketId
            ]);

            // Log the action (use employee_id where possible)
            $this->logTicketHistory(
                (int)$ticketId,
                $newStatus,
                "Status changed from {$oldStatus} to {$newStatus}",
                $oldStatus,
                $newStatus,
                (int)$updated_by,
                'AOM'
            );

            return true;
        } catch (Exception $e) {
            error_log('Error updating ticket status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log ticket history
     * 
     * @param int $ticketId Ticket ID
     * @param string $actionType Type of action
     * @param string $details Action details
     * @param string $oldStatus Old status
     * @param string $newStatus New status
     * @param int $performedBy User ID
     * @param string $performedRole User's role
     * @return bool Success
     */
    private function logTicketHistory($ticketId, $actionType, $details, $oldStatus, $newStatus, $performedBy, $performedRole)
    {
        try {
            $sql = "
                INSERT INTO {$this->tblticket_history} (
                    ticket_id, action_type, action_details, old_status, new_status, 
                    performed_by, performed_role, date_logged
                ) VALUES (
                    :ticket_id, :action_type, :action_details, :old_status, :new_status,
                    :performed_by, :performed_role, NOW()
                )
            ";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'ticket_id' => $ticketId,
                'action_type' => $actionType,
                'action_details' => $details,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'performed_by' => $performedBy,
                'performed_role' => $performedRole
            ]);
        } catch (Exception $e) {
            error_log('Error logging ticket history: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get ticket history
     * 
     * @param int $ticketId The ticket ID
     * @return array History records
     */
    public function getTicketHistory($ticketId)
    {
        $sql = "
            SELECT
                th.action_type,
                th.action_details,
                th.old_status,
                th.new_status,
                th.date_logged,
                CONCAT(e.lastname, ', ', e.firstname) AS performed_by
            FROM {$this->tblticket_history} th
            LEFT JOIN {$this->tblemployee} e ON th.performed_by = e.employee_id
            WHERE th.ticket_id = :ticket_id
            ORDER BY th.date_logged DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['ticket_id' => $ticketId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get tickets by branch
     * 
     * @param int $branchId The branch ID
     * @param string $status Optional status filter
     * @param int $limit Limit results
     * @return array Tickets
     */
    public function getTicketsByBranch($branchId, $status = null, $limit = 50)
    {
        $sql = "
            SELECT 
                t.*,
                e.firstname as employee_firstname,
                e.lastname as employee_lastname,
                b.branchName
            FROM {$this->tbltickets} t
            LEFT JOIN {$this->tblemployee} e ON t.employee_id = e.employee_id
            JOIN {$this->tblbranch} b ON t.branch_id = b.branch_id
            WHERE t.branch_id = :branch_id
              AND COALESCE(e.department, t.department) = :operations_dept
        ";
        
        if ($status) {
            $sql .= " AND t.status = :status";
        }
        
        $sql .= " ORDER BY t.date_filed DESC LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':branch_id', $branchId, PDO::PARAM_INT);
        $stmt->bindValue(':operations_dept', self::OPERATIONS_DEPARTMENT);
        if ($status) {
            $stmt->bindValue(':status', $status);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
