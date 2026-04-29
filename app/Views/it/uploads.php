<?php
$base = rtrim(BASE_URL, '/');
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Employee Uploads</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <style>
        .date-group {
            margin-bottom: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .date-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .date-header .count-badge {
            background-color: rgba(255,255,255,0.3);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
        }

        .upload-item {
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.2s;
        }

        .upload-item:last-child {
            border-bottom: none;
        }

        .upload-item:hover {
            background-color: #f8f9fa;
        }

        .file-icon {
            font-size: 1.5em;
            width: 40px;
            text-align: center;
            margin-right: 15px;
        }

        .pdf .file-icon { color: #e74c3c; }
        .docx .file-icon { color: #3498db; }
        .doc .file-icon { color: #3498db; }
        .jpg .file-icon, .jpeg .file-icon, .png .file-icon { color: #f39c12; }

        .upload-info {
            flex: 1;
        }

        .upload-filename {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .upload-meta {
            font-size: 0.85em;
            color: #7f8c8d;
        }

        .upload-meta span {
            margin-right: 20px;
        }

        .upload-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            white-space: nowrap;
        }

        .file-size {
            font-size: 0.85em;
            color: #95a5a6;
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 3em;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .btn-download {
            padding: 6px 12px;
            font-size: 0.85em;
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <?php
    $activePage = 'uploads';
    require_once __DIR__ . '/../partials/it/sidebar_topbar.php';
    ?>

    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">
            <i class="fas fa-file-upload"></i> Employee Technical Reports
        </h1>

        <!-- Flash Messages -->
        <?php require __DIR__ . '/../partials/flash_modal.php'; ?>

        <!-- Uploads by Date -->
        <?php if (!empty($uploadsByDate)): ?>
            <?php foreach ($uploadsByDate as $date => $uploads): ?>
                <div class="date-group">
                    <div class="date-header">
                        <div>
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo date('F d, Y', strtotime($date)); ?>
                            (<?php echo date('l', strtotime($date)); ?>)
                        </div>
                        <div class="count-badge">
                            <?php echo count($uploads); ?> file<?php echo count($uploads) !== 1 ? 's' : ''; ?>
                        </div>
                    </div>

                    <?php foreach ($uploads as $upload): ?>
                        <?php
                            $ext = strtolower(pathinfo($upload['original_filename'], PATHINFO_EXTENSION));
                            $fileIcon = match($ext) {
                                'pdf' => '<i class="fas fa-file-pdf"></i>',
                                'docx' => '<i class="fas fa-file-word"></i>',
                                'doc' => '<i class="fas fa-file-word"></i>',
                                'jpg' => '<i class="fas fa-image"></i>',
                                'jpeg' => '<i class="fas fa-image"></i>',
                                'png' => '<i class="fas fa-image"></i>',
                                default => '<i class="fas fa-file"></i>'
                            };
                            $fileSize = $upload['file_size'];
                            $fileSizeDisplay = $fileSize > 1048576 ? 
                                round($fileSize / 1048576, 2) . ' MB' : 
                                round($fileSize / 1024, 2) . ' KB';
                        ?>
                        <div class="upload-item <?php echo $ext; ?>">
                            <div class="file-icon">
                                <?php echo $fileIcon; ?>
                            </div>
                            <div class="upload-info">
                                <div class="upload-filename">
                                    <?php echo htmlspecialchars($upload['original_filename']); ?>
                                </div>
                                <div class="upload-meta">
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($upload['uploaded_by_name']); ?></span>
                                    <span><i class="fas fa-ticket-alt"></i> <?php echo htmlspecialchars($upload['ticket_number']); ?></span>
                                    <span><i class="fas fa-clock"></i> <?php echo date('H:i:s', strtotime($upload['date_uploaded'])); ?></span>
                                </div>
                            </div>
                            <div class="upload-actions">
                                <span class="file-size"><?php echo $fileSizeDisplay; ?></span>
                                <a href="<?php echo htmlspecialchars($base); ?>/assets/generatePDF/<?php echo htmlspecialchars($upload['stored_filename']); ?>" 
                                   class="btn btn-sm btn-primary btn-download" 
                                   download
                                   title="Download">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h4>No uploads yet</h4>
                        <p>Employee technical reports will appear here once they are uploaded.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
</body>
</html>
