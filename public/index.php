<?php
// Error handling - disabled for production
$isProduction = getenv('APP_ENV') === 'production' || !isset($_SERVER['HTTP_HOST']) || strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') === false;

if ($isProduction) {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../app/logs/php_errors.log');
    error_reporting(E_ALL);
} else {
    // Development: show errors
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $path;

    if (is_file($file)) {
        return false;
    }
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Helpers/Session.php';

Session::start();

// Normalize URI - no subfolder stripping needed for root domain
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = '/' . trim($uri, '/');

// ROUTES
// HOME ROUTE
if ($uri === '/' || $uri === '') {
    header('Location: ' . rtrim(BASE_URL, '/') . '/login');
    exit;
}

// LOGIN POST (exact match)
if ($uri === '/login-post') {
    require_once __DIR__ . '/../app/Controllers/AuthController.php';
    (new AuthController())->login();
    exit;
}

// LOGIN PAGE (exact match)
if ($uri === '/login') {
    require_once __DIR__ . '/../app/Controllers/AuthController.php';
    (new AuthController())->show();
    exit;
}

// FORGOT PASSWORD PAGE
if ($uri === '/forgot-password' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once __DIR__ . '/../app/Controllers/AuthController.php';
    (new AuthController())->showForgotPassword();
    exit;
}

// FORGOT PASSWORD SUBMIT
if ($uri === '/forgot-password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../app/Controllers/AuthController.php';
    (new AuthController())->resetPassword();
    exit;
}

// LOGOUT (exact match)
if ($uri === '/logout') {
    require_once __DIR__ . '/../app/Controllers/AuthController.php';
    (new AuthController())->logout();
    exit;
}

// MARK NOTIFICATION AS READ (AJAX)
if ($uri === '/notifications/read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../app/Controllers/NotificationController.php';
    (new NotificationController())->markRead();
    exit;
}

// NOTIFICATIONS LIST PAGE
if ($uri === '/notifications' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once __DIR__ . '/../app/Controllers/NotificationController.php';
    (new NotificationController())->index();
    exit;
}

// ADMIN PREFIX routes
if (strpos($uri, '/admin') === 0) {
    require_once __DIR__ . '/../app/Controllers/admin/AdminController.php';
    require_once __DIR__ . '/../app/Controllers/admin/TicketController.php';
    require_once __DIR__ . '/../app/Controllers/admin/AssetController.php';
    $admin = new AdminController();
    $ticket = new TicketController();
    $asset = new AssetController();
    $sub = trim(substr($uri, strlen('/admin')), '/');

    if ($sub === '' || $sub === 'dashboard') {
        $admin->dashboard();
    } elseif ($sub === 'account') {
        $admin->account();
    } elseif ($sub === 'account/add') {
        $admin->addAccount();
    } elseif ($sub === 'account/edit') {
        $admin->editAccount();
    } elseif ($sub === 'employee') {
        $admin->employee();
    } elseif ($sub === 'profile') {
        $admin->profile();
    } elseif ($sub === 'tickets') {
        $ticket->ticket();
    } elseif ($sub === 'tickets/history') {
        $ticket->history();
    } elseif ($sub === 'tickets/update-assignment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ticket->updateAssignment();
    } elseif ($sub === 'tickets/add') {
        $ticket->add();
    } elseif ($sub === 'tickets/get-assets') {
        $ticket->getAssets();
    } elseif ($sub === 'tickets/search-employee') {
        $ticket->searchEmployee();
    } elseif ($sub === 'tickets/file' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $ticket->fileTicket();
    } elseif ($sub === 'tickets/file' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ticket->storeFile();
    } elseif ($sub === 'assets') {
        $asset->asset();
    } elseif ($sub === 'assets/branch/add') {
        $asset->branch();
    } elseif ($sub === 'assets/branch/list' || $sub === 'assets/category/list' || $sub === 'assets/group/list') {
        $asset->referenceLists();
    } elseif ($sub === 'assets/reference') {
        $asset->referenceLists();
    } elseif ($sub === 'assets/category/add') {
        $asset->category();
    } elseif ($sub === 'assets/group/add') {
        $asset->group();
    } elseif ($sub === 'assets/group/update') {
        $asset->updateGroup();
    } elseif ($sub === 'assets/item') {
        $asset->item();
    } elseif ($sub === 'assets/add') {
        $asset->addItem();
    } elseif ($sub === 'assets/item/edit' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $asset->editItem();
    } elseif ($sub === 'assets/item/update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $asset->updateItem();
    } elseif ($sub === 'assets/transfer') {
        $asset->transferItem();
    } elseif ($sub === 'assets/search-employee') {
        $asset->searchEmployee();
    } elseif ($sub === 'assets/transfer-history') {
        $asset->transferHistory();
    } elseif ($sub === 'pendings') {
        $ticket->pendings();
    } elseif ($sub === 'tickets/pending' || $sub === 'tickets/pendings') {
        $ticket->pendings();
    } elseif ($sub === 'tickets/approve-assign' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ticket->approveAssign();
    } elseif ($sub === 'tickets/decline' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ticket->decline();
    } elseif ($sub === 'assets/view') {
        $admin->view_asset();
    } elseif ($sub === 'audit-trail') {
        $admin->auditTrail();
    } elseif ($sub === 'audit-trail/detail') {
        $admin->auditDetail();
    } else {
        http_response_code(404);
        echo "Admin page not found.";
    }
    exit;
}

// Employee PREFIX routes
if (strpos($uri, '/employee') === 0) {
    require_once __DIR__ . '/../app/Controllers/employee/EmployeeController.php';
    require_once __DIR__ . '/../app/Controllers/employee/AssetController.php';
    require_once __DIR__ . '/../app/Controllers/employee/TicketController.php';

    $employee = new EmployeeController();
    $asset    = new AssetController();
    $ticket   = new EmployeeTicketController();

    $sub = trim(substr($uri, strlen('/employee')), '/');

    if ($sub === '' || $sub === 'dashboard') {
        $employee->dashboard();
    } elseif ($sub === 'profile') {
        $employee->profile();
    } elseif ($sub === 'assets') {
        $asset->asset();
    } elseif ($sub === 'assets/file_ticket' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $ticket->create();
    } elseif ($sub === 'assets/file_ticket' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ticket->store();
    } elseif ($sub === 'tickets') {
        $ticket->index();
    } elseif ($sub === 'tickets/create') {
        $ticket->create();
    } elseif ($sub === 'tickets/history') {
        $ticket->index();
    } elseif ($sub === 'tickets/history/fetch') {
        $ticket->fetchHistory();
    } elseif ($sub === 'tickets/rate' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $ticket->rate();
    } elseif ($sub === 'tickets/rate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ticket->storeRating();
    } elseif ($sub === 'tickets/download-record' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $ticket->downloadTechnicalRecord();
    } elseif ($sub === 'tickets/upload-report' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ticket->uploadTechnicalReport();
    } else {
        http_response_code(404);
        echo "Employee page not found.";
    }
    exit;
}

// IT prefix routes - BUG-25 fix: only match exactly '/it' or paths starting with '/it/'
if ($uri === '/it' || strpos($uri, '/it/') === 0) {
    require_once __DIR__ . '/../app/Controllers/it/ItController.php';
    require_once __DIR__ . '/../app/Controllers/it/AssetController.php';
    require_once __DIR__ . '/../app/Controllers/it/TicketController.php';

    $it     = new ItController();
    $asset  = new AssetController();
    $ticket = new TicketController();

    $sub = trim(substr($uri, strlen('/it')), '/');

    if ($sub === '' || $sub === 'dashboard') {
        $it->dashboard();
    } elseif ($sub === 'profile') {
        $it->profile();
    } elseif ($sub === 'uploads') {
        $it->viewUploads();
    } elseif ($sub === 'assets') {
        $asset->asset();
    } elseif ($sub === 'assets/file_ticket' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $ticket->create();
    } elseif ($sub === 'assets/file_ticket' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ticket->store();
    } elseif ($sub === 'tickets') {
        $ticket->index();
    } elseif ($sub === 'tickets/create') {
        $ticket->create();
    } elseif ($sub === 'tickets/history') {
        $ticket->history();
    } elseif ($sub === 'tickets/history/fetch') {
        $ticket->fetchHistory();
    } elseif ($sub === 'tickets/in_progress') {
        $ticket->in_progress();
    } elseif ($sub === 'tickets/update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ticket->update();
    } elseif ($sub === 'tickets/resolve') {
        $ticket->resolve();
    } else {
        http_response_code(404);
        echo "IT page not found.";
    }
    exit;
}

// Head PREFIX routes
if (strpos($uri, '/head') === 0) {
    $sub = trim(substr($uri, strlen('/head')), '/');

    // AJAX-only endpoints
    if ($sub === 'employee/tickets' || $sub === 'employee/assets' || $sub === 'employee/assets/tickets') {
        require_once __DIR__ . '/../app/Controllers/head/headEmployeeController.php';
        $headEmployee = new HeadEmployeeController();
        if ($sub === 'employee/tickets') {
            $headEmployee->tickets();
        } elseif ($sub === 'employee/assets') {
            $headEmployee->assets();
        } elseif ($sub === 'employee/assets/tickets') {
            $headEmployee->assetTickets();
        }
        exit;
    }

    require_once __DIR__ . '/../app/Controllers/head/headController.php';
    require_once __DIR__ . '/../app/Controllers/head/headAssetController.php';
    require_once __DIR__ . '/../app/Controllers/head/headTicketController.php';

    $head       = new headController();
    $headAsset  = new headAssetController();
    $headTicket = new headTicketController();

    if ($sub === '' || $sub === 'dashboard') {
        $head->dashboard();
    } elseif ($sub === 'profile') {
        $head->profile();
    } elseif ($sub === 'assets') {
        $headAsset->asset();
    } elseif ($sub === 'assets/file_ticket' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $headTicket->create();
    } elseif ($sub === 'assets/file_ticket' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $headTicket->store();
    } elseif ($sub === 'tickets') {
        $headTicket->index();
    } elseif ($sub === 'tickets/create') {
        $headTicket->create();
    } elseif ($sub === 'tickets/rate' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $headTicket->rate();
    } elseif ($sub === 'tickets/rate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $headTicket->storeRating();
    } elseif ($sub === 'tickets/history') {
        $headTicket->index();
    } elseif ($sub === 'tickets/history/fetch') {
        $headTicket->fetchHistory();
    } elseif ($sub === 'employee') {
        $head->department();
    } else {
        http_response_code(404);
        echo "Head page not found.";
    }
    exit;
}

// HR PREFIX routes
if (strpos($uri, '/hr') === 0) {
    require_once __DIR__ . '/../app/Controllers/hr/HrController.php';
    require_once __DIR__ . '/../app/Controllers/hr/UniformController.php';
    require_once __DIR__ . '/../app/Controllers/hr/HrTicketController.php';

    $hr = new HrController();
    $uniform = new UniformController();
    $hrTicket = new HrTicketController();

    $sub = trim(substr($uri, strlen('/hr')), '/');

    if ($sub === '' || $sub === 'dashboard') {
        $hr->dashboard();
    } elseif ($sub === 'employees') {
        $hr->employees();
    } elseif (strpos($sub, 'employees/detail/') === 0) {
        $employeeId = (int) substr($sub, strlen('employees/detail/'));
        $hr->employeeDetail($employeeId);
    } elseif (strpos($sub, 'employees/accountability/') === 0) {
        $employeeId = (int) substr($sub, strlen('employees/accountability/'));
        $hr->downloadAccountabilityForm($employeeId);
    } elseif (strpos($sub, 'employees/search') === 0) {
        $hr->searchEmployees();
    } elseif ($sub === 'assets/file_ticket' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $hrTicket->create();
    } elseif ($sub === 'assets/file_ticket' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $hrTicket->store();
    } elseif (strpos($sub, 'tickets/fetch-history/') === 0) {
        $pathId = (int) substr($sub, strlen('tickets/fetch-history/'));
        $hrTicket->fetchHistory($pathId > 0 ? $pathId : null);
    } elseif ($sub === 'tickets/employees-by-branch' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $hrTicket->employeesByBranchAjax();
    } elseif ($sub === 'tickets/create') {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $hrTicket->create();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hrTicket->store();
        } else {
            http_response_code(405);
            echo 'Method not allowed.';
        }
    } elseif (strpos($sub, 'tickets/view') === 0) {
        $hrTicket->ticketDetail();
    } elseif ($sub === 'tickets/download-record' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $hrTicket->downloadTechnicalRecord();
    } elseif ($sub === 'tickets/upload-report' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $hrTicket->uploadTechnicalReport();
    } elseif ($sub === 'tickets/rate' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $hrTicket->rate();
    } elseif ($sub === 'tickets/rate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $hrTicket->storeRating();
    } elseif ($sub === 'tickets') {
        $hrTicket->index();
    } elseif ($sub === 'uniforms') {
        $uniform->list();
    } elseif ($sub === 'uniforms/add') {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $uniform->addForm();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $uniform->add();
        }
    } elseif (strpos($sub, 'uniforms/edit/') === 0) {
        $uniformId = (int) substr($sub, strlen('uniforms/edit/'));
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $uniform->editForm($uniformId);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $uniform->edit($uniformId);
        }
    } elseif (strpos($sub, 'uniforms/delete/') === 0) {
        $uniformId = (int) substr($sub, strlen('uniforms/delete/'));
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $uniform->deleteConfirm($uniformId);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $uniform->delete($uniformId);
        }
    } elseif (strpos($sub, 'uniforms/search') === 0) {
        $uniform->search();
    } elseif ($sub === 'uniforms/reorder-alerts') {
        $uniform->getReorderAlerts();
    } elseif ($sub === 'uniforms/get-by-type' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $uniform->getUniformsByType();
    } elseif ($sub === 'uniforms/assign') {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $uniform->assignForm();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $uniform->assign();
        }
    } elseif (strpos($sub, 'uniforms/assignments/') === 0) {
        $uniformId = (int) substr($sub, strlen('uniforms/assignments/'));
        $uniform->assignments($uniformId);
    } elseif (strpos($sub, 'uniforms/return_confirm/') === 0) {
        $assignmentId = (int) substr($sub, strlen('uniforms/return_confirm/'));
        $uniform->returnConfirm($assignmentId);
    } elseif (strpos($sub, 'uniforms/return/') === 0) {
        $assignmentId = (int) substr($sub, strlen('uniforms/return/'));
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $uniform->processReturn($assignmentId);
        }
    } else {
        http_response_code(404);
        echo "HR page not found.";
    }
    exit;
}

