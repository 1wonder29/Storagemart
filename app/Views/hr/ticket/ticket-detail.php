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
    <?php require_once __DIR__ . '/../../partials/ticket/ticket_detail_assets.php'; ?>
</head>

<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'tickets';
    require_once __DIR__ . '/../../partials/hr/sidebar_topbar.php';
    ?>
    <div class="container-fluid ticket-detail-page theme-hr">
        <?php
        ob_start();
        if ($ticket) {
            $ticketId = (int) ($ticket['ticket_id'] ?? 0);
            $ticketStatus = (string) ($ticket['status'] ?? '');
            $ticketNumber = (string) ($ticket['ticket_number'] ?? '');
            $btnClass = 'btn-danger btn-sm mr-2';
            require __DIR__ . '/../../partials/ticket/cancel_ticket_button.php';
        }
        $ticketDetailHeaderExtra = ob_get_clean();
        $ticketBackUrl = $base . '/hr/tickets';
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
            $routePrefix = 'hr';
            $showTechnicalUpload = true;
            $showRateDownload = true;
            require __DIR__ . '/../../partials/ticket/ticket_detail_content.php';
            ?>
        <?php else: ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> Ticket not found.
            </div>
            <a href="<?= htmlspecialchars($base) ?>/hr/tickets" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Tickets
            </a>
        <?php endif; ?>

    </div>
</div>
</div>

            </div>
<div class="modal fade" id="rateTicketModal" tabindex="-1" role="dialog" aria-labelledby="rateTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rateTicketModalLabel">Rate This Ticket</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="rateTicketModalBody"></div>
        </div>
    </div>
</div>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>

<script>
    $(function () {
        $('#uploadForm').on('submit', function (e) {
            e.preventDefault();
            const $msg = $('#uploadMsg');
            const $btn = $('#uploadBtn');
            $msg.addClass('d-none').removeClass('alert-success alert-danger').text('');

            const formData = new FormData(this);
            const base = "<?= htmlspecialchars($base) ?>";

            $btn.prop('disabled', true).find('i').addClass('fa-spin');

            $.ajax({
                url: base + '/hr/tickets/upload-report',
                method: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (res) {
                    if (res && res.success) {
                        $msg.removeClass('d-none').addClass('alert alert-success').text(res.message || 'Uploaded successfully.');
                        $('#uploadForm')[0].reset();
                        setTimeout(function () { window.location.reload(); }, 1000);
                    } else {
                        $msg.removeClass('d-none').addClass('alert alert-danger').text((res && res.message) ? res.message : 'Upload failed.');
                    }
                },
                error: function () {
                    $msg.removeClass('d-none').addClass('alert alert-danger').text('Upload request failed.');
                },
                complete: function () {
                    $btn.prop('disabled', false).find('i').removeClass('fa-spin');
                }
            });
        });

        $(document).on('click', '.rateBtn', function () {
            const ticketId = $(this).data('ticketid');
            const base = "<?= htmlspecialchars($base) ?>";

            $.get(base + '/hr/tickets/rate?id=' + ticketId)
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
