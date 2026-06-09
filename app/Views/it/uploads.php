<?php
$base = rtrim(BASE_URL, '/');

$totalFiles = 0;
$totalSize = 0;
$dateCount = 0;
$thisMonth = 0;
$employees = [];
$dates = [];
$now = time();

foreach ($uploadsByDate as $date => $uploads) {
    $dateCount++;
    $dates[$date] = date('F d, Y', strtotime($date));
    foreach ($uploads as $upload) {
        $totalFiles++;
        $totalSize += (int) ($upload['file_size'] ?? 0);
        $name = trim((string) ($upload['uploaded_by_name'] ?? ''));
        if ($name !== '') {
            $employees[$name] = true;
        }
        $uploaded = strtotime((string) ($upload['date_uploaded'] ?? ''));
        if ($uploaded && (int) date('Y', $uploaded) === (int) date('Y', $now) && (int) date('n', $uploaded) === (int) date('n', $now)) {
            $thisMonth++;
        }
    }
}

ksort($dates);
ksort($employees);

$formatSize = static function (int $bytes): string {
    if ($bytes > 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    }
    if ($bytes > 1024) {
        return round($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' B';
};

$totalSizeDisplay = $formatSize($totalSize);

$fileIconClass = static function (string $ext): string {
    return match ($ext) {
        'pdf' => 'pdf',
        'docx', 'doc' => 'docx',
        'jpg', 'jpeg', 'png' => 'jpg',
        default => 'default',
    };
};
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Employee Technical Reports</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../partials/it/theme_head.php'; ?>
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.png" type="image/png">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/it-uploads.css" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <?php
    $activePage = 'uploads';
    require_once __DIR__ . '/../partials/it/sidebar_topbar.php';
    ?>

    <div class="container-fluid it-uploads-page">

        <div class="page-hero hero-uploads">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1><i class="fas fa-file-alt mr-2"></i>Employee Technical Reports</h1>
                    <p>Download and review technical records uploaded by employees — search by ticket, name, or date.</p>
                    <div class="quick-nav mt-3">
                        <a href="<?= htmlspecialchars($base) ?>/it/tickets/in_progress" class="btn btn-sm btn-outline-light mr-1">
                            <i class="fas fa-spinner mr-1"></i> In Progress
                        </a>
                        <a href="<?= htmlspecialchars($base) ?>/it/tickets/resolve" class="btn btn-sm btn-outline-light mr-1">
                            <i class="fas fa-check-circle mr-1"></i> Resolved
                        </a>
                        <a href="<?= htmlspecialchars($base) ?>/it/tickets" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-ticket-alt mr-1"></i> My Tickets
                        </a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="row mt-3 mt-lg-0">
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int) $totalFiles ?></div>
                                <div class="stat-label">Total Files</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= (int) $thisMonth ?></div>
                                <div class="stat-label">This Month</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="hero-stat">
                                <div class="stat-value"><?= htmlspecialchars($totalSizeDisplay) ?></div>
                                <div class="stat-label">Total Size</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php require __DIR__ . '/../partials/flash_modal.php'; ?>

        <?php if (!empty($uploadsByDate)): ?>
            <div class="filter-toolbar">
                <div class="row align-items-end">
                    <div class="col-lg-4 col-md-6 mb-2 mb-lg-0">
                        <label for="uploadSearch">Search</label>
                        <input type="text" id="uploadSearch" class="form-control form-control-sm"
                               placeholder="Ticket #, employee, or filename…">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <label for="uploadDateFilter">Date</label>
                        <select id="uploadDateFilter" class="form-control form-control-sm">
                            <option value="">All Dates</option>
                            <?php foreach ($dates as $rawDate => $label): ?>
                                <option value="<?= htmlspecialchars($rawDate) ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <label for="uploadEmployeeFilter">Employee</label>
                        <select id="uploadEmployeeFilter" class="form-control form-control-sm">
                            <option value="">All Employees</option>
                            <?php foreach (array_keys($employees) as $employee): ?>
                                <option value="<?= htmlspecialchars($employee) ?>"><?= htmlspecialchars($employee) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 text-lg-right">
                        <button type="button" id="uploadClearFilters" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-undo mr-1"></i> Clear
                        </button>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-folder-open mr-1"></i> Reports by Date
                </h6>
                <span class="badge badge-info" id="uploadResultsCount"><?= (int) $totalFiles ?> file<?= $totalFiles === 1 ? '' : 's' ?></span>
            </div>

            <div id="uploadNoResults" class="no-results">
                <i class="fas fa-search d-block mb-2" style="font-size:1.75rem;opacity:0.35;"></i>
                No reports match your search or filters.
            </div>

            <?php foreach ($uploadsByDate as $date => $uploads): ?>
                <div class="upload-date-group" data-upload-date="<?= htmlspecialchars($date) ?>">
                    <div class="upload-date-header">
                        <div class="upload-date-title">
                            <i class="fas fa-calendar-day"></i>
                            <?= date('F d, Y', strtotime($date)) ?>
                            <span class="text-muted font-weight-normal">(<?= date('l', strtotime($date)) ?>)</span>
                        </div>
                        <span class="upload-date-badge">
                            <?= count($uploads) ?> file<?= count($uploads) !== 1 ? 's' : '' ?>
                        </span>
                    </div>

                    <?php foreach ($uploads as $upload):
                        $ext = strtolower(pathinfo($upload['original_filename'], PATHINFO_EXTENSION));
                        $iconType = $fileIconClass($ext);
                        $fileSize = (int) ($upload['file_size'] ?? 0);
                        $ticketId = (int) ($upload['ticket_id'] ?? 0);
                        $ticketNumber = (string) ($upload['ticket_number'] ?? '');
                        $employeeName = (string) ($upload['uploaded_by_name'] ?? '');
                        $filename = (string) ($upload['original_filename'] ?? '');
                        $uploadedAt = strtotime((string) ($upload['date_uploaded'] ?? ''));
                    ?>
                        <div class="upload-file-row"
                             data-ticket="<?= htmlspecialchars($ticketNumber) ?>"
                             data-employee="<?= htmlspecialchars($employeeName) ?>"
                             data-filename="<?= htmlspecialchars($filename) ?>">
                            <div class="file-type-icon <?= $iconType ?>">
                                <?php
                                echo match ($iconType) {
                                    'pdf' => '<i class="fas fa-file-pdf"></i>',
                                    'docx' => '<i class="fas fa-file-word"></i>',
                                    'jpg' => '<i class="fas fa-file-image"></i>',
                                    default => '<i class="fas fa-file"></i>',
                                };
                                ?>
                            </div>
                            <div class="upload-file-main">
                                <div class="upload-file-top">
                                    <?php if ($ticketId > 0): ?>
                                        <a href="<?= htmlspecialchars($base) ?>/it/tickets/view?id=<?= $ticketId ?>"
                                           class="ticket-ref" title="View ticket">
                                            <?= htmlspecialchars($ticketNumber) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="ticket-ref"><?= htmlspecialchars($ticketNumber) ?></span>
                                    <?php endif; ?>
                                    <span class="report-type-pill">
                                        <i class="fas fa-file-medical-alt mr-1"></i>Technical Report
                                    </span>
                                </div>
                                <div class="upload-filename" title="<?= htmlspecialchars($filename) ?>">
                                    <?= htmlspecialchars($filename) ?>
                                </div>
                                <div class="upload-meta">
                                    <span><i class="fas fa-user"></i><?= htmlspecialchars($employeeName) ?></span>
                                    <span>
                                        <i class="fas fa-clock"></i>
                                        <?= $uploadedAt ? date('g:i A', $uploadedAt) : '—' ?>
                                    </span>
                                    <span><i class="fas fa-file"></i><?= strtoupper($ext ?: 'file') ?></span>
                                </div>
                            </div>
                            <div class="upload-file-actions">
                                <span class="file-size-badge"><?= htmlspecialchars($formatSize($fileSize)) ?></span>
                                <a href="<?= htmlspecialchars($base) ?>/assets/generatePDF/<?= htmlspecialchars($upload['stored_filename']) ?>"
                                   class="btn btn-sm btn-primary btn-download-report"
                                   download
                                   title="Download report">
                                    <i class="fas fa-download mr-1"></i> Download
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

        <?php else: ?>
            <div class="card empty-state-card shadow">
                <div class="empty-state">
                    <i class="fas fa-inbox d-block"></i>
                    <h5 class="font-weight-bold text-gray-700">No uploads yet</h5>
                    <p class="mb-0">Employee technical reports will appear here once they are uploaded.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
<?php if (!empty($uploadsByDate)): ?>
<script src="<?= htmlspecialchars($base) ?>/assets/js/it-uploads.js"></script>
<?php endif; ?>
</body>
</html>
