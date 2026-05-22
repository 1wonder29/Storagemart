<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | HR — Ticket Details</title>

    <link href="<?= htmlspecialchars($base) ?>/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/sm_favicon.png" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>

<body id="page-top">
<div id="wrapper">
    <?php
    $activePage = 'tickets';
    require_once __DIR__ . '/../../partials/hr/sidebar_topbar.php';
    ?>

    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Ticket Details</h1>
            <a href="<?= htmlspecialchars($base) ?>/hr/tickets" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back
            </a>
        </div>

        <?php if (empty($ticket)): ?>
            <div class="alert alert-warning">Ticket not found.</div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white">
                                <i class="fas fa-ticket-alt"></i>
                                <?= htmlspecialchars($ticket['ticket_number'] ?? ('#' . ($ticket['ticket_id'] ?? ''))) ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="small text-gray-500 text-uppercase font-weight-bold">Employee</div>
                                    <div class="h6 mb-0">
                                        <?= htmlspecialchars(trim(($ticket['employee_firstname'] ?? '') . ' ' . ($ticket['employee_lastname'] ?? '')) ?: 'Unassigned') ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-gray-500 text-uppercase font-weight-bold">Branch</div>
                                    <div class="h6 mb-0"><?= htmlspecialchars($ticket['branchName'] ?? '-') ?></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="small text-gray-500 text-uppercase font-weight-bold">Status</div>
                                    <div class="h6 mb-0"><?= htmlspecialchars($ticket['status'] ?? '-') ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="small text-gray-500 text-uppercase font-weight-bold">Priority</div>
                                    <div class="h6 mb-0"><?= htmlspecialchars($ticket['priority'] ?? '-') ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="small text-gray-500 text-uppercase font-weight-bold">Filed</div>
                                    <div class="h6 mb-0">
                                        <?= !empty($ticket['date_filed']) ? date('M d, Y', strtotime((string) $ticket['date_filed'])) : '-' ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="small text-gray-500 text-uppercase font-weight-bold">Department</div>
                                <div class="h6 mb-0"><?= htmlspecialchars($ticket['department'] ?? '-') ?></div>
                            </div>

                            <div class="mb-3">
                                <div class="small text-gray-500 text-uppercase font-weight-bold">Category</div>
                                <div class="h6 mb-0"><?= htmlspecialchars($ticket['category'] ?? '-') ?></div>
                            </div>

                            <div class="mb-0">
                                <div class="small text-gray-500 text-uppercase font-weight-bold">Concern</div>
                                <div class="p-3 bg-light rounded border">
                                    <?= nl2br(htmlspecialchars((string) ($ticket['concern_details'] ?? ''))) ?>
                                </div>
                            </div>
                        </div>
                        <?php if (($ticket['status'] ?? '') === 'Resolved'): ?>
                            <div class="card-footer bg-light">
                                <a href="<?= htmlspecialchars($base) ?>/hr/tickets/download-record?id=<?= (int)($ticket['ticket_id'] ?? 0) ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-download"></i> Download Technical Record
                                </a>
                                <button class="btn btn-sm btn-warning rateBtn" data-ticketid="<?= (int)($ticket['ticket_id'] ?? 0) ?>">
                                    <i class="fas fa-star"></i> Rate
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                        <?php if (($ticket['status'] ?? '') === 'Resolved'): ?>
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-success">
                                    <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-file-upload"></i> Technical Report</h6>
                                </div>
                                <div class="card-body">
                                    <div id="uploadMsg" class="alert d-none" role="alert"></div>
                                    <form id="uploadForm" enctype="multipart/form-data">
                                        <input type="hidden" name="ticket_id" value="<?= (int)($ticket['ticket_id'] ?? 0) ?>">
                                        <div class="form-group">
                                            <label class="small text-gray-600 font-weight-bold">Select File (PDF, DOCX, DOC, JPG, PNG - Max 10MB)</label>
                                            <input type="file" class="form-control-file" name="report_file" required accept=".pdf,.docx,.doc,.jpg,.jpeg,.png">
                                        </div>
                                        <button type="submit" class="btn btn-success btn-block" id="uploadBtn">
                                            <i class="fas fa-upload"></i> Upload Report
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-primary">
                            <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-history"></i> History</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($ticketHistory)): ?>
                                <div class="text-muted">No history found.</div>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($ticketHistory as $h): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between">
                                                <strong><?= htmlspecialchars($h['action_type'] ?? 'Updated') ?></strong>
                                                <small class="text-muted">
                                                    <?= !empty($h['date_logged']) ? date('M d, Y H:i', strtotime((string) $h['date_logged'])) : '' ?>
                                                </small>
                                            </div>
                                            <div class="small text-gray-700">
                                                <?= htmlspecialchars($h['action_details'] ?? '') ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>
</div>

<!-- Rate Ticket Modal -->
<div class="modal fade" id="rateTicketModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-star"></i> Rate IT Support
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="rateTicketModalBody">
                <!-- Rating form will be loaded here -->
            </div>
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
    });

    $(document).on('click', '.rateBtn', function () {
        const ticketId = $(this).data('ticketid');
        const base = "<?= htmlspecialchars($base) ?>";
        
        $.get(base + '/hr/tickets/rate?id=' + ticketId)
            .done(function(html) {
                const container = document.getElementById('rateTicketModalBody');
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
    
    $(document).on('submit', '#rateTicketForm', function (e) {
        e.preventDefault();
        const base = "<?= htmlspecialchars($base) ?>";
        const form = $(this);
        
        $.ajax({
            url: base + '/hr/tickets/rate',
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(result) {
                if (result.success) {
                    alert(result.message);
                    $('#rateTicketModal').modal('hide');
                    setTimeout(function() { location.reload(); }, 500);
                } else {
                    alert('Error: ' + result.message);
                }
            },
            error: function() {
                alert('Failed to submit rating. Please try again.');
            }
        });
    });
</script>
</body>
</html>
