<?php
    require_once __DIR__ . '/../AuthController.php';
    require_once __DIR__ . '/../../Models/admin/Asset.php';
    require_once __DIR__ . '/../../Models/admin/Logger.php';
    require_once __DIR__ . '/../../Helpers/Session.php';
    require_once __DIR__ . '/../../Helpers/ActivityLogger.php';

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
        $defectiveCount = $assetModel->countDefectiveItems();

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

        $assetModel = new Asset();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            }
            $csrf_token = $_SESSION['csrf_token'];
            $branches = $assetModel->fetchBranches();
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

        $branchName    = trim($_POST['branchName'] ?? '');
        $branchCode    = trim($_POST['branchCode'] ?? '');
        $branchAddress = trim($_POST['branchAddress'] ?? '');
        $createdBy     = $_SESSION['account_id'] ?? '';

        try {
            $id = $assetModel->addBranch($branchName, $branchCode, $branchAddress, $createdBy);

            if ($id) {
                // Log branch creation via ActivityLogger
                ActivityLogger::create('Admin - Assets', (string)$id,
                    "New branch added: {$branchName} ({$branchCode})",
                    $_SESSION['username'] ?? 'Unknown', [
                        'branch_name' => $branchName,
                        'branch_code' => $branchCode,
                        'branch_address' => $branchAddress
                    ]);
                $_SESSION['flash_success'] = 'New branch added successfully.';
                $this->redirect('/admin/assets/branch/add');
                exit;
            } else {
                throw new \Exception('Failed to insert branch.');
            }
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error adding branch: ' . $e->getMessage();
            $this->redirect('/admin/assets/branch/add');
            exit;
        }
    }

    public function updateBranch()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            exit;
        }

        $assetModel = new Asset();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $branchId = isset($_GET['branch_id']) ? (int) $_GET['branch_id'] : 0;
            if ($branchId <= 0) {
                $_SESSION['flash_error'] = 'Invalid branch id.';
                $this->redirect('/admin/assets/branch/add');
                return;
            }

            $branch = $assetModel->fetchBranchById($branchId);
            if (!$branch) {
                $_SESSION['flash_error'] = 'Branch not found.';
                $this->redirect('/admin/assets/branch/add');
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
            require_once __DIR__ . '/../../Views/admin/asset/update_branch.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $posted_token = $_POST['csrf_token'] ?? '';
        if (empty($posted_token) || $posted_token !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid CSRF token.';
            $this->redirect('/admin/assets/branch/add');
            return;
        }

        $branchId      = isset($_POST['branch_id']) ? (int) $_POST['branch_id'] : 0;
        $branchName    = trim($_POST['branchName'] ?? '');
        $branchCode    = trim($_POST['branchCode'] ?? '');
        $branchAddress = trim($_POST['branchAddress'] ?? '');

        if ($branchId <= 0 || $branchName === '' || $branchCode === '' || $branchAddress === '') {
            $_SESSION['flash_error'] = 'All branch fields are required.';
            $this->redirect('/admin/assets/branch/add');
            return;
        }

        try {
            $existing = $assetModel->fetchBranchById($branchId);
            if (!$existing) {
                $_SESSION['flash_error'] = 'Branch not found.';
                $this->redirect('/admin/assets/branch/add');
                return;
            }

            $ok = $assetModel->updateBranch($branchId, $branchName, $branchCode, $branchAddress);
            if ($ok) {
                ActivityLogger::update('Admin - Assets', (string) $branchId,
                    "Branch updated: {$branchName} ({$branchCode})",
                    $_SESSION['username'] ?? 'Unknown', [
                        'branch_id' => $branchId,
                        'branch_name' => $branchName,
                        'branch_code' => $branchCode,
                        'branch_address' => $branchAddress,
                    ]);
                $_SESSION['flash_success'] = 'Branch updated successfully.';
                $this->redirect('/admin/assets/branch/add');
                return;
            }

            throw new \Exception('No rows updated.');
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error updating branch: ' . $e->getMessage();
            $this->redirect('/admin/assets/branch/update?branch_id=' . $branchId);
            return;
        }
    }

    public function deleteBranch()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $_SESSION['flash_error'] = 'Unauthorized access.';
            $this->redirect('/login');
            return;
        }

        $branchId = isset($_GET['branch_id']) ? (int) $_GET['branch_id'] : 0;
        if ($branchId <= 0) {
            $_SESSION['flash_error'] = 'Invalid branch ID.';
            $this->redirect('/admin/assets/branch/add');
            return;
        }

        $assetModel = new Asset();
        $branch = $assetModel->fetchBranchById($branchId);
        if (!$branch) {
            $_SESSION['flash_error'] = 'Branch not found.';
            $this->redirect('/admin/assets/branch/add');
            return;
        }

        if ($assetModel->isBranchInUse($branchId)) {
            $_SESSION['flash_error'] = 'Cannot delete this branch because it is assigned to employees or assets.';
            $this->redirect('/admin/assets/branch/add');
            return;
        }

        try {
            $ok = $assetModel->deleteBranch($branchId);
            if ($ok) {
                ActivityLogger::delete('Admin - Assets', (string) $branchId,
                    "Branch deleted: {$branch['branchName']} ({$branch['branchCode']})",
                    $_SESSION['username'] ?? 'Unknown', [
                        'branch_id' => $branchId,
                        'branch_name' => $branch['branchName'] ?? '',
                        'branch_code' => $branch['branchCode'] ?? '',
                    ]);
                $_SESSION['flash_success'] = 'Branch deleted successfully.';
                $this->redirect('/admin/assets/branch/add');
                return;
            }

            throw new \Exception('Failed to delete branch.');
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error deleting branch: ' . $e->getMessage();
            $this->redirect('/admin/assets/branch/add');
            return;
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
        $assetModel = new Asset();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            }
            $csrf_token = $_SESSION['csrf_token'];
            $categories = $assetModel->fetchCategories();
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
                // Log category creation via ActivityLogger
                ActivityLogger::create('Admin - Assets', (string)$id,
                    "New asset category added: {$categoryName}",
                    $_SESSION['username'] ?? 'Unknown', [
                        'category_name' => $categoryName,
                        'ic_code' => $ic_code
                    ]);
                $_SESSION['flash_success'] = 'New category added successfully.';
                $this->redirect('/admin/assets/category/add');
                exit;
            } else {
                throw new \Exception('Failed to insert category.');
            }
        }
        catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error adding category: ' . $e->getMessage();
            $this->redirect('/admin/assets/category/add');
            exit;
        }
    }

    public function deleteCategory()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $_SESSION['flash_error'] = 'Unauthorized access.';
            $this->redirect('/login');
            return;
        }

        $categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;
        if ($categoryId <= 0) {
            $_SESSION['flash_error'] = 'Invalid category ID.';
            $this->redirect('/admin/assets/category/add');
            return;
        }

        $assetModel = new Asset();
        $category = $assetModel->fetchCategoryById($categoryId);
        if (!$category) {
            $_SESSION['flash_error'] = 'Category not found.';
            $this->redirect('/admin/assets/category/add');
            return;
        }

        if ($assetModel->isCategoryInUse($categoryId)) {
            $_SESSION['flash_error'] = 'Cannot delete this category because it is assigned to one or more asset groups.';
            $this->redirect('/admin/assets/category/add');
            return;
        }

        try {
            $ok = $assetModel->deleteCategory($categoryId);
            if ($ok) {
                ActivityLogger::delete('Admin - Assets', (string) $categoryId,
                    "Category deleted: {$category['categoryName']} ({$category['ic_code']})",
                    $_SESSION['username'] ?? 'Unknown', [
                        'category_id' => $categoryId,
                        'category_name' => $category['categoryName'] ?? '',
                        'ic_code' => $category['ic_code'] ?? '',
                    ]);
                $_SESSION['flash_success'] = 'Category deleted successfully.';
                $this->redirect('/admin/assets/category/add');
                return;
            }

            throw new \Exception('Failed to delete category.');
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error deleting category: ' . $e->getMessage();
            $this->redirect('/admin/assets/category/add');
            return;
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
        $groups = $assetModel->fetchAllAssets();
        $totalGroups = count($groups);

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
                // Log group creation via ActivityLogger
                ActivityLogger::create('Admin - Assets', (string)$id,
                    "New asset group added: {$groupName}",
                    $_SESSION['username'] ?? 'Unknown', [
                        'group_name' => $groupName,
                        'description' => $description,
                        'category_id' => $category_id,
                        'ic_code' => $ic_code
                    ]);
                $_SESSION['flash_success'] = 'New group added successfully.';
                $this->redirect('/admin/assets/group/add');
                exit;
            }

            throw new \Exception('Failed to insert group.');

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error adding group: ' . $e->getMessage();
            $groups = $assetModel->fetchAllAssets();
            $totalGroups = count($groups);
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
                // Log group update via ActivityLogger
                ActivityLogger::update('Admin - Assets', (string)$groupId,
                    "Asset group updated: {$groupName}",
                    $_SESSION['username'] ?? 'Unknown', [
                        'group_id' => $groupId,
                        'group_name' => $groupName,
                        'description' => $description
                    ]);

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

    public function defective()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            exit;
        }

        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
        $month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');

        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }
        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }

        $assetModel = new Asset();
        $items = $assetModel->fetchDefectiveItemsByMonth($year, $month);
        $totalItems = count($items);

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
        $selectedYear = $year;
        $selectedMonth = $month;
        $monthLabel = date('F Y', mktime(0, 0, 0, $month, 1, $year));

        require_once __DIR__ . '/../../Views/admin/asset/defective.php';
    }

    public function defectiveExport()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            http_response_code(403);
            echo 'Unauthorized';
            exit;
        }

        require_once __DIR__ . '/../../Services/ExcelExportService.php';

        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
        $month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');

        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
            http_response_code(400);
            echo 'Invalid month or year.';
            exit;
        }

        $assetModel = new Asset();
        $items = $assetModel->fetchDefectiveItemsByMonth($year, $month);

        $headers = [
            'Group',
            'Category',
            'Asset #',
            'Serial',
            'Item Info',
            'Branch',
            'Reason',
            'Marked Defective',
        ];

        $rows = [];
        foreach ($items as $item) {
            $rows[] = [
                $item['groupName'] ?? '',
                $item['categoryName'] ?? '',
                $item['assetNumber'] ?? '',
                $item['serialNumber'] ?? '',
                $item['itemInfo'] ?? '',
                $item['branchName'] ?? '',
                $item['transferDetails'] ?? '',
                $item['markedDefectiveAt'] ?? '',
            ];
        }

        $filename = sprintf('defective_items_%04d_%02d.xls', $year, $month);

        try {
            (new ExcelExportService())->download($headers, $rows, $filename);
        } catch (Throwable $e) {
            error_log('Defective items export failed: ' . $e->getMessage());
            http_response_code(500);
            echo 'Failed to generate Excel file.';
            exit;
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

        // GET -> show Add Item form
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
            $groups = $assetModel->fetchAllAssets();
            $totalGroups = count($groups);
            $totalItems = 0;
            foreach ($groups as $groupRow) {
                $totalItems += (int) ($groupRow['totalItems'] ?? 0);
            }

            // Load the add item view
            require_once __DIR__ . '/../../Views/admin/asset/add_item.php';
            return;
        }

        // POST -> create new item
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF check
            $posted_token = $_POST['csrf_token'] ?? '';
            if (empty($posted_token) || $posted_token !== ($_SESSION['csrf_token'] ?? '')) {
                $_SESSION['flash_error'] = 'Invalid CSRF token.';
                $this->redirect('/admin/assets/add');
                return;
            }

            // validate inputs
            $serialNumber   = trim($_POST['serialNumber'] ?? '');
            $itemInfo       = trim($_POST['itemInfo'] ?? '');
            $year_purchased = trim($_POST['year_purchased'] ?? '');
            $group_id       = isset($_POST['group_id']) ? (int) $_POST['group_id'] : 0;
            $createdBy      = $_SESSION['username'] ?? ($_SESSION['account_id'] ?? 'system');

            if ($serialNumber === '' || $itemInfo === '' || $year_purchased === '' || $group_id <= 0) {
                $_SESSION['flash_error'] = 'Please fill all required fields including selecting a group.';
                $this->redirect('/admin/assets/add');
                return;
            }

            try {
                $newId = $assetModel->addItem($group_id, $serialNumber, $itemInfo, $year_purchased, $createdBy);

                if ($newId) {
                    $logger = new Logger();
                    $logger->log('Add Asset', 'Asset Inventory', "Asset ID: {$newId}", $_SESSION['username'] ?? 'Unknown User');

                    $_SESSION['flash_success'] = 'New asset added successfully.';
                    $this->redirect('/admin/assets');
                    return;
                }

                throw new \Exception('Failed to add item.');
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = 'Error adding asset: ' . $e->getMessage();
                $this->redirect('/admin/assets/add');
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
        if (strtoupper($status) === 'DEFECTIVE') {
            $current = $assetModel->fetchInventoryById($inventoryID);
            $currentStatus = strtoupper(trim((string) ($current['status'] ?? '')));
            if (!in_array($currentStatus, ['RETURNED', 'UNASSIGNED'], true)) {
                $_SESSION['flash_error'] = 'Asset must be returned before marking as defective.';
                $this->redirect('/admin/assets/item?group_id=' . (int) ($_POST['group_id'] ?? 0));
                return;
            }
        }

        $ok = $assetModel->updateItem($inventoryID, $itemInfo, $serialNumber, $yearPurchased, $status, $reason, $_SESSION['account_id'] ?? null);

        if ($ok) {
            require_once __DIR__ . '/../../Models/NotificationModel.php';

            $targets = $assetModel->getAssetNotificationTargets($inventoryID);
            $notificationModel = new NotificationModel();

            // 👔 Notify department head only
            if (!empty($targets['head_account_id'])) {
                $notificationModel->create(
                    (int)$targets['head_account_id'],
                    "Asset {$targets['assetNumber']} details were updated.",
                    'fa-edit',
                    'warning',
                    '/head/dashboard',
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

    public function markDefective()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }
        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            return;
        }

        $posted_token = $_POST['csrf_token'] ?? '';
        if (empty($posted_token) || $posted_token !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid CSRF token.';
            $this->redirect('/admin/assets');
            return;
        }

        $inventoryID = isset($_POST['inventory_id']) ? (int) $_POST['inventory_id'] : 0;
        $group_id = (int) ($_POST['group_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($inventoryID <= 0) {
            $_SESSION['flash_error'] = 'Invalid inventory id.';
            $this->redirect('/admin/assets/item?group_id=' . $group_id);
            return;
        }
        if ($reason === '') {
            $_SESSION['flash_error'] = 'Please provide a reason for marking this item defective.';
            $this->redirect('/admin/assets/item?group_id=' . $group_id);
            return;
        }

        $assetModel = new Asset();
        $ok = $assetModel->markItemDefective($inventoryID, $reason, $_SESSION['account_id'] ?? null);

        if ($ok) {
            $logger = new Logger();
            $logger->log('Mark Defective', 'Item Asset', "Inventory {$inventoryID}", $_SESSION['username'] ?? 'Unknown');
            $_SESSION['flash_success'] = 'Item marked as defective successfully.';
        } else {
            $_SESSION['flash_error'] = 'Could not mark item as defective. Return the asset first, then mark it defective from inventory.';
        }

        $this->redirect('/admin/assets/item?group_id=' . $group_id);
    }

    public function returnAsset()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }
        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $this->redirect('/login');
            return;
        }

        $posted_token = $_POST['csrf_token'] ?? '';
        if (empty($posted_token) || $posted_token !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid CSRF token.';
            $this->redirect('/admin/employee');
            return;
        }

        $inventoryID = isset($_POST['inventory_id']) ? (int) $_POST['inventory_id'] : 0;
        $employeeId = isset($_POST['employee_id']) ? (int) $_POST['employee_id'] : 0;
        $reason = trim($_POST['reason'] ?? '');

        if ($inventoryID <= 0 || $employeeId <= 0) {
            $_SESSION['flash_error'] = 'Invalid asset or employee.';
            $this->redirect('/admin/assets/view?employee_id=' . $employeeId);
            return;
        }
        if ($reason === '') {
            $reason = '';
        }

        $assetModel = new Asset();
        $ok = $assetModel->returnAssetFromEmployee($inventoryID, $employeeId, $reason, $_SESSION['account_id'] ?? null, 'ADMIN');

        if ($ok) {
            $logger = new Logger();
            $logger->log('Return Asset', 'Item Asset', "Inventory {$inventoryID}", $_SESSION['username'] ?? 'Unknown');
            $_SESSION['flash_success'] = 'Asset returned successfully. Accountability record updated.';
        } else {
            $_SESSION['flash_error'] = 'Could not return asset. It may no longer be assigned to this employee.';
        }

        $this->redirect('/admin/assets/view?employee_id=' . $employeeId);
    }

    public function updateAccountabilityRemarks()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $role = strtoupper($_SESSION['usertype'] ?? '');
        if (empty($_SESSION['account_id']) || !in_array($role, ['ADMIN', 'IT'], true)) {
            $this->redirect('/login');
            return;
        }

        $posted_token = $_POST['csrf_token'] ?? '';
        if (empty($posted_token) || $posted_token !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid CSRF token.';
            $this->redirect('/admin/employee');
            return;
        }

        $assignmentId = (int) ($_POST['assignment_id'] ?? 0);
        $remarks = trim((string) ($_POST['remarks'] ?? ''));
        $returnUrl = trim((string) ($_POST['return_url'] ?? '/admin/employee'));

        if ($assignmentId <= 0 || $remarks === '') {
            $_SESSION['flash_error'] = 'Remarks are required.';
            $this->redirect($returnUrl !== '' ? $returnUrl : '/admin/employee');
            return;
        }

        $assetModel = new Asset();
        if ($assetModel->updateAccountabilityRemarks($assignmentId, $remarks, $_SESSION['account_id'] ?? null)) {
            $_SESSION['flash_success'] = 'Accountability remarks updated.';
        } else {
            $_SESSION['flash_error'] = 'Unable to update accountability remarks.';
        }

        $this->redirect($returnUrl !== '' ? $returnUrl : '/admin/employee');
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

                // 👤 Notify employee who received asset
                if (!empty($targets['employee_account_id'])) {
                    $notificationModel->create(
                        (int)$targets['employee_account_id'],
                        "A new asset ({$targets['assetNumber']}) has been assigned to you.",
                        'fa-box',
                        'info',
                        '/employee/assets',
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
                        '/head/dashboard',
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

    public function searchEmployeeSuggestions()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            if (empty($_SESSION['account_id'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Not authenticated']);
                return;
            }

            $q = trim($_GET['q'] ?? '');
            if ($q === '') {
                echo json_encode(['success' => true, 'results' => []]);
                return;
            }

            $assetModel = new Asset();
            $rows = $assetModel->searchEmployeesByQuery($q, 10);
            $results = array_map(static function (array $row): array {
                return [
                    'employee_id' => (int) ($row['employee_id'] ?? 0),
                    'full_name' => trim((string) ($row['fullname'] ?? '')),
                    'branchName' => (string) ($row['branchName'] ?? ''),
                    'branchCode' => (string) ($row['branchCode'] ?? ''),
                ];
            }, $rows);

            echo json_encode([
                'success' => true,
                'results' => $results,
            ]);
        } catch (\Throwable $e) {
            file_put_contents(__DIR__ . '/../../../public/debug.log',
                date('c') . " searchEmployeeSuggestions EXCEPTION: " . $e->getMessage() . PHP_EOL, FILE_APPEND);

            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
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
        $remarksFormAction = rtrim(BASE_URL, '/') . '/admin/assets/accountability-remarks';
        $returnUrl = rtrim(BASE_URL, '/') . '/admin/assets/transfer-history?inventory_id=' . $inventoryId;

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

    // Delete an inventory item
    public function deleteItem()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $_SESSION['flash_error'] = 'Unauthorized access.';
            $this->redirect('/login');
            return;
        }

        $inventoryId = isset($_GET['inventory_id']) ? (int) $_GET['inventory_id'] : 0;
        if ($inventoryId <= 0) {
            $_SESSION['flash_error'] = 'Invalid inventory id.';
            $this->redirect('/admin/assets');
            return;
        }

        $assetModel = new Asset();

        // fetch inventory to obtain group_id for redirect and logging
        $inventory = $assetModel->fetchInventoryById($inventoryId);
        if (!$inventory) {
            $_SESSION['flash_error'] = 'Item not found.';
            $this->redirect('/admin/assets');
            return;
        }

        try {
            $ok = $assetModel->deleteItem($inventoryId);

            if ($ok) {
                $logger = new Logger();
                $logger->log('Delete Item', 'Asset Inventory', "Inventory {$inventoryId}", $_SESSION['username'] ?? 'Unknown User');

                $_SESSION['flash_success'] = 'Asset item deleted successfully.';
                $this->redirect('/admin/assets/item?group_id=' . (int)($inventory['group_id'] ?? 0));
                return;
            }

            throw new \Exception('Failed to delete item.');
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error deleting item: ' . $e->getMessage();
            $this->redirect('/admin/assets/item?group_id=' . (int)($inventory['group_id'] ?? 0));
            return;
        }
    }

    // Delete Asset Group Here
    public function deleteGroup()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['account_id']) || strtoupper($_SESSION['usertype'] ?? '') !== 'ADMIN') {
            $_SESSION['flash_error'] = 'Unauthorized access.';
            $this->redirect('/login');
            return;
        }

        $groupId = isset($_GET['group_id']) ? (int) $_GET['group_id'] : 0;
        if ($groupId <= 0) {
            $_SESSION['flash_error'] = 'Invalid group ID.';
            $this->redirect('/admin/assets');
            return;
        }

        $assetModel = new Asset();

        // Check if group exists
        $group = $assetModel->fetchGroupById($groupId);
        if (!$group) {
            $_SESSION['flash_error'] = 'Group not found.';
            $this->redirect('/admin/assets');
            return;
        }

        try {
            $ok = $assetModel->deleteGroup($groupId);

            if ($ok) {
                $logger = new Logger();
                $logger->log(
                    'Delete Group',
                    'Group Management',
                    $group['groupName'],
                    $_SESSION['username'] ?? 'Unknown User'
                );

                $_SESSION['flash_success'] = 'Asset group and its items deleted successfully.';
                $this->redirect('/admin/assets');
                return;
            }

            throw new \Exception('Failed to delete group.');
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Error deleting group: ' . $e->getMessage();
            $this->redirect('/admin/assets');
            return;
        }
    }

}