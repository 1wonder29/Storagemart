<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | HR — File Ticket</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/ticket-create.css" rel="stylesheet">
</head>
<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'tickets';
    require_once __DIR__ . '/../../partials/hr/sidebar_topbar.php';
    ?>
    <div class="container-fluid ticket-create-page">
        <div class="page-hero">
            <h1><i class="fas fa-ticket-alt mr-2"></i>File Ticket</h1>
            <p>Submit a support request from HR. Asset details are included when filing from an assigned asset.</p>
        </div>

        <?php require __DIR__ . '/../../partials/ticket/flash_messages.php'; ?>

        <div class="row">
            <div class="col-lg-9 col-xl-8">
                <div class="card shadow mb-4 ticket-form-card">
                    <div class="card-header ticket-header-employee text-white">
                        <h6 class="font-weight-bold text-white"><i class="fas fa-clipboard-list mr-1"></i> File Ticket</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $formAction = htmlspecialchars($base) . '/hr/assets/file_ticket';
                        $cancelUrl = htmlspecialchars($base) . '/hr/tickets';
                        $showAssetSection = !empty($inventory['inventory_id']);
                        require __DIR__ . '/../../partials/ticket/file_ticket_form.php';
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

            </div>
<a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
</body>
</html>
