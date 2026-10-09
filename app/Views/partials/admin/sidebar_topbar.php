<?php
require_once __DIR__ . '/../../../Helpers/SuperUser.php';
require_once __DIR__ . '/../../../Helpers/AdminAccess.php';
$base = rtrim(BASE_URL, '/');
$showSystemMenu = AdminAccess::canManageSystem();
$inRoleArea = SuperUser::isActingInRoleArea();
$adminActivePage = $inRoleArea ? '' : ($activePage ?? '');
$ticketSubPage = $inRoleArea ? '' : ($ticketSubPage ?? '');
$userSubPage = $inRoleArea ? '' : ($userSubPage ?? '');
$assetSubPage = $inRoleArea ? '' : ($assetSubPage ?? '');

$fullAccessGroups = [];
if (SuperUser::isCurrent()) {
    $fullAccessGroups = [
        'Hr' => ['HR &amp; Inventory', 'fa-user-friends', [
            ['/hr/dashboard', 'HR Dashboard', 'fa-tachometer-alt'],
            ['/hr/employees', 'Employees', 'fa-users'],
            ['/hr/uniforms', 'Inventory', 'fa-archive'],
            ['/hr/uniforms/assignments', 'Item Assignments', 'fa-user-tag'],
            ['/hr/uniforms/reports', 'Lost / Damaged Reports', 'fa-exclamation-triangle'],
            ['/hr/tickets', 'HR Tickets', 'fa-ticket-alt'],
        ]],
        'It' => ['IT', 'fa-laptop-code', [
            ['/it/dashboard', 'IT Dashboard', 'fa-tachometer-alt'],
            ['/it/tickets', 'IT Tickets', 'fa-ticket-alt'],
            ['/it/uploads', 'Technical Uploads', 'fa-file-upload'],
            ['/it/ratings', 'IT Ratings', 'fa-star'],
        ]],
        'Ops' => ['Operations', 'fa-warehouse', [
            ['/hom/dashboard', 'Operations Dashboard', 'fa-tachometer-alt'],
            ['/hom/employees', 'Operations Staff', 'fa-users'],
            ['/hom/assets', 'Operations Assets', 'fa-boxes'],
            ['/hom/aom-branches', 'AOM Branches', 'fa-map-marked-alt'],
            ['/hom/tickets', 'Operations Tickets', 'fa-ticket-alt'],
        ]],
        'Head' => ['Department Head', 'fa-user-tie', [
            ['/head/dashboard', 'Head Dashboard', 'fa-tachometer-alt'],
            ['/head/employee', 'My Department', 'fa-sitemap'],
            ['/head/tickets', 'Department Tickets', 'fa-ticket-alt'],
        ]],
    ];

    $requestPath = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '');
    if ($base !== '' && strpos($requestPath, $base) === 0) {
        $requestPath = substr($requestPath, strlen($base));
    }
    $requestPath = '/' . trim($requestPath, '/');

    // Highlight the most specific link that matches the current page.
    $activeFullAccessPath = '';
    // Prefixed names: this partial runs in the including view's scope, so a plain $items
    // here used to overwrite the view's own $items (e.g. the asset item and defective lists).
    foreach ($fullAccessGroups as [, , $faGroupItems]) {
        foreach ($faGroupItems as [$faPath]) {
            $faArea = dirname($faPath);
            $faMatches = $requestPath === $faPath
                || strpos($requestPath, $faPath . '/') === 0
                || (substr($faPath, -10) === '/dashboard' && $requestPath === $faArea);
            if ($faMatches && strlen($faPath) > strlen($activeFullAccessPath)) {
                $activeFullAccessPath = $faPath;
            }
        }
    }
}
?>
<?php require_once __DIR__ . '/../sidebar_styles.php'; ?>
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion sidebar-modern" id="accordionSidebar">