// AOM PREFIX routes - Area Operation Manager
if ($uri === '/aom' || strpos($uri, '/aom/') === 0) {
    require_once __DIR__ . '/../app/Controllers/aom/AOMController.php';

    $aom = new AOMController();
    $sub = trim(substr($uri, strlen('/aom')), '/');

    if ($sub === '' || $sub === 'dashboard') {
        $aom->dashboard();
    } elseif ($sub === 'profile') {
        // Profile page (if needed)
        http_response_code(501);
        exit("Coming soon");
    } elseif ($sub === 'employees') {
        $aom->employees();
    } elseif (strpos($sub, 'employees/detail') === 0) {
        $aom->employeeDetail();
    } elseif ($sub === 'branches') {
        // List branches - similar to dashboard
        $aom->dashboard();
    } elseif (strpos($sub, 'branches/detail') === 0) {
        $aom->branchDetail();
    } elseif ($sub === 'tickets') {
        $aom->tickets();
    } elseif ($sub === 'tickets/create') {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $aom->createTicketForm();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $aom->submitTicket();
        }
    } elseif (strpos($sub, 'tickets/view') === 0) {
        $aom->ticketDetail();
    } elseif ($sub === 'tickets/update-status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $aom->updateTicketStatus();
    } elseif ($sub === 'api/employees-by-branch' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        // AJAX endpoint to get employees in a branch
        $aom->getEmployeesByBranchAjax();
    } else {
        http_response_code(404);
        echo "AOM page not found.";
    }
    exit;
}

