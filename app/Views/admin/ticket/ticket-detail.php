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
    <link rel="icon" href="<?= htmlspecialchars($base) ?>/assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= htmlspecialchars($base) ?>/assets/css/storagemart.css" rel="stylesheet">
</head>

<body id="page-top">

<div id="wrapper">
    <?php
    $activePage = 'tickets';
    require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
    ?>
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Ticket Details</h1>
            <a href="<?= htmlspecialchars($base) ?>/admin/tickets" class="btn btn-sm btn-secondary">
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
            <?php
            $priority = (string) ($ticket['priority'] ?? 'Low');
            $priorityClass = $priority === 'High' ? 'danger' : ($priority === 'Medium' ? 'warning' : 'success');
            $status = (string) ($ticket['status'] ?? 'Pending');
            $statusClass = $status === 'Pending' ? 'warning' : ($status === 'In Progress' ? 'info' : ($status === 'Resolved' ? 'success' : 'secondary'));
            ?>
            <div class="row" data-realtime-ticket-detail data-ticket-id="<?= (int) ($ticket['ticket_id'] ?? 0) ?>">
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
                                    <p><strong>Department:</strong> <?php echo htmlspecialchars((string) ($ticket['department'] ?? '')); ?></p>
                                    <p><strong>Priority:</strong>
                                        <span class="badge badge-<?php echo $priorityClass; ?>">
                                            <?php echo htmlspecialchars($priority); ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status:</strong>
                                        <span class="badge badge-<?php echo $statusClass; ?>" data-ticket-status>
                                            <?php echo htmlspecialchars($status); ?>
                                        </span>
                                    </p>
                                    <p><strong>Category:</strong> <?php echo htmlspecialchars((string) ($ticket['category'] ?? '-')); ?></p>
                                    <p><strong>Assigned To:</strong> <?php echo htmlspecialchars((string) ($ticket['assigned_to_name'] ?? 'Unassigned')); ?></p>
                                    <p><strong>Filed Date:</strong> <?php echo htmlspecialchars(date('M d, Y H:i', strtotime((string) ($ticket['date_filed'] ?? '')))); ?></p>
                                    <p><strong>Remarks:</strong> <?php echo htmlspecialchars((string) ($ticket['remarks'] ?? 'No remarks')); ?></p>
                                </div>
                            </div>

                            <hr>

                            <h6 class="font-weight-bold mb-2">Ticket Description</h6>
                            <p><?php echo nl2br(htmlspecialchars((string) ($ticket['concern_details'] ?? ''))); ?></p>
                        </div>
                    </div>

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
                            <?php if (strcasecmp($status, 'resolved') !== 0): ?>
                            <div class="mb-3">
                                <button type="button" class="btn btn-primary btn-block btn-sm openUpdateAssignBtn"
                                    data-ticket-id="<?= (int) ($ticket['ticket_id'] ?? 0) ?>"
                                    data-assignedid="<?= (int) ($ticket['assigned_to'] ?? 0) ?>"
                                    data-status="<?= htmlspecialchars($status) ?>">
                                    <i class="fas fa-edit"></i> Update Assignment
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
                                <a href="<?= htmlspecialchars($base) ?>/admin/tickets" class="btn btn-block btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to Tickets
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-2">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history"></i> Ticket History</h6>
                        </div>
                        <div class="card-body p-0" style="max-height: 320px; overflow-y: auto;">
                            <?php if (empty($history)): ?>
                                <p class="text-muted small mb-0 p-3">No history found.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($history as $entry): ?>
                                        <div class="list-group-item py-2 px-3">
                                            <p class="mb-1 small font-weight-bold text-gray-800">
                                                <?php echo htmlspecialchars((string) ($entry['action_details'] ?? '')); ?>
                                            </p>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars((string) ($entry['assigned_to'] ?? 'System')); ?> &bull;
                                                <?php echo htmlspecialchars(date('M d, Y H:i', strtotime((string) ($entry['date_logged'] ?? '')))); ?>
                                            </small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> Ticket not found.
            </div>
            <a href="<?= htmlspecialchars($base) ?>/admin/tickets" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Tickets
            </a>
        <?php endif; ?>

    </div>
</div>
</div>

<div class="modal fade" id="updateAssignModal" tabindex="-1" role="dialog" aria-labelledby="updateAssignLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/tickets/update-assignment" id="updateAssignForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="updateAssignLabel">Update Ticket Assignment</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="update_ticket_id" value="">
                    <div class="form-group">
                        <label>Assign To (IT Staff)</label>
                        <select class="form-control" name="assigned_to" id="assigned_to_select" required>
                            <option value="">-- Select IT Staff --</option>
                            <?php foreach ($itStaff ?? [] as $it): ?>
                                <option value="<?= (int) $it['employee_id'] ?>">
                                    <?= htmlspecialchars($it['firstname'] . ' ' . $it['lastname']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Remarks (optional)</label>
                        <textarea class="form-control" name="remarks" rows="3" placeholder="Add a short note (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Assignment</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="<?= htmlspecialchars($base) ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/sb-admin-2.min.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/ticket/ticket_comments.js"></script>
<script src="<?= htmlspecialchars($base) ?>/assets/js/edit_ticket_action.js"></script>
<?php require __DIR__ . '/../../partials/ticket/cancel_ticket_modal.php'; ?>

</body>
</html>
