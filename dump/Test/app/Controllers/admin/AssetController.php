<?php
    require_once __DIR__ . '/../AuthController.php';
    require_once __DIR__ . '/../../Models/admin/Asset.php';
    require_once __DIR__ . '/../../Models/admin/Logger.php';
    require_once __DIR__ . '/../../Helpers/Session.php';

class AssetController extends AuthController {
    // Asset Management Page
    public function asset() {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Auth check – only ADMIN allowed
        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            return;
        }

        $assetModel = new Asset();
        $assets = $assetModel->fetchAllAssets();  


        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $csrf_token = $_SESSION['csrf_token'];

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];
                $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        require_once __DIR__ . '/../../Views/admin/asset/asset.php';
    }
    //Adding branch Here
    public function branch(){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            exit;
        }

        // If you want to show the add-branch form on GET:
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            }
            $csrf_token = $_SESSION['csrf_token'];
            $ctx = $this->getLoggedUserContext();
            $base = $ctx['base'];
            $loggedFirstname = $ctx['loggedFirstname'];
            $loggedPosition  = $ctx['loggedPosition'];
                    $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
            require_once __DIR__ . '/../../Views/admin/asset/add_branch.php';
            return;
        }

        // Only allow POST from here
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $assetModel = new Asset();

        $branchName    = trim($_POST['branchName'] ?? '');
        $branchCode    = trim($_POST['branchCode'] ?? '');
        $branchAddress = trim($_POST['branchAddress'] ?? '');
        $createdBy     = $_SESSION['account_id'] ?? '';

        try {
            $id = $assetModel->addBranch($branchName, $branchCode, $branchAddress, $createdBy);

            if ($id) {
                $logger = new Logger();
                $logger->log('Add Branch', 'Branch Management',$branchName,($_SESSION['username'] ?? 'Unknown User'));
                $_SESSION['flash_success'] = 'New branch added successfully.';
                $this->redirect('/admin/assets');
                exit;
            } else {
                throw new \Exception('Failed to insert branch.');
            }
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error adding branch: ' . $e->getMessage();
            $this->redirect('/admin/assets');
            exit;
        }
    }
    //adding Category Asset Here
    public function category(){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            exit;
        }

        // If you want to show the add-branch form on GET:
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            }
            $csrf_token = $_SESSION['csrf_token'];
            $ctx = $this->getLoggedUserContext();
            $base = $ctx['base'];
            $loggedFirstname = $ctx['loggedFirstname'];
            $loggedPosition  = $ctx['loggedPosition'];
                    $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
            require_once __DIR__ . '/../../Views/admin/asset/add_category.php';
            return;
        }

        // Only allow POST from here
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $assetModel = new Asset();
        $categoryName = trim($_POST['categoryName'] ?? '');
        $ic_code = trim($_POST['ic_code'] ?? '');
        $createdBy     = $_SESSION['account_id'] ?? '';

        try{
            $id = $assetModel->addCategory($categoryName, $ic_code, $createdBy);

            if ($id) {
                $logger = new Logger();
                $logger->log('Add Category', 'Category Management',$categoryName,($_SESSION['username'] ?? 'Unknown User'));
                $_SESSION['flash_success'] = 'New category added successfully.';
                $this->redirect('/admin/assets');
                exit;
            } else {
                throw new \Exception('Failed to insert category.');
            }
        }
        catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error adding category: ' . $e->getMessage();
            $this->redirect('/admin/assets');
            exit;
        }
    }

    //adding Group Asset Here 
    public function group(){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            exit;
        }

        $assetModel = new Asset();

        // Always load categories for the form (GET and also in case of re-render on error)
        $categories = $assetModel->fetchCategories();

        // GET → show form
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            }
            $csrf_token = $_SESSION['csrf_token'];
            $ctx = $this->getLoggedUserContext();
            $base = $ctx['base'];
            $loggedFirstname = $ctx['loggedFirstname'];
            $loggedPosition  = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
            // make $categories available to the view
            require_once __DIR__ . '/../../Views/admin/asset/add_group.php';
            return;
        }

        // POST → handle insert
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $category_id = (int) trim($_POST['category_id'] ?? 0);
        $ic_code     = trim($_POST['ic_code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $groupName   = trim($_POST['groupName'] ?? '');
        $createdBy   = $_SESSION['account_id'] ?? '';

        try {
            // NOTE: model signature below expects (groupName, description, categoryId, ic_code, createdBy)
            $id = $assetModel->addGroup($groupName, $description, $category_id, $ic_code, $createdBy);

            if ($id) {
                $logger = new Logger();
                $logger->log('Add Group', 'Group Management', $groupName, ($_SESSION['username'] ?? 'Unknown User'));
                $_SESSION['flash_success'] = 'New group added successfully.';
                $this->redirect('/admin/assets');
                exit;
            }

            throw new \Exception('Failed to insert group.');

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error adding group: ' . $e->getMessage();
            // ensure categories still available if you re-render the form
            require_once __DIR__ . '/../../Views/admin/asset/add_group.php';
            exit;
        }
    }
    // Update Group Asset Here
    public function updateGroup(){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            exit;
        }

        $assetModel = new Asset();


        // GET -> show form
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // require a group_id query param
            $groupId = isset($_GET['group_id']) ? (int) $_GET['group_id'] : 0;
            if ($groupId <= 0) {
                $_SESSION['flash_error'] = 'Invalid group id.';
                $this->redirect('/admin/assets');
                return;
            }

            $group = $assetModel->fetchGroupById($groupId);
            if (!$group) {
                $_SESSION['flash_error'] = 'Group not found.';
                $this->redirect('/admin/assets');
                return;
            }

            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            }
            $csrf_token = $_SESSION['csrf_token'];

            $ctx = $this->getLoggedUserContext();
            $base = $ctx['base'];
            $loggedFirstname = $ctx['loggedFirstname'];
            $loggedPosition  = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
            // make $group and $categories available to the view
            $assets = $group;       // keeps view variable names consistent with your legacy templates
            $category = [
                'category_id'  => $group['category_id'] ?? null,
                'categoryName' => $group['categoryName'] ?? '',
                'ic_code'      => $group['ic_code'] ?? '',
            ];

            require_once __DIR__ . '/../../Views/admin/asset/update_group.php';
            return;
        }

        // POST -> perform update
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        // Basic CSRF check (optional but recommended)
        $posted_token = $_POST['csrf_token'] ?? '';
        if (empty($posted_token) || $posted_token !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid CSRF token.';
            $this->redirect('/admin/assets');
            return;
        }

        // sanitize/validate inputs
        $groupId     = isset($_POST['group_id']) ? (int) $_POST['group_id'] : 0;
        $groupName   = trim($_POST['groupName'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($groupId <= 0 || $groupName === '') {
            $_SESSION['flash_error'] = 'Invalid input.';
            $this->redirect('/admin/assets');
            return;
        }

        try {
            $ok = $assetModel->updateGroup($groupId, $groupName, $description);

            if ($ok) {
                $logger = new Logger();
                $logger->log(
                    'Update Group',
                    'Group Management',
                    $groupName,
                    $_SESSION['username'] ?? 'Unknown User'
                );

                $_SESSION['flash_success'] = 'Group updated successfully.';
                $this->redirect('/admin/assets');
                return;
            }

            throw new \Exception('No rows updated.');
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error updating group: ' . $e->getMessage();
            // If you want to re-render the form with the old data:
            $group = $assetModel->fetchGroupById($groupId);
            $assets = $group;
            $category = [
                'category_id'  => $group['category_id'] ?? null,
                'categoryName' => $group['categoryName'] ?? '',
                'ic_code'      => $group['ic_code'] ?? '',
            ];
                    $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
            require_once __DIR__ . '/../../Views/admin/asset/update_group.php';
            return;
        }
    }

    // View Asset Items Here
    public function item()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            exit;
        }

        $assetModel = new Asset();

        // read group_id from querystring
        $group_id = isset($_GET['group_id']) ? (int) $_GET['group_id'] : 0;
        if ($group_id <= 0) {
            $_SESSION['flash_error'] = 'Invalid group id.';
            $this->redirect('/admin/assets');
            return;
        }

        // GET -> show items for the group
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $items = $assetModel->fetchItemsByGroupId($group_id);

            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            }
            $csrf_token = $_SESSION['csrf_token'];

            $ctx = $this->getLoggedUserContext();
            $base = $ctx['base'];
            $loggedFirstname = $ctx['loggedFirstname'];
            $loggedPosition  = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
            // make variables available to the view (items, group_id, etc.)
            require_once __DIR__ . '/../../Views/admin/asset/item.php';
            return;
        }

        // POST -> (optional) handle creating an item; for now return 405
        http_response_code(405);
        echo 'Method Not Allowed';
        return;
    }
    // Add Asset Item Here
    public function addItem()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            exit;
        }

        $assetModel = new Asset();

        // read group_id from querystring and validate immediately
        $group_id = isset($_GET['group_id']) ? (int) $_GET['group_id'] : 0;
        if ($group_id <= 0) {
            $_SESSION['flash_error'] = 'Invalid group id.';
            $this->redirect('/admin/assets');
            return;
        }

        // GET -> show Add Item form (and items list if you want)
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $group = $assetModel->fetchGroupById($group_id);
            if (!$group) {
                $_SESSION['flash_error'] = 'Group not found.';
                $this->redirect('/admin/assets');
                return;
            }

            $items = $assetModel->fetchItemsByGroupId($group_id);

            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            }
            $csrf_token = $_SESSION['csrf_token'];

            $ctx = $this->getLoggedUserContext();
            $base = $ctx['base'];
            $loggedFirstname = $ctx['loggedFirstname'];
            $loggedPosition  = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
            // legacy view variable names
            $group_name = $group['groupName'] ?? '';
            require_once __DIR__ . '/../../Views/admin/asset/add_item.php';
            return;
        }

        // POST -> create new item
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF check
            $posted_token = $_POST['csrf_token'] ?? '';
            if (empty($posted_token) || $posted_token !== ($_SESSION['csrf_token'] ?? '')) {
                $_SESSION['flash_error'] = 'Invalid CSRF token.';
                $this->redirect('/admin/assets/item?group_id=' . $group_id);
                return;
            }

            // validate inputs
            $serialNumber   = trim($_POST['serialNumber'] ?? '');
            $itemInfo       = trim($_POST['itemInfo'] ?? '');
            $year_purchased = trim($_POST['year_purchased'] ?? '');
            $createdBy      = $_SESSION['username'] ?? ($_SESSION['account_id'] ?? 'system');

            if ($serialNumber === '' || $itemInfo === '' || $year_purchased === '') {
                $_SESSION['flash_error'] = 'Please fill all required fields.';
                $this->redirect('/admin/assets/item?group_id=' . $group_id);
                return;
            }

            try {
                $newId = $assetModel->addItem($group_id, $serialNumber, $itemInfo, $year_purchased, $createdBy);

                if ($newId) {
                    $logger = new Logger();
                    $logger->log('Add Asset', 'Asset Inventory', "Asset ID: {$newId}", $_SESSION['username'] ?? 'Unknown User');

                    $_SESSION['flash_success'] = 'New asset added successfully.';
                    $this->redirect('/admin/assets/item?group_id=' . $group_id);
                    return;
                }

                throw new \Exception('Failed to add item.');
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = 'Error adding asset: ' . $e->getMessage();
                $this->redirect('/admin/assets/item?group_id=' . $group_id);
                return;
            }
        }

        // fallback
        http_response_code(405);
        echo 'Method Not Allowed';
    }
    // Edit Asset Item Here
    public function editItem()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login'); return;
        }

        $inventoryId = isset($_GET['inventory_id']) ? (int) $_GET['inventory_id'] : 0;
        if ($inventoryId <= 0) {
            $_SESSION['flash_error'] = 'Invalid inventory id.';
            $this->redirect('/admin/assets'); return;
        }

        $assetModel = new Asset();
        // fetch the inventory row — create a new method or a quick inline query in model if not present
        $inventory = $assetModel->fetchInventoryById($inventoryId); // implement this in the model if needed

        if (!$inventory) {
            $_SESSION['flash_error'] = 'Item not found.';
            $this->redirect('/admin/assets'); return;
        }

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition = $ctx['loggedPosition'];
                $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        $csrf_token = $_SESSION['csrf_token'];

        // pass $inventory to view
        require_once __DIR__ . '/../../Views/admin/asset/update_item.php';
    }
    // Store Updated Asset Item Here
    public function updateItem()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo 'Method Not Allowed'; return; }
        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login'); return;
        }

        // CSRF
        $posted_token = $_POST['csrf_token'] ?? '';
        if (empty($posted_token) || $posted_token !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid CSRF token.'; $this->redirect('/admin/assets'); return;
        }

        $inventoryID = isset($_POST['inventory']) ? (int) $_POST['inventory'] : 0;
        $itemInfo = trim($_POST['itemInfo'] ?? '');
        $serialNumber = trim($_POST['serialNumber'] ?? '');
        $yearPurchased = trim($_POST['year_purchased'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $reason = trim($_POST['transferDetails'] ?? '');

        if ($inventoryID <= 0 || $itemInfo === '' || $serialNumber === '') {
            $_SESSION['flash_error'] = 'Please complete required fields.';
            $this->redirect('/admin/assets/item?group_id=' . (int)($_POST['group_id'] ?? 0)); return;
        }

        $assetModel = new Asset();
        $ok = $assetModel->updateItem($inventoryID, $itemInfo, $serialNumber, $yearPurchased, $status, $reason, $_SESSION['account_id'] ?? null);

        if ($ok) {
            require_once __DIR__ . '/../../Models/NotificationModel.php';

            $targets = $assetModel->getAssetNotificationTargets($inventoryID);
            $notificationModel = new NotificationModel();
            $base = $this->getLoggedUserContext()['base'];

            // 👔 Notify department head only
            if (!empty($targets['head_account_id'])) {
                $notificationModel->create(
                    (int)$targets['head_account_id'],
                    "Asset {$targets['assetNumber']} details were updated.",
                    'fa-edit',
                    'warning',
                    $base . '/head/dashboard',
                    $inventoryID
                );
            }

            $logger = new Logger();
            $logger->log('Update Item', 'Item Asset', "Inventory {$inventoryID}", $_SESSION['username'] ?? 'Unknown');
            $_SESSION['flash_success'] = 'Item updated successfully.';
        } else {
            $_SESSION['flash_error'] = 'Error updating item.';
        }

        // redirect back to item list for the group
        $group_id = (int) ($_POST['group_id'] ?? 0);
        $this->redirect('/admin/assets/item?group_id=' . $group_id);
    }

    // Transfer Asset Item Here
    public function transferItem()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            return;
        }

        $assetModel = new Asset();

        // GET → show transfer form
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $inventoryId = isset($_GET['inventory_id']) ? (int) $_GET['inventory_id'] : 0;
            if ($inventoryId <= 0) {
                $_SESSION['flash_error'] = 'Invalid inventory id.';
                $this->redirect('/admin/assets');
                return;
            }

            $inventory = $assetModel->fetchInventoryById($inventoryId);
            if (!$inventory) {
                $_SESSION['flash_error'] = 'Item not found.';
                $this->redirect('/admin/assets');
                return;
            }
            $itemInfo = $inventory['itemInfo'] ?? '';
            $assetNumber = $inventory['assetNumber'] ?? '';

            if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            $csrf_token = $_SESSION['csrf_token'];

            $ctx = $this->getLoggedUserContext();
            $base = $ctx['base'];
            $loggedFirstname = $ctx['loggedFirstname'];
            $loggedPosition  = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
            // variables visible in view: $inventory, $csrf_token, $base, $loggedFirstname, $loggedPosition
            require_once __DIR__ . '/../../Views/admin/asset/transfer.php';
            return;
        }

        // POST → perform transfer
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF
            $posted_token = $_POST['csrf_token'] ?? '';
            if (empty($posted_token) || $posted_token !== ($_SESSION['csrf_token'] ?? '')) {
                $_SESSION['flash_error'] = 'Invalid CSRF token.';
                $this->redirect('/admin/assets');
                return;
            }

            $inventoryId = isset($_POST['item_id']) ? (int) $_POST['item_id'] : 0;
            $employeeId   = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
            $transferDetails = trim($_POST['transferDetails'] ?? '');
            $performedBy = $_SESSION['account_id'] ?? ($_SESSION['username'] ?? 'SYSTEM');

            if ($inventoryId <= 0 || $employeeId <= 0 || $transferDetails === '') {
                $_SESSION['flash_error'] = 'Please complete required fields.';
                $this->redirect('/admin/assets/item?group_id=' . (int)($_POST['group_id'] ?? 0));
                return;
            }

            // Model will handle transaction and return new asset number on success
            $result = $assetModel->transferAssetToEmployee($inventoryId, $employeeId, $transferDetails, $performedBy);

            if ($result['ok']) {
                require_once __DIR__ . '/../../Models/NotificationModel.php';

                $targets = $assetModel->getAssetNotificationTargets($inventoryId);
                $notificationModel = new NotificationModel();
                $base = $this->getLoggedUserContext()['base'];

                // 👤 Notify employee who received asset
                if (!empty($targets['employee_account_id'])) {
                    $notificationModel->create(
                        (int)$targets['employee_account_id'],
                        "A new asset ({$targets['assetNumber']}) has been assigned to you.",
                        'fa-box',
                        'info',
                        $base . '/employee/assets',
                        $inventoryId
                    );
                }

                // 👔 Notify department head
                if (!empty($targets['head_account_id'])) {
                    $notificationModel->create(
                        (int)$targets['head_account_id'],
                        "Asset {$targets['assetNumber']} has been transferred to your department.",
                        'fa-exchange-alt',
                        'primary',
                        $base . '/head/dashboard',
                        $inventoryId
                    );
                }

                $logger = new Logger();
                $logger->log('Transfer Asset', 'Asset Inventory', "$employeeId", $_SESSION['username'] ?? 'Unknown');
                $_SESSION['flash_success'] = 'Asset successfully transferred. New Asset Number: ' . $result['newAssetNumber'];
            } else {
                $_SESSION['flash_error'] = 'Transfer failed: ' . $result['message'];
            }

            $group_id = (int)($_POST['group_id'] ?? 0);
            $this->redirect('/admin/assets/item?group_id=' . $group_id);
            return;
        }

        http_response_code(405);
        echo 'Method Not Allowed';
    }

    // Search Employee for Transfer Here
    public function searchEmployee()
    {
        // Always return JSON and avoid redirects for AJAX
        header('Content-Type: application/json');

        // Ensure session so authentication check works
        if (session_status() === PHP_SESSION_NONE) session_start();

        try {
            // very early auth check — but return JSON instead of redirect
            if (empty($_SESSION['account_id'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Not authenticated']);
                return;
            }

            $q = trim($_GET['q'] ?? '');
            if ($q === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Empty query']);
                return;
            }

            $assetModel = new Asset();

            // use model helper that searches by id or name
            $row = $assetModel->findEmployeeByQuery($q);

            if ($row) {
                echo json_encode([
                    'success' => true,
                    'employee_id' => (int)$row['employee_id'],
                    'full_name' => $row['fullname'] ?? ($row['full_name'] ?? ''),
                    'branchName' => $row['branchName'] ?? '',
                    'branchCode' => $row['branchCode'] ?? ''
                ]);
                return;
            }

            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Employee not found']);
            return;

        } catch (\Throwable $e) {
            // Log to file for debugging (server-side)
            file_put_contents(__DIR__ . '/../../../public/debug.log',
                date('c') . " searchEmployee EXCEPTION: " . $e->getMessage() . PHP_EOL, FILE_APPEND);

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
            return;
        }
    }


    // View Transfer History Here
    public function transferHistory()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            return;
        }

        $assetModel = new Asset();

        // require inventory_id
        $inventoryId = isset($_GET['inventory_id']) ? (int) $_GET['inventory_id'] : 0;
        if ($inventoryId <= 0) {
            $_SESSION['flash_error'] = 'Invalid inventory id.';
            $this->redirect('/admin/assets');
            return;
        }

        $inventory = $assetModel->fetchInventoryById($inventoryId);
        if (!$inventory) {
            $_SESSION['flash_error'] = 'Item not found.';
            $this->redirect('/admin/assets');
            return;
        }

        // assignments (transfer history)
        $assignments = $assetModel->fetchAssignmentsByInventoryId($inventoryId);

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $csrf_token = $_SESSION['csrf_token'];

        $ctx = $this->getLoggedUserContext();
        $base = $ctx['base'];
        $loggedFirstname = $ctx['loggedFirstname'];
        $loggedPosition  = $ctx['loggedPosition'];
        $notificationData = $this->loadNotifications();

        $count = $notificationData['count'];
        $notifications = $notificationData['notifications'];
        // expose $inventory and $assignments to the view
        require_once __DIR__ . '/../../Views/admin/asset/transfer_history.php';
    }

}