// OM PREFIX routes - Operation Manager
if ($uri === '/om' || strpos($uri, '/om/') === 0) {
    require_once __DIR__ . '/../app/Controllers/om/OMController.php';
    require_once __DIR__ . '/../app/Controllers/om/OMTicketController.php';

    $sub = trim(substr($uri, strlen('/om')), '/');

    // Handle ticket routes
    if (strpos($sub, 'tickets') === 0) {
        $omTicket = new OMTicketController();
        
        if ($sub === 'tickets') {
            $omTicket->index();
        } elseif ($sub === 'tickets/create') {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $omTicket->create();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $omTicket->store();
            }
        } elseif (strpos($sub, 'tickets/view') === 0) {
            $omTicket->view();
        } elseif ($sub === 'tickets/upload-technical-report' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $omTicket->uploadTechnicalReport();
        } elseif ($sub === 'tickets/rate' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $omTicket->rate();
        } elseif ($sub === 'tickets/store-rating' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $omTicket->storeRating();
        } elseif ($sub === 'tickets/download-technical-record' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $omTicket->downloadTechnicalRecord();
        } else {
            http_response_code(404);
            echo "OM Ticket page not found.";
        }
        exit;
    }

    // Handle non-ticket routes
    $om = new OMController();

    if ($sub === '' || $sub === 'dashboard') {
        $om->dashboard();
    } elseif ($sub === 'employees') {
        $om->employees();
    } elseif ($sub === 'assignments') {
        $om->assignments();
    } elseif ($sub === 'new-assignment') {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $om->createAssignment();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $om->createAssignment();
        }
    } elseif (strpos($sub, 'edit-assignment') === 0) {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $om->updateAssignment();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $om->updateAssignment();
        }
    } elseif ($sub === 'deactivate-assignment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $om->deactivateAssignment();
    } elseif ($sub === 'api/unassigned-employees' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        // AJAX endpoint to get unassigned employees
        $om->getUnassignedEmployees();
    } elseif ($sub === 'api/aoms' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        // AJAX endpoint to get all AOMs
        $om->getAOMs();
    } elseif ($sub === 'api/employee-assignments' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        // AJAX endpoint to get employee assignments
        $om->getEmployeeAssignments();
    } else {
        http_response_code(404);
        echo "OM page not found.";
    }
    exit;
}

// FALLBACK
http_response_code(404);
echo "404 - Page not found.";