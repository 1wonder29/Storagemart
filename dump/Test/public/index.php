<?php
// public/index.php - simplified + robust routing for your current setup



require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Helpers/Session.php';

// ensure session started
if (session_status() === PHP_SESSION_NONE) session_start();

// Debug: log raw request for troubleshooting (remove in production)
file_put_contents(__DIR__ . '/debug.log', date('c') . " RAW REQUEST_URI=" . ($_SERVER['REQUEST_URI'] ?? '') . " SCRIPT_NAME=" . ($_SERVER['SCRIPT_NAME'] ?? '') . PHP_EOL, FILE_APPEND);

// normalize URI and strip BASE_URL if your app is in a subfolder
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (BASE_URL !== '/' && strpos($uri, BASE_URL) === 0) {
    $uri = substr($uri, strlen(BASE_URL));
}
$uri = '/' . trim($uri, '/'); // now '/login', '/admin', '/admin/account', etc.

// Debug: log normalized URI
file_put_contents(__DIR__ . '/debug.log', date('c') . " NORMALIZED_URI=" . $uri . PHP_EOL, FILE_APPEND);

// ROUTES

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


// ADMIN PREFIX routes (everything under /admin handled here)
if (strpos($uri, '/admin') === 0) {
    require_once __DIR__ . '/../app/Controllers/admin/AdminController.php';
    require_once __DIR__ . '/../app/Controllers/admin/TicketController.php';
    require_once __DIR__ . '/../app/Controllers/admin/AssetController.php';
    $admin = new AdminController();
    $ticket = new TicketController();
    $asset = new AssetController();
    // Remove '/admin' from URL → get subpage
    $sub = trim(substr($uri, strlen('/admin')), '/');

    if ($sub === '' || $sub === 'dashboard') {
        $admin->dashboard();
    } elseif ($sub === 'account') {
        $admin->account();
    }  elseif ($sub === 'account/add') {
        $admin->addAccount();
    }  elseif ($sub === 'account/edit') {
        $admin->editAccount();
    } elseif ($sub === 'employee') {
        $admin->employee();
    } elseif ($sub === 'tickets') {
        $ticket->ticket();
    } elseif ($sub === 'tickets/history') {
        $ticket->history();
    } elseif ($sub === 'tickets/update-assignment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ticket->updateAssignment();
    } elseif ($sub === 'tickets/add') {
        $ticket->add();
    } elseif ($sub === 'tickets/get-assets'){
        $ticket->getAssets();
    } elseif ($sub === 'tickets/search-employee'){
        $ticket->searchEmployee();
    } elseif ($sub === 'tickets/file' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $ticket->fileTicket();          
    } elseif ($sub === 'tickets/file' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ticket->storeFile();      
    } elseif ($sub === 'assets') {
        $asset->asset();
    } elseif ($sub === 'assets/branch/add') {
        $asset->branch();
    } elseif ($sub === 'assets/category/add') {
        $asset->category();
    } elseif ($sub === 'assets/group/add') {
        $asset->group();
    } elseif ($sub === 'assets/group/update'){
        $asset->updateGroup();
    } elseif ($sub === 'assets/item'){
        $asset->item();
    } elseif ($sub ==='assets/add'){
        $asset->addItem();
    } elseif ($sub === 'assets/item/edit' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $asset->editItem();
    } elseif ($sub === 'assets/item/update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $asset->updateItem();
    } elseif ($sub === 'assets/transfer'){
        $asset->transferItem();
    } elseif ($sub === 'assets/search-employee') {
        $asset->searchEmployee();
    } elseif ($sub === 'assets/transfer-history'){
        $asset->transferHistory();
    } elseif ($sub === 'pendings') {
        $ticket->pendings();
    } elseif ($sub === 'tickets') {
        $ticket->ticket(); // existing route if any
    } elseif ($sub === 'tickets/pending' || $sub === 'tickets/pendings') {
        $ticket->pendings();
    } elseif ($sub === 'tickets/approve-assign' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ticket->approveAssign();
    } elseif ($sub === 'tickets/decline' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ticket->decline();
    } elseif ($sub ==='assets/view'){
        $admin->view_asset();
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

    // Remove '/employee' prefix
    $sub = trim(substr($uri, strlen('/employee')), '/');
    if ($sub === '' || $sub === 'dashboard') {
        $employee->dashboard();
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
    } elseif ($sub === 'tickets/history/fetch'){
        $ticket->fetchHistory();
    } elseif ($sub === 'tickets/rate' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $ticket->rate();
    } elseif ($sub === 'tickets/rate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ticket->storeRating();
    } else {
        http_response_code(404);
        echo "Employee page not found.";
    }
    exit;
}
//IT  prefix routes
if (strpos($uri, '/it') === 0) {

    require_once __DIR__ . '/../app/Controllers/it/ItController.php';
    require_once __DIR__ . '/../app/Controllers/it/AssetController.php';
    require_once __DIR__ . '/../app/Controllers/it/TicketController.php';

    $it = new ItController();
    $asset    = new AssetController();
    $ticket   = new TicketController();

    $sub = trim(substr($uri, strlen('/it')), '/');
    if ($sub === '' || $sub === 'dashboard') {
        $it->dashboard();
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
    } elseif ($sub === 'tickets/history/fetch'){
        $ticket->fetchHistory();
    } elseif ($sub === 'tickets/in_progress'){
        $ticket->in_progress();
    } elseif ($sub === 'tickets/update' && $_SERVER['REQUEST_METHOD'] === 'POST') { 
        $ticket->update();
    } elseif ($sub === 'tickets/resolve'){
        $ticket->resolve();
    } else {
        http_response_code(404);
        echo "IT page not found.";
    }
    exit;
}

// Head PREFIX routes
if (strpos($uri, '/head') === 0) {

    require_once __DIR__ . '/../app/Controllers/head/headController.php';
    require_once __DIR__ . '/../app/Controllers/head/headAssetController.php';
    require_once __DIR__ . '/../app/Controllers/head/headTicketController.php';

    $head = new headController();
    $headAsset    = new headAssetController();
    $headTicket   = new headTicketController();

    // Remove '/Head' prefix
    $sub = trim(substr($uri, strlen('/head')), '/');
    if ($sub === '' || $sub === 'dashboard') {
        $head->dashboard();
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
    } elseif ($sub === 'tickets/history') {
        $headTicket->history();
    } elseif ($sub === 'tickets/history/fetch'){
        $headTicket->fetchHistory();
    } elseif ($sub === 'tickets/rate' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $headTicket->rate();
    } elseif ($sub === 'tickets/rate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $headTicket->storeRating();
    } elseif ($sub === 'employee'){
        $head->department();
    } elseif ($sub === 'employee/tickets') {
        require_once __DIR__ . '/../app/Controllers/head/HeadEmployeeController.php';
        (new HeadEmployeeController())->tickets();
    } elseif ($sub === 'employee/assets') {
        require_once __DIR__ . '/../app/Controllers/head/HeadEmployeeController.php';
        (new HeadEmployeeController())->assets();
    } elseif ($sub === 'employee/assets/tickets') {
        require_once __DIR__ . '/../app/Controllers/head/HeadEmployeeController.php';
        (new HeadEmployeeController())->assetTickets();
    } else {
        http_response_code(404);
        echo "Employee page not found.";
    }
    exit;
}
// FALLBACK
http_response_code(404);
echo "404 - Page not found.";
