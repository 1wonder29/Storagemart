<?php
// app/Controllers/hom/HOMController.php

require_once __DIR__ . '/../AuthController.php';
require_once __DIR__ . '/../../Models/employee/Employee.php';
require_once __DIR__ . '/../../Models/hom/HOMModel.php';
require_once __DIR__ . '/../../Models/aom/AOMModel.php';
require_once __DIR__ . '/../../Models/employee/Ticket.php';
require_once __DIR__ . '/../../Helpers/Session.php';
require_once __DIR__ . '/../../Helpers/ActivityLogger.php';

/**
 * HOMController - Head Of Operation Controller
 * Manages employee assignments to AOMs
 */
class HOMController extends AuthController
{
    protected $homModel;
    protected $employeeModel;

    public function __construct()
    {
        parent::__construct();
        $this->homModel = new HOMModel();
        $this->employeeModel = new Employee();
    }

    /**
     * Check if user is HOM
     */
    protected function requireHOM()
    {
        if (empty($_SESSION['account_id'])) {
            $_SESSION['loginMessage'] = 'Please log in to continue.';
            $this->redirect('/login');
            return false;
        }

        $user = $this->employeeModel->fetchUserDetails($_SESSION['account_id']);
        $role = strtoupper($user['usertype'] ?? '');
        if (!$user || !in_array($role, ['HOM', 'OM'], true)) {
            http_response_code(403);
            exit('Unauthorized: This area requires HOM access.');
        }

        return $user;
    }

    /**
     * HOM Dashboard - View assignments overview
     */
    public function dashboard()
    {
        $user = $this->requireHOM();
        if (!$user) return;

        $accountId = (int) $_SESSION['account_id'];
        
        // Get all Operations department tickets for HOM oversight
        $ticketModel = new EmployeeTicket();
        $role = strtoupper($user['usertype'] ?? '');
        $tickets = $role === 'HOM'
            ? $ticketModel->fetchTicketsByDepartment('Operations')
            : $ticketModel->getTicketsByCreatedBy($accountId);
        
        // Count tickets by status
        $ticketStats = ['total' => 0, 'open' => 0, 'in_progress' => 0, 'completed' => 0];
        $ticketStats['total'] = count($tickets);
        foreach ($tickets as $t) {
            $status = strtolower($t['status'] ?? 'open');
            if ($status === 'completed') {
                $ticketStats['completed']++;
            } elseif ($status === 'in progress') {
                $ticketStats['in_progress']++;
            } else {
                $ticketStats['open']++;
            }
        }

        // Get dashboard overview counts
        $employeeCount = count($this->homModel->getOperationsEmployees());
        $branchCount = count($this->homModel->getAllBranches());
        $aomCount = count($this->homModel->getAllActiveAOMs());
        
        // Get recent tickets (last 5)
        $recentTickets = array_slice($tickets, 0, 5);

        $data = [
            'page_title' => 'Dashboard',
            'user' => $user,
            'employeeCount' => $employeeCount,
            'branchCount' => $branchCount,
            'aomCount' => $aomCount,
            'ticketStats' => $ticketStats,
            'recentTickets' => $recentTickets,
            'user_role' => 'HOM'
        ];

        extract($data);
        require __DIR__ . '/../../Views/om/dashboard.php';
    }

    /**
     * View Operations employees and manage branch transfers
     */
    public function employees()
    {
        $user = $this->requireHOM();
        if (!$user) return;

        $role = strtoupper($user['usertype'] ?? '');
        $employees = $this->homModel->getOperationsEmployees();
        $branches = $this->homModel->getAllBranches();

        $data = [
            'page_title' => 'Operations Employees',
            'user' => $user,
            'employees' => $employees,
            'branches' => $branches,
            'user_role' => $role === 'OM' ? 'OM' : 'HOM',
            'routePrefix' => $role === 'OM' ? 'om' : 'hom',
        ];

        extract($data);
        require __DIR__ . '/../../Views/om/employees.php';
    }

