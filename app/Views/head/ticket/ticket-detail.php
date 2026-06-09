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
</head>

<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'tickets';
    require_once __DIR__ . '/../../partials/head/sidebar_topbar.php';
    ?>
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Ticket Details</h1>
            <a href="<?= htmlspecialchars($base) ?>/head/tickets" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars((string) $_SESSION['flash_error']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php if ($ticket): ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-light">
                            <h6 class="m-0 font-weight-bold text-gray-800">
                                <?php echo htmlspecialchars((string) ($ticket['ticket_number'] ?? '')); ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p><strong>Employee:</strong> <?php echo htmlspecialchars(trim((string) ($ticket['emp_firstname'] ?? '') . ' ' . ($ticket['emp_lastname'] ?? ''))); ?></p>
                                    <p><strong>Branch:</strong> <?php echo htmlspecialchars((string) ($ticket['branchName'] ?? '')); ?></p>
                                    <p><strong>Priority:</strong>
                                        <?php
                                        $priority = (string) ($ticket['priority'] ?? 'Low');
                                        $priorityClass = $priority === 'High' ? 'danger' : ($priority === 'Medium' ? 'warning' : 'success');
                                        ?>
                                        <span class="badge badge-<?php echo $priorityClass; ?>">
                                            <?php echo htmlspecialchars($priority); ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status:</strong>
                                        <?php
                                        $status = (string) ($ticket['status'] ?? 'Pending');
                                        $statusClass = $status === 'Pending' ? 'warning' : ($status === 'In Progress' ? 'info' : ($status === 'Resolved' ? 'success' : 'secondary'));
                                        ?>
                                        <span class="badge badge-<?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($status); ?>
                                        </span>
                                    </p>
                                    <p><strong>Filed Date:</strong> <?php echo htmlspecialchars(date('M d, Y H:i', strtotime((string) ($ticket['date_filed'] ?? '')))); ?></p>
                                    <p><strong>Remarks:</strong> <?php echo htmlspecialchars((string) ($ticket['remarks'] ?? 'No remarks')); ?></p>
                                </div>
                            </div>

                            <hr>

                            <h6 class="font-weight-bold mb-2">Ticket Description</h6>
                            <p><?php echo nl2br(htmlspecialchars((string) ($ticket['concern_details'] ?? ''))); ?></p>
                        </div>
                    </div>

                    <?php if (!empty($history)): ?>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Ticket History</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($history as $entry): ?>
                                    <div class="mb-3">
                                        <p class="mb-1">
                                            <strong><?php echo htmlspecialchars((string) ($entry['action_details'] ?? '')); ?></strong>
                                        </p>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars((string) ($entry['performed_by'] ?? 'System')); ?> •
                                            <?php echo htmlspecialchars(date('M d, Y H:i', strtotime((string) ($entry['date_logged'] ?? '')))); ?>
                                        </small>
                                        <hr>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php
                    $ticketId = (int) ($ticket['ticket_id'] ?? 0);
                    $canPostComments = true;
                    require __DIR__ . '/../../partials/ticket/comments_section.php';
                    ?>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <p class="text-xs text-uppercase text-muted mb-1">Ticket ID</p>
                                <p class="h6 mb-0"><?php echo (int) ($ticket['ticket_id'] ?? 0); ?></p>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <p class="text-xs text-uppercase text-muted mb-1">Status</p>
                                <p class="h6 mb-0">
                                    <?php
                                    $statusIcon = 'fa-circle text-secondary';
                                    if ($status === 'Pending') $statusIcon = 'fa-clock text-warning';
                                    elseif ($status === 'In Progress') $statusIcon = 'fa-spinner text-info';
                                    elseif ($status === 'Resolved') $statusIcon = 'fa-check-circle text-success';
                                    elseif ($status === 'Closed') $statusIcon = 'fa-times-circle text-dark';
                                    ?>
                                    <i class="fas <?php echo $statusIcon; ?>"></i> <?php echo htmlspecialchars($status); ?>
                                </p>
                            </div>
                            <hr>
                            <?php if (($ticket['status'] ?? '') === 'Resolved'): ?>
                            <div class="mb-3">
                                <a href="<?= htmlspecialchars($base) ?>/head/tickets/download-record?id=<?= (int)($ticket['ticket_id'] ?? 0) ?>" class="btn btn-info btn-block btn-sm">
                                    <i class="fas fa-download"></i> Download Technical Record
                                </a>
                            </div>
                            <div class="mb-3">
                                <button class="btn btn-primary btn-block btn-sm rateBtn" data-ticketid="<?= (int)($ticket['ticket_id'] ?? 0) ?>">
                                    <i class="fas fa-star"></i> Rate Ticket
                                </button>
                            </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <?php
                                $ticketId = (int) ($ticket['ticket_id'] ?? 0);
                                $ticketStatus = (string) ($ticket['status'] ?? '');
                                $ticketNumber = (string) ($ticket['ticket_number'] ?? '');
                                $btnBlock = true;
                                require __DIR__ . '/../../partials/ticket/cancel_ticket_button.php';
                                ?>
                            </div>
                            <div>
                                <a href="<?= htmlspecialchars($base) ?>/head/tickets" class="btn btn-block btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to Tickets
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> Ticket not found.
            </div>
            <a href="<?= htmlspecialchars($base) ?>/head/tickets" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Tickets
            </a>
        <?php endif; ?>

    </div>
</div>
</div>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>

<script>
    $(function () {
        $(document).on('click', '.rateBtn', function () {
            const ticketId = $(this).data('ticketid');
            const base = "<?= htmlspecialchars($base) ?>";

            $.get(base + '/head/tickets/rate?id=' + ticketId)
                .done(function(html) {
                    const container = document.getElementById('rateTicketModalBody');
                    if (!container) {
                        alert('Rating modal not available.');
                        return;
                    }
                    container.innerHTML = html;
                    const scripts = Array.from(container.querySelectorAll('script'));
                    scripts.forEach(oldScript => {
                        const newScript = document.createElement('script');
                        if (oldScript.src) {
                            newScript.src = oldScript.src;
                            if (oldScript.async) newScript.async = true;
                            if (oldScript.defer) newScript.defer = true;
                            document.body.appendChild(newScript);
                        } else {
                            newScript.textContent = oldScript.textContent;
                            document.body.appendChild(newScript);
                        }
                    });
                    $('#rateTicketModal').modal('show');
                })
                .fail(function() {
                    alert('Failed to load rating form. Please try again.');
                });
        });
    });
</script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/ticket/ticket_comments.js"></script>
<?php require __DIR__ . '/../../partials/ticket/cancel_ticket_modal.php'; ?>

</body>
</html>
