<?php
// app/Helpers/RBAC.php

/**
 * RBAC (Role-Based Access Control) Helper
 * Provides utilities for role-based authorization and access control
 */
class RBAC
{
    // Role definitions
    const ROLE_ADMIN = 'ADMIN';
    const ROLE_EMPLOYEE = 'EMPLOYEE';
    const ROLE_HEAD = 'HEAD';
    const ROLE_HR = 'HR';
    const ROLE_IT = 'IT';
    const ROLE_AOM = 'AOM';
    const ROLE_OM = 'OM';

    // Available roles
    private static $availableRoles = [
        self::ROLE_ADMIN,
        self::ROLE_EMPLOYEE,
        self::ROLE_HEAD,
        self::ROLE_HR,
        self::ROLE_IT,
        self::ROLE_AOM,
        self::ROLE_OM
    ];

    // Permission matrix
    private static $permissions = [
        self::ROLE_ADMIN => [
            'view_all_branches' => true,
            'manage_branches' => true,
            'manage_employees' => true,
            'manage_aoм' => true,
            'create_tickets' => true,
            'approve_tickets' => true,
            'view_all_tickets' => true,
            'manage_assets' => true,
            'manage_roles' => true,
            'view_audit_trail' => true,
            'manage_system' => true
        ],
        self::ROLE_EMPLOYEE => [
            'view_own_branch' => true,
            'create_own_ticket' => true,
            'view_own_tickets' => true,
            'view_own_assets' => true,
            'rate_tickets' => true
        ],
        self::ROLE_HEAD => [
            'view_department_branch' => true,
            'view_department_employees' => true,
            'create_tickets' => true,
            'view_department_tickets' => true,
            'manage_department_assets' => true,
            'rate_tickets' => true
        ],
        self::ROLE_HR => [
            'manage_employees' => true,
            'manage_uniforms' => true,
            'create_tickets' => true,
            'view_all_tickets' => true,
            'manage_payroll' => true,
            'view_audit_trail' => true
        ],
        self::ROLE_IT => [
            'view_all_tickets' => true,
            'assign_tickets' => true,
            'resolve_tickets' => true,
            'manage_tickets' => true
        ],
        self::ROLE_AOM => [
            'view_assigned_branches' => true,
            'view_branch_employees' => true,
            'create_tickets' => true,
            'view_branch_tickets' => true,
            'manage_branch_operations' => true,
            'monitor_employees' => true,
            'access_branch_records' => true
        ],
        self::ROLE_OM => [
            'view_all_employees' => true,
            'view_all_aoms' => true,
            'assign_employees_to_aom' => true,
            'manage_aom_assignments' => true,
            'view_assignment_history' => true,
            'create_assignments' => true,
            'update_assignments' => true,
            'deactivate_assignments' => true,
            'view_aom_branches' => true,
            'access_assignment_records' => true
        ]
    ];

    /**
     * Check if user has a specific role
     * 
     * @param string $userRole Current user's role
     * @param string $requiredRole Required role
     * @return bool True if user has the role
     */
    public static function hasRole($userRole, $requiredRole)
    {
        $userRole = strtoupper($userRole);
        return $userRole === strtoupper($requiredRole);
    }

    /**
     * Check if user has any of the required roles
     * 
     * @param string $userRole Current user's role
     * @param array $requiredRoles Array of required roles
     * @return bool True if user has any of the roles
     */
    public static function hasAnyRole($userRole, $requiredRoles)
    {
        $userRole = strtoupper($userRole);
        foreach ($requiredRoles as $role) {
            if ($userRole === strtoupper($role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has a specific permission
     * 
     * @param string $userRole User's role
     * @param string $permission Permission to check
     * @return bool True if user has permission
     */
    public static function hasPermission($userRole, $permission)
    {
        $userRole = strtoupper($userRole);
        
        if (!isset(self::$permissions[$userRole])) {
            return false;
        }
        
        return isset(self::$permissions[$userRole][$permission]) 
            && self::$permissions[$userRole][$permission] === true;
    }

    /**
     * Check if user has all required permissions
     * 
     * @param string $userRole User's role
     * @param array $permissions Array of permissions
     * @return bool True if user has all permissions
     */
    public static function hasAllPermissions($userRole, $permissions)
    {
        foreach ($permissions as $permission) {
            if (!self::hasPermission($userRole, $permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get all permissions for a role
     * 
     * @param string $role The role
     * @return array Permissions for the role
     */
    public static function getRolePermissions($role)
    {
        $role = strtoupper($role);
        return isset(self::$permissions[$role]) ? self::$permissions[$role] : [];
    }

    /**
     * Verify AOM can access employee
     * 
     * @param int $aom_employee_id AOM's employee ID
     * @param int $employee_id Employee's employee ID
     * @param object $aomModel The AOM model instance
     * @return bool True if AOM can access employee
     */
    public static function aomCanAccessEmployee($aom_employee_id, $employee_id, $aomModel)
    {
        return $aomModel->hasAccessToEmployee($aom_employee_id, $employee_id);
    }

    /**
     * Verify AOM can access branch
     * 
     * @param int $aom_employee_id AOM's employee ID
     * @param int $branch_id Branch ID
     * @param object $aomModel The AOM model instance
     * @return bool True if AOM can access branch
     */
    public static function aomCanAccessBranch($aom_employee_id, $branch_id, $aomModel)
    {
        return $aomModel->hasAccessToBranch($aom_employee_id, $branch_id);
    }

    /**
     * Verify AOM can access ticket
     * 
     * @param int $aom_employee_id AOM's employee ID
     * @param int $ticket_id Ticket ID
     * @param object $aomTicketModel The AOM ticket model instance
     * @return bool True if AOM can access ticket
     */
    public static function aomCanAccessTicket($aom_employee_id, $ticket_id, $aomTicketModel)
    {
        $ticket = $aomTicketModel->getTicketByIdForAOM($ticket_id, $aom_employee_id);
        return $ticket !== false;
    }

    /**
     * Check if role is valid
     * 
     * @param string $role Role to check
     * @return bool True if role is valid
     */
    public static function isValidRole($role)
    {
        return in_array(strtoupper($role), self::$availableRoles);
    }

    /**
     * Get all available roles
     * 
     * @return array List of available roles
     */
    public static function getAvailableRoles()
    {
        return self::$availableRoles;
    }

    /**
     * Enforce role requirement - exits if unauthorized
     * 
     * @param string $userRole User's current role
     * @param string|array $requiredRole Required role(s)
     * @param string $message Error message
     * @return void
     */
    public static function enforceRole($userRole, $requiredRole, $message = 'Unauthorized access.')
    {
        if (is_array($requiredRole)) {
            if (!self::hasAnyRole($userRole, $requiredRole)) {
                http_response_code(403);
                exit($message);
            }
        } else {
            if (!self::hasRole($userRole, $requiredRole)) {
                http_response_code(403);
                exit($message);
            }
        }
    }

    /**
     * Enforce permission requirement - exits if unauthorized
     * 
     * @param string $userRole User's current role
     * @param string|array $permission Required permission(s)
     * @param string $message Error message
     * @return void
     */
    public static function enforcePermission($userRole, $permission, $message = 'Insufficient permissions.')
    {
        if (is_array($permission)) {
            if (!self::hasAllPermissions($userRole, $permission)) {
                http_response_code(403);
                exit($message);
            }
        } else {
            if (!self::hasPermission($userRole, $permission)) {
                http_response_code(403);
                exit($message);
            }
        }
    }
}
