<?php
$base = rtrim(BASE_URL, '/');
$h = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$schedules = $schedules ?? [];
$fmt = static function (string $value): string {
    return date('M j, Y g:i A', strtotime($value));
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Seasonal Themes</title>
    <link href="<?= $h($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="<?= $h($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= $h($base) ?>/assets/css/admin-users.css" rel="stylesheet">
    <link rel="icon" href="<?= $h($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <style>
        .theme-card { border-radius:14px; overflow:hidden; height:100%; }
        .theme-swatch { height:86px; display:flex; align-items:center; justify-content:center; gap:10px; font-size:1.9rem; color:#fff; }
        .theme-card .card-body { display:flex; flex-direction:column; }
        .theme-live { position:absolute; top:8px; right:8px; background:#1cc88a; color:#fff; font-size:.7rem; font-weight:800; padding:2px 8px; border-radius:999px; }
        .sched-badge { display:inline-block; padding:1px 8px; border-radius:999px; font-size:.7rem; font-weight:700; }
        .sched-badge.live { background:#d1fae5; color:#047857; } .sched-badge.up { background:#e0e7ff; color:#3730a3; }
        .sched-badge.past { background:#f3f4f6; color:#6b7280; } .sched-badge.off { background:#fee2e2; color:#b91c1c; }
    </style>
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'system';
        $systemSubPage = 'themes';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-users-page">

            <div class="page-hero hero-accounts">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-palette mr-2"></i>Seasonal Themes</h1>
                        <p>Give the whole system a seasonal look (Halloween, Christmas, summer...). Preview a theme here first;
                            only you see the preview. Then set its release date and time.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="hero-stat mt-3 mt-lg-0">
                            <div class="stat-value" style="font-size:1.2rem"><?= $h($current['name'] ?? 'None') ?></div>
                            <div class="stat-label">Live now</div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($setupMissing)): ?>
                <div class="alert alert-warning"><i class="fas fa-database mr-1"></i>
                    The database update has not been run yet. Import the update SQL in phpMyAdmin first.</div>
            <?php endif; ?>

            <div class="d-flex flex-wrap align-items-center mb-3">
                <h5 class="mb-0 mr-3 font-weight-bold text-gray-800">Theme list</h5>
                <a class="btn btn-sm btn-outline-danger ml-auto" href="<?= $h($base) ?>/admin/themes?birthday_preview=1">
                    <i class="fas fa-birthday-cake mr-1"></i>Test the birthday greeting</a>
            </div>
            <div class="row">
                <?php foreach (SeasonalThemes::CATALOG as $key => $theme):
                    $range = SeasonalThemes::suggestedRange($key);
                    $isLive = ($current['key'] ?? '') === $key;
                ?>
                    <div class="col-md-6 col-xl-3 mb-4">
                        <div class="card shadow theme-card position-relative">
                            <div class="theme-swatch" style="background:linear-gradient(135deg, <?= $h($theme['colors'][0]) ?>, <?= $h($theme['colors'][1]) ?>)">
                                <?php foreach (array_slice($theme['items'], 0, 3) as $item): ?><span aria-hidden="true"><?= $h($item) ?></span><?php endforeach; ?>
                            </div>
                            <?php if ($isLive): ?><span class="theme-live">LIVE</span><?php endif; ?>
                            <div class="card-body">
                                <h6 class="font-weight-bold mb-1"><i class="fas <?= $h($theme['icon']) ?> mr-1"></i><?= $h($theme['name']) ?></h6>
                                <p class="small text-muted mb-2"><?= $h($theme['message']) ?></p>
                                <p class="small mb-3">
                                    <?php if ($range): ?>
                                        Suggested: <strong><?= $h(date('M j', strtotime($range['from']))) ?> – <?= $h(date('M j, Y', strtotime($range['to']))) ?></strong>
                                    <?php else: ?>
                                        Any date (company events)
                                    <?php endif; ?>
                                </p>
                                <div class="mt-auto d-flex flex-wrap" style="gap:6px">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= $h($base) ?>/admin/themes?theme_preview=<?= $h($key) ?>">
                                        <i class="fas fa-eye mr-1"></i>Preview</a>
                                    <button type="button" class="btn btn-sm btn-primary js-schedule"
                                            data-key="<?= $h($key) ?>"
                                            data-from="<?= $h($range['from'] ?? '') ?>" data-to="<?= $h($range['to'] ?? '') ?>">
                                        <i class="fas fa-calendar-plus mr-1"></i>Schedule</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="row">
                <div class="col-xl-5">
                    <div class="card form-card shadow mb-4" id="schedule-form">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-calendar-alt mr-1"></i>Schedule a release</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= $h($base) ?>/admin/themes">
                                <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                                <input type="hidden" name="action" value="schedule" id="t_action">
                                <div class="form-group">
                                    <label for="t_key">Theme</label>
                                    <select class="form-control" id="t_key" name="theme_key" required>
                                        <?php foreach (SeasonalThemes::CATALOG as $key => $theme): ?>
                                            <option value="<?= $h($key) ?>"><?= $h($theme['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-sm-6">
                                        <label for="t_from">Release date / time</label>
                                        <input type="datetime-local" class="form-control" id="t_from" name="starts_at">
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label for="t_to">Ends</label>
                                        <input type="datetime-local" class="form-control" id="t_to" name="ends_at">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="t_msg">Greeting <small class="text-muted">(optional; default greeting when empty)</small></label>
                                    <input type="text" class="form-control" id="t_msg" name="message" maxlength="200" placeholder="e.g. Happy Halloween, team!">
                                </div>
                                <div class="d-flex flex-wrap align-items-center" style="gap:8px">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Save schedule</button>
                                    <span class="text-muted small">or</span>
                                    <div class="input-group input-group-sm" style="width:auto">
                                        <select class="custom-select custom-select-sm" name="days" aria-label="Days">
                                            <option value="1">1 day</option><option value="3">3 days</option>
                                            <option value="7" selected>7 days</option><option value="14">14 days</option><option value="30">30 days</option>
                                        </select>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-outline-success" onclick="document.getElementById('t_action').value='now'">
                                                Start now</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-7">
                    <div class="card data-list-card shadow mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list mr-1"></i>Live, upcoming and past</h6>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!$schedules): ?>
                                <p class="text-muted p-4 mb-0">Nothing scheduled yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead><tr><th>Theme</th><th>From</th><th>To</th><th>Status</th><th></th></tr></thead>
                                        <tbody>
                                            <?php foreach ($schedules as $s):
                                                $name = SeasonalThemes::CATALOG[$s['theme_key']]['name'] ?? $s['theme_key'];
                                                if ((int) $s['is_active'] !== 1) {
                                                    $status = ['off', 'Off'];
                                                } elseif ((int) $s['is_live'] === 1) {
                                                    $status = ['live', 'Live now'];
                                                } elseif ((int) $s['is_upcoming'] === 1) {
                                                    $status = ['up', 'Upcoming'];
                                                } else {
                                                    $status = ['past', 'Ended'];
                                                }
                                            ?>
                                                <tr>
                                                    <td><strong><?= $h($name) ?></strong>
                                                        <?php if (!empty($s['message'])): ?><div class="small text-muted"><?= $h($s['message']) ?></div><?php endif; ?></td>
                                                    <td class="small"><?= $h($fmt($s['starts_at'])) ?></td>
                                                    <td class="small"><?= $h($fmt($s['ends_at'])) ?></td>
                                                    <td><span class="sched-badge <?= $status[0] ?>"><?= $status[1] ?></span></td>
                                                    <td class="text-nowrap text-right">
                                                        <?php if ($status[0] === 'live'): ?>
                                                            <form method="POST" action="<?= $h($base) ?>/admin/themes" class="d-inline">
                                                                <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                                                                <input type="hidden" name="action" value="stop">
                                                                <input type="hidden" name="schedule_id" value="<?= (int) $s['schedule_id'] ?>">
                                                                <button class="btn btn-sm btn-outline-warning" title="End now"><i class="fas fa-stop"></i></button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <?php if ($status[0] !== 'past'): ?>
                                                            <form method="POST" action="<?= $h($base) ?>/admin/themes" class="d-inline">
                                                                <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                                                                <input type="hidden" name="action" value="toggle">
                                                                <input type="hidden" name="schedule_id" value="<?= (int) $s['schedule_id'] ?>">
                                                                <button class="btn btn-sm btn-outline-secondary" title="<?= (int) $s['is_active'] === 1 ? 'Turn off' : 'Turn on' ?>">
                                                                    <i class="fas <?= (int) $s['is_active'] === 1 ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i></button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <form method="POST" action="<?= $h($base) ?>/admin/themes" class="d-inline" onsubmit="return confirm('Delete this schedule?');">
                                                            <input type="hidden" name="csrf_token" value="<?= $h($csrf_token ?? '') ?>">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="schedule_id" value="<?= (int) $s['schedule_id'] ?>">
                                                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
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
    <script>
        document.querySelectorAll('.js-schedule').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('t_key').value = btn.getAttribute('data-key');
                document.getElementById('t_from').value = btn.getAttribute('data-from');
                document.getElementById('t_to').value = btn.getAttribute('data-to');
                var form = document.getElementById('schedule-form');
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                document.getElementById('t_from').focus();
            });
        });
    </script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
