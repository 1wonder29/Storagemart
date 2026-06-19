<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Ticket Detail</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../../partials/it/theme_head.php'; ?>
    <?php require_once __DIR__ . '/../../partials/ticket/ticket_detail_assets.php'; ?>
</head>

<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'tickets';
    require_once __DIR__ . '/../../partials/it/sidebar_topbar.php';
    ?>
    <div class="container-fluid ticket-detail-page theme-it">
        <?php
        $ticketBackUrl = rtrim($base, '/') . ($backUrl ?? '/it/tickets');
        require __DIR__ . '/../../partials/ticket/ticket_detail_page_header.php';
        ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show alert-modern" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars((string) $_SESSION['flash_error']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php if ($ticket): ?>
            <?php
            $routePrefix = 'it';
            $ticketsListUrl = rtrim($base, '/') . ($backUrl ?? '/it/tickets');
            $showTechnicalUpload = false;
            $showRateDownload = false;
            require __DIR__ . '/../../partials/ticket/ticket_detail_content.php';
            ?>
        <?php else: ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> Ticket not found.
            </div>
            <a href="<?= htmlspecialchars($base) ?><?= htmlspecialchars($backUrl ?? '/it/tickets') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Tickets
            </a>
        <?php endif; ?>

    </div>
</div>
</div>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/ticket/ticket_comments.js"></script>
<?php require __DIR__ . '/../../partials/ticket/cancel_ticket_modal.php'; ?>

</body>
</html>
