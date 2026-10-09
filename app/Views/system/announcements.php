<?php
$base = rtrim(BASE_URL, '/');
$h = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$isAdmin = $role === 'ADMIN';
$editing = $editing ?? null;
$toLocal = static function (?string $value): string {
    return $value ? date('Y-m-d\TH:i', strtotime($value)) : '';
};
$now = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Announcements</title>
    <link href="<?= $h($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="<?= $h($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= $h($base) ?>/assets/css/admin-users.css" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <style>
        .ann-item { border-left:4px solid #4e73df; }
        .ann-item.important { border-left-color:#e74a3b; }
        .ann-item.inactive { opacity:.6; }
        .ann-body { white-space:pre-wrap; font-size:.88rem; color:#4b5563; }
        .ann-meta { font-size:.75rem; color:#6b7280; }
        .ann-badge { display:inline-block; padding:1px 8px; border-radius:999px; font-size:.7rem; font-weight:700; margin-right:4px; }
        .ann-badge.all { background:#eef2ff; color:#4e73df; } .ann-badge.dept { background:#ecfdf5; color:#047857; }
        .ann-badge.imp { background:#fee2e2; color:#b91c1c; } .ann-badge.sched { background:#fff7ed; color:#c2410c; }
        .ann-badge.off { background:#f3f4f6; color:#6b7280; }
        .ann-badge.wait { background:#fef3c7; color:#92400e; } .ann-badge.rej { background:#fee2e2; color:#991b1b; }
        .ann-item.pending { border-left-color:#f6c23e; background:#fffdf5; }
        html.it-dark .ann-item.pending { background:#2d2a1e; }
        html.it-dark .ann-body, html.it-dark .ann-meta { color:#cbd5e1; }
    </style>
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        if ($area === 'admin') {
            $activePage = 'system';
            $systemSubPage = 'announcements';
            require_once __DIR__ . '/../partials/admin/sidebar_topbar.php';
        } elseif ($area === 'head') {
            $activePage = 'announcements';
            require_once __DIR__ . '/../partials/head/sidebar_topbar.php';
        } else {
            $activePage = 'announcements';
            $routePrefix = 'hom';
            require_once __DIR__ . '/../partials/hom/sidebar_topbar.php';
        }
        ?>

        <div class="container-fluid admin-users-page">

            <div class="page-hero hero-accounts">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-bullhorn mr-2"></i>Announcements</h1>
                        <p>
                            <?php if ($isAdmin): ?>
                                Post a message to every user, or to one department. It pops up once for each person and stays under the megaphone in the header.
                            <?php else: ?>
                                Post a message to your department (<strong><?= $h($department ?: 'none') ?></strong>). Admin approves it first; then it pops up once for each member and stays under the megaphone in the header.
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="hero-stat mt-3 mt-lg-0">
                            <div class="stat-value"><?= count($items) ?></div>
                            <div class="stat-label">Announcements</div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($setupMissing)): ?>
                <div class="alert alert-warning"><i class="fas fa-database mr-1"></i>
                    The database update has not been run yet. Import the update SQL in phpMyAdmin first.</div>
            <?php endif; ?>

            <div class="row">
                <div class="col-xl-5">
                    <div class="card form-card shadow mb-4" id="announcement-form">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas <?= $editing ? 'fa-edit' : 'fa-plus' ?> mr-1"></i><?= $editing ? 'Edit announcement' : 'New announcement' ?>
                            </h6>
                            <?php if ($editing): ?>
                                <a href="<?= $h($base . $home) ?>" class="btn btn-sm btn-light">Cancel</a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= $h($base . $home) ?>">
                                <input type="hidden" name="csrf_token" value="<?= $h($csrf_token) ?>">
                                <input type="hidden" name="action" value="save">
                                <input type="hidden" name="announcement_id" value="<?= (int) ($editing['announcement_id'] ?? 0) ?>">
                                <div class="form-group">
                                    <label for="a_title">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="a_title" name="title" maxlength="150" required
                                           value="<?= $h($editing['title'] ?? '') ?>"
                                           placeholder="e.g. File a ticket before any IT work">
                                </div>
                                <div class="form-group">
                                    <label for="a_body">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="a_body" name="body" rows="6" maxlength="5000" required
                                              placeholder="e.g. IT will not start any repair without a ticket. Please file a ticket first so the problem is recorded and assigned."><?= $h($editing['body'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="a_dept">Send to</label>
                                    <?php if ($isAdmin): ?>
                                        <select class="form-control" id="a_dept" name="department">
                                            <option value="">Everyone (all users)</option>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?= $h($dept) ?>" <?= ($editing['department'] ?? '') === $dept ? 'selected' : '' ?>>
                                                    <?= $h($dept) ?> department only</option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <input type="text" class="form-control" id="a_dept" value="<?= $h($department) ?> department" readonly>
                                    <?php endif; ?>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-sm-6">
                                        <label for="a_start">Show from</label>
                                        <input type="datetime-local" class="form-control" id="a_start" name="starts_at"
                                               value="<?= $h($toLocal($editing['starts_at'] ?? null)) ?>">
                                        <small class="form-text text-muted">Empty = now</small>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label for="a_end">Until</label>
                                        <input type="datetime-local" class="form-control" id="a_end" name="ends_at"
                                               value="<?= $h($toLocal($editing['ends_at'] ?? null)) ?>">
                                        <small class="form-text text-muted">Empty = no end</small>
                                    </div>
                                </div>
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="a_imp" name="importance" value="important"
                                        <?= ($editing['importance'] ?? '') === 'important' ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="a_imp">Important (shown in red, listed first)</label>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane mr-1"></i><?= $editing ? 'Save changes' : 'Post announcement' ?></button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-7">
                    <div class="card data-list-card shadow mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list mr-1"></i>Posted</h6>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!$items): ?>
                                <p class="text-muted p-4 mb-0">No announcements yet.</p>
                            <?php endif; ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($items as $item):
                                    $approval = (string) ($item['approval_status'] ?? 'APPROVED');
                                    $live = $approval === 'APPROVED' && (int) $item['is_active'] === 1 && $item['starts_at'] <= $now && (empty($item['ends_at']) || $item['ends_at'] > $now);
                                    $scheduled = $approval === 'APPROVED' && (int) $item['is_active'] === 1 && $item['starts_at'] > $now;
                                ?>
                                    <div class="list-group-item ann-item <?= $item['importance'] === 'important' ? 'important' : '' ?> <?= (int) $item['is_active'] === 1 ? '' : 'inactive' ?> <?= $approval === 'PENDING' ? 'pending' : '' ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="pr-3">
                                                <div class="mb-1">
                                                    <?php if ($item['audience'] === 'ALL'): ?>
                                                        <span class="ann-badge all">Everyone</span>
                                                    <?php else: ?>
                                                        <span class="ann-badge dept"><?= $h($item['department']) ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($item['importance'] === 'important'): ?><span class="ann-badge imp">Important</span><?php endif; ?>
                                                    <?php if ($approval === 'PENDING'): ?><span class="ann-badge wait">Waiting for Admin approval</span>
                                                    <?php elseif ($approval === 'REJECTED'): ?><span class="ann-badge rej">Not approved</span>
                                                    <?php else: ?>
                                                        <?php if ($scheduled): ?><span class="ann-badge sched">Scheduled</span><?php endif; ?>
                                                        <?php if ((int) $item['is_active'] !== 1): ?><span class="ann-badge off">Hidden</span>
                                                        <?php elseif (!$live && !$scheduled): ?><span class="ann-badge off">Ended</span><?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                                <h6 class="font-weight-bold mb-1"><?= $h($item['title']) ?></h6>
                                                <div class="ann-body"><?= $h($item['body']) ?></div>
                                                <div class="ann-meta mt-1">
                                                    By <?= $h($item['created_by_name'] ?: $item['created_by_role']) ?> ·
                                                    <?= $h(date('M j, Y g:i A', strtotime($item['starts_at']))) ?>
                                                    <?= !empty($item['ends_at']) ? ' to ' . $h(date('M j, Y g:i A', strtotime($item['ends_at']))) : '' ?>
                                                    · <i class="fas fa-eye"></i> <?= (int) $item['read_count'] ?> read
                                                </div>
                                                <?php if ($approval === 'REJECTED' && !empty($item['review_note'])): ?>
                                                    <div class="small text-danger mt-1"><i class="fas fa-comment-dots mr-1"></i>Admin: <?= $h($item['review_note']) ?>
                                                        <?= $isAdmin ? '' : ' — edit it and save to send it again.' ?></div>
                                                <?php endif; ?>
                                                <?php if ($isAdmin && $approval === 'PENDING'): ?>
                                                    <div class="d-flex flex-wrap align-items-start mt-2" style="gap:6px">
                                                        <form method="POST" action="<?= $h($base . $home) ?>" class="d-inline">
                                                            <input type="hidden" name="csrf_token" value="<?= $h($csrf_token) ?>">
                                                            <input type="hidden" name="action" value="approve">
                                                            <input type="hidden" name="announcement_id" value="<?= (int) $item['announcement_id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check mr-1"></i>Approve &amp; post</button>
                                                        </form>
                                                        <form method="POST" action="<?= $h($base . $home) ?>" class="d-flex" style="gap:6px">
                                                            <input type="hidden" name="csrf_token" value="<?= $h($csrf_token) ?>">
                                                            <input type="hidden" name="action" value="reject">
                                                            <input type="hidden" name="announcement_id" value="<?= (int) $item['announcement_id'] ?>">
                                                            <input type="text" name="review_note" class="form-control form-control-sm" maxlength="255"
                                                                   placeholder="Reason (required to reject)" aria-label="Reason for rejecting" style="min-width:220px">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                                        </form>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-nowrap">
                                                <a href="<?= $h($base . $home) ?>?edit=<?= (int) $item['announcement_id'] ?>#announcement-form"
                                                   class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                <form method="POST" action="<?= $h($base . $home) ?>" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $h($csrf_token) ?>">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="announcement_id" value="<?= (int) $item['announcement_id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="<?= (int) $item['is_active'] === 1 ? 'Hide' : 'Show again' ?>">
                                                        <i class="fas <?= (int) $item['is_active'] === 1 ? 'fa-eye-slash' : 'fa-eye' ?>"></i></button>
                                                </form>
                                                <form method="POST" action="<?= $h($base . $home) ?>" class="d-inline" onsubmit="return confirm('Delete this announcement?');">
                                                    <input type="hidden" name="csrf_token" value="<?= $h($csrf_token) ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="announcement_id" value="<?= (int) $item['announcement_id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            </div>
        </div>
    </div>

    <script src="<?= $h($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= $h($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $h($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= $h($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <?php require __DIR__ . '/../partials/flash_modal.php'; ?>
</body>

</html>