<?php require __DIR__ . '/../sidebar_user.php'; ?>

            <!-- Nav Item - Dashboard -->
            <li class="nav-item <?= ($adminActivePage === 'dashboard') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base) ?>/admin">  
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Interface
            </div>

            <!-- Nav Item - Users -->
            <li class="nav-item <?= ($adminActivePage === 'users') ? 'active' : '' ?>">
                <a class="nav-link <?= ($adminActivePage === 'users') ? '' : 'collapsed' ?>" href="#" data-toggle="collapse" data-target="#collapseUsers"
                    aria-expanded="<?= ($adminActivePage === 'users') ? 'true' : 'false' ?>" aria-controls="collapseUsers">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Users</span>
                </a>
                <div id="collapseUsers" class="collapse <?= ($adminActivePage === 'users') ? 'show' : '' ?>" aria-labelledby="headingUsers" data-parent="#accordionSidebar">
                    <div class="sidebar-submenu">
                        <a class="sidebar-submenu-item <?= in_array($userSubPage, ['accounts', 'employee'], true) ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/account">
                            <i class="fas fa-id-card"></i>
                            <span>Users &amp; Employees</span>
                        </a>
                        <a class="sidebar-submenu-item <?= ($userSubPage === 'department') ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/department">
                            <i class="fas fa-building"></i>
                            <span>Departments</span>
                        </a>
                    </div>
                </div>
            </li>
			
            <li class="nav-item <?= ($adminActivePage === 'tickets') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base) ?>/admin/tickets">
                    <i class="fas fa-fw fa-ticket-alt"></i>
                    <span>Ticket</span>
                </a>
            </li>
            <li class="nav-item <?= in_array($adminActivePage, ['assets', 'asset', 'branch', 'category']) ? 'active' : '' ?>">
                <a class="nav-link <?= in_array($adminActivePage, ['assets', 'asset', 'branch', 'category']) ? '' : 'collapsed' ?>" href="#" data-toggle="collapse" data-target="#collapseAssets"
                    aria-expanded="<?= in_array($adminActivePage, ['assets', 'asset', 'branch', 'category']) ? 'true' : 'false' ?>" aria-controls="collapseAssets">
                    <i class="fas fa-archive"></i>
                    <span>Assets Directory</span>
                </a>
                <div id="collapseAssets" class="collapse <?= in_array($adminActivePage, ['assets', 'asset', 'branch', 'category']) ? 'show' : '' ?>" aria-labelledby="headingAssets" data-parent="#accordionSidebar">
                    <div class="sidebar-submenu">
                        <a class="sidebar-submenu-item <?= ($assetSubPage === 'directory') ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/assets">
                            <i class="fas fa-th-list"></i>
                            <span>Assets Directory</span>
                        </a>
                        <a class="sidebar-submenu-item <?= ($assetSubPage === 'defective') ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/assets/defective">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Defective Items</span>
                        </a>
                        <a class="sidebar-submenu-item <?= ($assetSubPage === 'add-item') ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/assets/add">
                            <i class="fas fa-plus-circle"></i>
                            <span>Add Item</span>
                        </a>
                        <a class="sidebar-submenu-item <?= ($assetSubPage === 'add-branch') ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/assets/branch/add">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Add Branch</span>
                        </a>
                        <a class="sidebar-submenu-item <?= ($assetSubPage === 'add-category') ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/assets/category/add">
                            <i class="fas fa-tags"></i>
                            <span>Add Category</span>
                        </a>
                        <a class="sidebar-submenu-item <?= ($assetSubPage === 'add-group') ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/assets/group/add">
                            <i class="fas fa-layer-group"></i>
                            <span>Add Model / Group</span>
                        </a>
                    </div>
                </div>
            </li>
            <!-- Nav Item - My Issued Items (the admin's own uniforms / IDs from HR) -->
            <li class="nav-item <?= ($adminActivePage === 'items') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base) ?>/admin/items">
                    <i class="fas fa-fw fa-tshirt"></i>
                    <span>My Issued Items</span></a>
            </li>

            <!-- Nav Item - System (auto-assignment, announcements, IT manuals, themes); not for the General Manager -->
            <?php if ($showSystemMenu): ?>
            <?php
            $systemSubPage = $inRoleArea ? '' : ($systemSubPage ?? '');
            $systemOpen = $adminActivePage === 'system';
            ?>
            <li class="nav-item <?= $systemOpen ? 'active' : '' ?>">
                <a class="nav-link <?= $systemOpen ? '' : 'collapsed' ?>" href="#" data-toggle="collapse" data-target="#collapseSystem"
                    aria-expanded="<?= $systemOpen ? 'true' : 'false' ?>" aria-controls="collapseSystem">
                    <i class="fas fa-fw fa-cogs"></i>
                    <span>System</span>
                </a>
                <div id="collapseSystem" class="collapse <?= $systemOpen ? 'show' : '' ?>" data-parent="#accordionSidebar">
                    <div class="sidebar-submenu">
                        <a class="sidebar-submenu-item <?= $systemSubPage === 'ticket-assignment' ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/ticket-assignment">
                            <i class="fas fa-random"></i>
                            <span>Ticket Auto-Assignment</span>
                        </a>
                        <a class="sidebar-submenu-item <?= $systemSubPage === 'announcements' ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/announcements">
                            <i class="fas fa-bullhorn"></i>
                            <span>Announcements</span>
                        </a>
                        <a class="sidebar-submenu-item <?= $systemSubPage === 'manuals' ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/manuals">
                            <i class="fas fa-book"></i>
                            <span>IT Manuals</span>
                        </a>
                        <a class="sidebar-submenu-item <?= $systemSubPage === 'themes' ? 'active' : '' ?>"
                           href="<?= htmlspecialchars($base) ?>/admin/themes">
                            <i class="fas fa-palette"></i>
                            <span>Themes &amp; Events</span>
                        </a>
                    </div>
                </div>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Nav Item - Audit Trail -->
            <li class="nav-item <?= ($adminActivePage === 'audit_trail') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base) ?>/admin/audit-trail">
                    <i class="fas fa-fw fa-history"></i>
                    <span>Audit Trail</span></a>
            </li>
            <?php endif; ?>

            <?php if (!empty($fullAccessGroups)): ?>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Full Access</div>
            <?php foreach ($fullAccessGroups as $faGroupKey => [$faGroupLabel, $faGroupIcon, $faGroupLinks]):
                $faGroupOpen = in_array($activeFullAccessPath, array_column($faGroupLinks, 0), true);
            ?>
            <li class="nav-item <?= $faGroupOpen ? 'active' : '' ?>">
                <a class="nav-link <?= $faGroupOpen ? '' : 'collapsed' ?>" href="#" data-toggle="collapse" data-target="#collapseFull<?= $faGroupKey ?>"
                   aria-expanded="<?= $faGroupOpen ? 'true' : 'false' ?>" aria-controls="collapseFull<?= $faGroupKey ?>">
                    <i class="fas fa-fw <?= $faGroupIcon ?>"></i>
                    <span><?= $faGroupLabel ?></span>
                </a>
                <div id="collapseFull<?= $faGroupKey ?>" class="collapse <?= $faGroupOpen ? 'show' : '' ?>" data-parent="#accordionSidebar">
                    <div class="sidebar-submenu">
                        <?php foreach ($faGroupLinks as [$faLinkPath, $faLinkLabel, $faLinkIcon]): ?>
                            <a class="sidebar-submenu-item <?= $faLinkPath === $activeFullAccessPath ? 'active' : '' ?>"
                               href="<?= htmlspecialchars($base . $faLinkPath) ?>">
                                <i class="fas <?= $faLinkIcon ?>"></i>
                                <span><?= htmlspecialchars($faLinkLabel) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </li>
            <?php endforeach; ?>
            <?php unset($faGroupKey, $faGroupLabel, $faGroupIcon, $faGroupLinks, $faGroupOpen, $faLinkPath, $faLinkLabel, $faLinkIcon); ?>
            <?php endif; ?>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Reports
            </div>

            <!-- Nav Item - Ratings -->
            <li class="nav-item <?= ($adminActivePage === 'ratings') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base) ?>/admin/ratings">
                    <i class="fas fa-fw fa-star"></i>
                    <span>Ratings Report</span></a>
            </li>

            <!-- Nav Item - Ticket Report -->
            <li class="nav-item <?= ($adminActivePage === 'ticket_report') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= htmlspecialchars($base) ?>/admin/reports/tickets">
                    <i class="fas fa-fw fa-clipboard-list"></i>
                    <span>Ticket Report</span></a>
            </li>

            <!-- Divider -->
</ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
<?php
$shellHomeUrl = $base . '/admin';
$shellProfileUrl = $base . '/admin/profile';
require __DIR__ . '/../app_topbar.php';
?>

<?php require_once __DIR__ . '/../realtime_scripts.php'; ?>
<?php require_once __DIR__ . '/../logout_modal.php'; ?>
