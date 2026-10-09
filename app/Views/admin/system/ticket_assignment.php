<?php
$base = rtrim(BASE_URL, '/');
$zones = $zones ?? [];
$branches = $branches ?? [];
$staff = $staff ?? [];
$zoneCodes = array_column($zones, 'zone');
$staffNames = [];
foreach ($staff as $member) {
    $staffNames[(int) $member['employee_id']] = (string) $member['name'];
}
$branchesPerZone = [];
foreach ($branches as $branch) {
    $branchesPerZone[(string) ($branch['zone'] ?? '')][] = $branch['branchName'];
}
?>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Ticket Auto-Assignment</title>
    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/admin-users.css" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <style>
        .zone-chip { display:inline-block; padding:2px 10px; border-radius:999px; font-weight:700; font-size:.78rem; background:#eef2ff; color:#4e73df; }
        .zone-chip.none { background:#f3f4f6; color:#6b7280; }
        .rule-list li { margin-bottom:.35rem; }
        .branch-zone-select { max-width:160px; }
        .away-switch .custom-control-label { cursor:pointer; }
        .duty-now { display:flex; align-items:center; gap:10px; flex-wrap:wrap; padding:10px 14px; border-radius:10px; background:#fff4ec; color:#9a3412; font-weight:600; margin-bottom:14px; }
        .duty-now.on { background:#e8f6ee; color:#13633f; }
        .duty-table td, .duty-table th { vertical-align:middle; }
        .duty-sat { display:inline-block; padding:1px 8px; margin:0 4px 4px 0; border-radius:999px; background:#eef2ff; color:#19398a; font-size:.75rem; font-weight:700; }
        .duty-sat.changed { background:#fff1e8; color:#c2410c; }
        .duty-days { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:8px 16px; padding:8px 0 4px; }
        .duty-days label { font-size:.78rem; font-weight:700; margin-bottom:2px; }
        html.it-dark .duty-now { background:#3a2a1f; color:#fdba74; }
        html.it-dark .duty-now.on { background:#1f3a2c; color:#86efac; }
    </style>
</head>

<body id="page-top">

    <div id="wrapper">
        <?php
        $activePage = 'system';
        $systemSubPage = 'ticket-assignment';
        require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
        ?>

        <div class="container-fluid admin-users-page">

            <div class="page-hero hero-accounts">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1><i class="fas fa-random mr-2"></i>Ticket Auto-Assignment</h1>
                        <p>New tickets go straight to IT without admin approval: branch tickets to the zone's IT person,
                            Head Office (and branches without a zone) to a random available IT person.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="hero-stat mt-3 mt-lg-0">
                            <div class="stat-value"><?= $autoAssignEnabled ? 'ON' : 'OFF' ?></div>
                            <div class="stat-label">Automatic assignment</div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($setupMissing)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-database mr-1"></i>
                    The database update has not been run yet, so zones cannot be loaded. Import the G2 SQL file in phpMyAdmin first.
                </div>
            <?php endif; ?>

            <form action="<?= htmlspecialchars($base) ?>/admin/ticket-assignment" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

                <div class="row">
                    <div class="col-12">
                        <div class="card form-card shadow mb-4">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-sliders-h mr-1"></i> Rules</h6>
                            </div>
                            <div class="card-body">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="auto_assign_enabled" name="auto_assign_enabled" value="1" <?= $autoAssignEnabled ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="auto_assign_enabled">Assign new tickets automatically</label>
                                    <small class="form-text text-muted">When off, new tickets wait in Pending for the admin to assign, as before.</small>
                                </div>
                                <label for="reassign_after_minutes" class="form-label">Move a ticket to the next IT person when not opened within</label>
                                <div class="input-group mb-2" style="max-width:220px">
                                    <input type="number" min="5" max="480" class="form-control" id="reassign_after_minutes" name="reassign_after_minutes" value="<?= (int) $reassignMinutes ?>">
                                    <div class="input-group-append"><span class="input-group-text">minutes</span></div>
                                </div>
                                <ul class="small text-muted rule-list mb-0 pl-3">
                                    <li>Opening the ticket counts as accepting it; it then stays with that person.</li>
                                    <li>Each other IT person gets one turn, so tickets do not bounce back and forth.</li>
                                    <li>IT staff marked <strong>Away</strong> get no new tickets; their unopened tickets move right away.</li>
                                    <li>Admins are never auto-assigned (e.g. Kenneth Dador).</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card form-card shadow mb-4" id="saturday-duty">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-calendar-week mr-1"></i> Saturday duty (monthly rotation)</h6>
                        <span class="small text-muted">On Saturdays every new ticket goes to this IT person</span>
                    </div>
                    <div class="card-body">
                        <?php
                        $dutyName = static function (?int $id) use ($staffNames): string {
                            return $id ? ($staffNames[$id] ?? 'an IT person who is not active') : '';
                        };
                        $satLabel = $dutyIsToday ? 'Today (Saturday)' : 'Next Saturday (' . date('M j', strtotime($nextSaturday)) . ')';
                        ?>
                        <?php if ($dutyNext): ?>
                            <div class="duty-now on"><i class="fas fa-user-clock"></i> <?= htmlspecialchars($satLabel) ?>: every new ticket goes to <?= htmlspecialchars($dutyName($dutyNext)) ?>.</div>
                        <?php else: ?>
                            <div class="duty-now"><i class="fas fa-info-circle"></i> <?= htmlspecialchars($satLabel) ?>: nobody is set, so the zone rules are used.</div>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <table class="table table-sm duty-table mb-2">
                                <thead><tr><th>Month</th><th>Saturdays</th><th style="min-width:230px">IT person on duty</th></tr></thead>
                                <tbody>
                                <?php foreach ($saturdayMonths as $month => $saturdays):
                                    $monthDuty = $dutyMonths[$month] ?? 0;
                                    $monthName = date('F Y', strtotime($month . '-01'));
                                ?>
                                    <tr>
                                        <td class="font-weight-bold text-nowrap"><?= htmlspecialchars($monthName) ?></td>
                                        <td>
                                            <?php foreach ($saturdays as $sat):
                                                $changed = array_key_exists($sat, $dutyDates);
                                                $changeText = $changed ? 'Changed: ' . ($dutyDates[$sat] ? $dutyName($dutyDates[$sat]) : 'normal zone rules') : '';
                                            ?>
                                                <span class="duty-sat<?= $changed ? ' changed' : '' ?>" title="<?= htmlspecialchars($changeText) ?>"><?= date('M j', strtotime($sat)) ?><?= $changed ? ' *' : '' ?></span>
                                            <?php endforeach; ?>
                                        </td>
                                        <td>
                                            <select class="custom-select custom-select-sm" name="saturday_duty[<?= htmlspecialchars($month) ?>]"
                                                    aria-label="IT person on Saturday duty in <?= htmlspecialchars($monthName) ?>">
                                                <option value="0">-- Nobody (normal zone rules) --</option>
                                                <?php foreach ($staff as $member): ?>
                                                    <option value="<?= (int) $member['employee_id'] ?>" <?= (int) $member['employee_id'] === $monthDuty ? 'selected' : '' ?>><?= htmlspecialchars($member['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <details class="mt-1">
                                                <summary class="small text-primary">Change one Saturday</summary>
                                                <div class="duty-days">
                                                    <?php foreach ($saturdays as $sat):
                                                        $dayValue = array_key_exists($sat, $dutyDates) ? ($dutyDates[$sat] ? (string) $dutyDates[$sat] : 'none') : '';
                                                    ?>
                                                        <div>
                                                            <label for="duty_<?= htmlspecialchars($sat) ?>"><?= date('l, M j', strtotime($sat)) ?></label>
                                                            <select class="custom-select custom-select-sm" id="duty_<?= htmlspecialchars($sat) ?>" name="saturday_duty_date[<?= htmlspecialchars($sat) ?>]">
                                                                <option value="">Same as the month</option>
                                                                <?php foreach ($staff as $member): ?>
                                                                    <option value="<?= (int) $member['employee_id'] ?>" <?= $dayValue === (string) (int) $member['employee_id'] ? 'selected' : '' ?>><?= htmlspecialchars($member['name']) ?></option>
                                                                <?php endforeach; ?>
                                                                <option value="none" <?= $dayValue === 'none' ? 'selected' : '' ?>>Nobody (normal zone rules)</option>
                                                            </select>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </details>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <ul class="small text-muted rule-list mb-0 pl-3">
                            <li>On a Saturday, every new ticket (all branches and Head Office) goes to the person on duty, and tickets not opened in time move to them too.</li>
                            <li>The person on duty keeps their tickets that day. If they are marked <strong>Away</strong>, the normal zone rules are used.</li>
                            <li>Use "Change one Saturday" for swaps, for example when the person on duty is on leave that day.</li>
                            <li>Press <strong>Save Settings</strong> at the bottom of the page.</li>
                        </ul>
                    </div>
                </div>

                <div class="card form-card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-map-marked-alt mr-1"></i> Zones</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($zones as $zone): $code = (string) $zone['zone']; ?>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="zone_it_<?= htmlspecialchars($code) ?>"><span class="zone-chip"><?= htmlspecialchars($zone['label']) ?></span> IT person</label>
                                    <select class="form-control" id="zone_it_<?= htmlspecialchars($code) ?>" name="zone_it[<?= htmlspecialchars($code) ?>]">
                                        <option value="">-- Nobody (random) --</option>
                                        <?php foreach ($staffNames as $eid => $name): ?>
                                            <option value="<?= $eid ?>" <?= (int) $zone['it_employee_id'] === $eid ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">
                                        <?= htmlspecialchars(implode(', ', $branchesPerZone[$code] ?? []) ?: 'No branches yet') ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="new_zone">Add a zone</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="new_zone" name="new_zone" maxlength="10" placeholder="e.g. C">
                                    <select class="form-control" name="new_zone_it" aria-label="IT person for the new zone">
                                        <option value="">-- IT person --</option>
                                        <?php foreach ($staffNames as $eid => $name): ?>
                                            <option value="<?= $eid ?>"><?= htmlspecialchars($name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card data-list-card shadow mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-store mr-1"></i> Branch Zones</h6>
                        <span class="badge badge-info"><?= count($branches) ?> branches</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr><th>Branch</th><th>Code</th><th>Staff</th><th>Zone</th><th>Tickets go to</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($branches as $branch):
                                        $bz = (string) ($branch['zone'] ?? '');
                                        $zoneIt = null;
                                        foreach ($zones as $zone) {
                                            if ($zone['zone'] === $bz) {
                                                $zoneIt = $staffNames[(int) $zone['it_employee_id']] ?? null;
                                            }
                                        }
                                    ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($branch['branchName']) ?></strong></td>
                                            <td><?= htmlspecialchars($branch['branchCode']) ?></td>
                                            <td><?= (int) $branch['staff_count'] ?></td>
                                            <td>
                                                <select class="form-control form-control-sm branch-zone-select" name="branch_zone[<?= (int) $branch['branch_id'] ?>]">
                                                    <option value="">No zone</option>
                                                    <?php foreach ($zoneCodes as $code): ?>
                                                        <option value="<?= htmlspecialchars($code) ?>" <?= $bz === $code ? 'selected' : '' ?>>Zone <?= htmlspecialchars($code) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <?php if ($bz !== '' && $zoneIt): ?>
                                                    <span class="zone-chip"><?= htmlspecialchars($zoneIt) ?></span>
                                                <?php else: ?>
                                                    <span class="zone-chip none">Random available IT</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card form-card shadow mb-4" id="it-availability">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-check mr-1"></i> IT Staff Availability</h6>
                        <span class="small text-muted">Away = no new tickets; unopened ones move to another IT person</span>
                    </div>
                    <div class="card-body">
                        <?php if (!$staff): ?>
                            <p class="text-muted mb-0">No active IT accounts.</p>
                        <?php endif; ?>
                        <div class="row">
                            <?php foreach ($staff as $member): $eid = (int) $member['employee_id']; ?>
                                <div class="col-md-6 col-xl-4">
                                    <input type="hidden" name="it_staff[]" value="<?= $eid ?>">
                                    <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                                        <div>
                                            <strong><?= htmlspecialchars($member['name']) ?></strong>
                                            <div class="small text-muted"><?= (int) $member['active_load'] ?> active ticket<?= (int) $member['active_load'] === 1 ? '' : 's' ?></div>
                                        </div>
                                        <div class="custom-control custom-switch away-switch">
                                            <input type="checkbox" class="custom-control-input" id="away_<?= $eid ?>" name="it_away[<?= $eid ?>]" value="1" <?= (int) $member['is_away'] === 1 ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="away_<?= $eid ?>">Away</label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Settings</button>
                </div>
            </form>
        </div>

            </div>
        </div>
    </div>

    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
    <?php require __DIR__ . '/../../partials/flash_modal.php'; ?>
</body>

</html>