    /**
     * View Operations assets across all branches
     */
    public function assets()
    {
        $user = $this->requireHOM();
        if (!$user) return;

        $role = strtoupper($user['usertype'] ?? '');
        $viewerEmployeeId = (int) ($user['employee_id'] ?? 0);
        $myAssets = $viewerEmployeeId > 0
            ? $this->employeeModel->fetchAssetDetailsByEmployeeId($viewerEmployeeId)
            : [];
        $teamAssets = $this->homModel->getOperationsTeamAssets(
            null,
            $viewerEmployeeId > 0 ? $viewerEmployeeId : null
        );
        $branches = $this->homModel->getAllBranches();

        $data = [
            'page_title' => 'Operations Assets',
            'user' => $user,
            'myAssets' => $myAssets,
            'teamAssets' => $teamAssets,
            'branches' => $branches,
            'user_role' => $role === 'OM' ? 'OM' : 'HOM',
            'routePrefix' => $role === 'OM' ? 'om' : 'hom',
            'teamEmptyMessage' => 'No assets found for Operations employees.',
        ];

        extract($data);
        require __DIR__ . '/../../Views/om/asset/assets.php';
    }

    /**
     * Transfer an employee from their current branch to another branch
     */
    public function transferEmployee()
    {
        $user = $this->requireHOM();
        if (!$user) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed.');
        }

        $role = strtoupper($user['usertype'] ?? '');
        $routePrefix = $role === 'OM' ? 'om' : 'hom';
        $employeeId = (int) ($_POST['employee_id'] ?? 0);
        $newBranchId = (int) ($_POST['branch_id'] ?? 0);

        $result = $this->homModel->transferEmployeeBranch($employeeId, $newBranchId);

        if ($result['success']) {
            $performedBy = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
            $transferModule = $role === 'OM' ? 'OM - Employees' : 'HOM - Employees';
            ActivityLogger::transfer(
                $transferModule,
                (string) $employeeId,
                sprintf(
                    'Transferred %s from %s to %s',
                    $result['employee_name'] ?? 'employee',
                    $result['old_branch_name'] ?? 'previous branch',
                    $result['new_branch_name'] ?? 'new branch'
                ),
                $performedBy,
                [
                    'old_branch_id' => $result['old_branch_id'] ?? null,
                    'new_branch_id' => $result['new_branch_id'] ?? null,
                    'old_branch_name' => $result['old_branch_name'] ?? null,
                    'new_branch_name' => $result['new_branch_name'] ?? null,
                ]
            );
            $_SESSION['success_message'] = $result['message'];
        } else {
            $_SESSION['error_message'] = $result['message'];
        }

        $this->redirect("/$routePrefix/employees");
    }

    /**
     * List AOMs and their assigned branches
     */
    public function aomBranches()
    {
        $user = $this->requireHOM();
        if (!$user) return;

        $aomModel = new AOMModel();
        $activeAOMs = $this->homModel->getAllActiveAOMs();
        $aoms = [];

        foreach ($activeAOMs as $aom) {
            $branches = $aomModel->getAssignedBranches($aom['employee_id']);
            $aoms[] = [
                'employee_id' => $aom['employee_id'],
                'firstname' => $aom['firstname'],
                'lastname' => $aom['lastname'],
                'email' => $aom['email'],
                'branches' => $branches,
                'branch_count' => count($branches),
            ];
        }

        $data = [
            'page_title' => 'AOM Branch Assignments',
            'user' => $user,
            'aoms' => $aoms,
            'user_role' => 'HOM',
            'routePrefix' => (strpos($_SERVER['REQUEST_URI'] ?? '', '/om/') !== false) ? 'om' : 'hom',
        ];

        extract($data);
        require __DIR__ . '/../../Views/om/aom-branches.php';
    }

    /**
     * Edit branch assignments for an AOM
     */
    public function editAOMBranches()
    {
        $user = $this->requireHOM();
        if (!$user) return;

        $aomEmployeeId = (int)($_GET['id'] ?? $_POST['aom_employee_id'] ?? 0);
        if ($aomEmployeeId <= 0) {
            http_response_code(404);
            exit('AOM not found.');
        }

        $activeAOMs = $this->homModel->getAllActiveAOMs();
        $aom = null;
        foreach ($activeAOMs as $candidate) {
            if ((int)$candidate['employee_id'] === $aomEmployeeId) {
                $aom = $candidate;
                break;
            }
        }

        if (!$aom) {
            http_response_code(404);
            exit('AOM not found.');
        }

        $aomModel = new AOMModel();
        $branches = $this->homModel->getAllBranches();

        $role = strtoupper($user['usertype'] ?? '');
        $routePrefix = (strpos($_SERVER['REQUEST_URI'] ?? '', '/om/') !== false) ? 'om' : 'hom';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $branchIds = isset($_POST['aom_branch_ids']) ? (array)$_POST['aom_branch_ids'] : [];
            $result = $aomModel->updateAOMBranchAssignments(
                $aomEmployeeId,
                $branchIds,
                $user['employee_id']
            );

            if (!empty($result['success'])) {
                $addedIds = $result['added_branch_ids'] ?? [];
                $removedIds = $result['removed_branch_ids'] ?? [];

                if (!empty($addedIds) || !empty($removedIds)) {
                    $aomName = trim(($aom['firstname'] ?? '') . ' ' . ($aom['lastname'] ?? ''));
                    $performedBy = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
                    $performedRole = $role === 'OM' ? 'OM' : 'HOM';
                    $transferModule = $role === 'OM' ? 'OM - AOM Branches' : 'HOM - AOM Branches';
                    $addedNames = $aomModel->getBranchNamesByIds($addedIds);
                    $removedNames = $aomModel->getBranchNamesByIds($removedIds);

                    $description = sprintf('Updated branch assignments for %s', $aomName);
                    if (!empty($addedNames)) {
                        $description .= ' | Added: ' . implode(', ', $addedNames);
                    }
                    if (!empty($removedNames)) {
                        $description .= ' | Removed: ' . implode(', ', $removedNames);
                    }

                    ActivityLogger::transfer(
                        $transferModule,
                        (string) $aomEmployeeId,
                        $description,
                        $performedBy,
                        [
                            'added_branch_ids' => $addedIds,
                            'removed_branch_ids' => $removedIds,
                            'added_branch_names' => $addedNames,
                            'removed_branch_names' => $removedNames,
                        ]
                    );

                    $aomModel->logBranchAssignmentTicketHistory(
                        $addedIds,
                        $removedIds,
                        (int) $user['employee_id'],
                        $performedRole,
                        $aomName,
                        $performedBy
                    );
                }

                $_SESSION['success_message'] = 'Branch assignments updated successfully.';
                $this->redirect("/$routePrefix/aom-branches");
            }

            $_SESSION['error_message'] = $result['message'] ?? 'Failed to update branch assignments.';
            $this->redirect("/$routePrefix/edit-aom-branches?id=$aomEmployeeId");
            return;
        }

        $assignedBranches = $aomModel->getAssignedBranches($aomEmployeeId);
        $assignmentHistory = $aomModel->getBranchAssignmentHistory($aomEmployeeId);

        $data = [
            'page_title' => 'Edit AOM Branch Assignments',
            'user' => $user,
            'aom' => $aom,
            'branches' => $branches,
            'assigned_branches' => $assignedBranches,
            'assignment_history' => $assignmentHistory,
            'user_role' => 'HOM',
            'routePrefix' => $routePrefix,
        ];

        extract($data);
        require __DIR__ . '/../../Views/om/edit-aom-branches.php';
    }
}
