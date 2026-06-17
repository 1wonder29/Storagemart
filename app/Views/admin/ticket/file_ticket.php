<?php
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Storage Mart | Admin — File Ticket</title>

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
    require_once __DIR__ . '/../../partials/admin/sidebar_topbar.php';
    ?>
    <div class="container-fluid ticket-create-page">
        <div class="page-hero">
            <h1><i class="fas fa-ticket-alt mr-2"></i>File Ticket</h1>
            <p>Create an administrative ticket record with assignment, technical details, and resolution notes.</p>
        </div>

        <?php require __DIR__ . '/../../partials/ticket/flash_messages.php'; ?>

        <div class="row">
            <div class="col-lg-10 col-xl-9">
                <div class="card shadow mb-4 ticket-form-card">
                    <div class="card-header ticket-header-employee text-white">
                        <h6 class="font-weight-bold text-white"><i class="fas fa-clipboard-list mr-1"></i> Admin Ticket</h6>
                    </div>
                    <div class="card-body">
                        <form action="<?= htmlspecialchars($base) ?>/admin/tickets/file" method="POST">
                            <input type="hidden" name="inventory_id" value="<?= htmlspecialchars($inventory['inventory_id'] ?? '') ?>">
                            <input type="hidden" name="branch_id" value="<?= htmlspecialchars($inventory['branch_id'] ?? '') ?>">

                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-user"></i> Employee Details
                                </div>
                                <div class="row readonly-grid">
                                    <div class="col-md-6">
                                        <label for="employee_id" class="form-label">Employee ID</label>
                                        <input type="text" class="form-control form-control-lg" id="employee_id" name="employee_id"
                                               value="<?= htmlspecialchars($inventory['employee_id'] ?? '') ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fullname" class="form-label">Full Name</label>
                                        <input type="text" class="form-control form-control-lg" id="fullname" name="fullname"
                                               value="<?= htmlspecialchars($inventory['fullname'] ?? '') ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="department" class="form-label">Department</label>
                                        <input type="text" class="form-control form-control-lg" id="department" name="department"
                                               value="<?= htmlspecialchars($inventory['department'] ?? '') ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="branchName" class="form-label">Branch</label>
                                        <input type="text" class="form-control form-control-lg" id="branchName" name="branchName"
                                               value="<?= htmlspecialchars($inventory['branchName'] ?? '') ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-archive"></i> Asset Details
                                </div>
                                <div class="row readonly-grid">
                                    <div class="col-md-6">
                                        <label for="assetNumber" class="form-label">Asset Number</label>
                                        <input type="text" class="form-control form-control-lg" id="assetNumber" name="assetNumber"
                                               value="<?= htmlspecialchars($inventory['assetNumber'] ?? '') ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="groupName" class="form-label">Model / Group</label>
                                        <input type="text" class="form-control form-control-lg" id="groupName" name="groupName"
                                               value="<?= htmlspecialchars($inventory['groupName'] ?? '') ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fas fa-clipboard-list"></i> Ticket Details
                                </div>

                                <div class="priority-legend">
                                    <strong>Priority guide</strong>
                                    <ul class="mb-0">
                                        <li><strong>Low</strong> — Non-urgent; can wait for regular maintenance.</li>
                                        <li><strong>Medium</strong> — Should be addressed within a reasonable timeframe.</li>
                                        <li><strong>High</strong> — Urgent; requires immediate attention.</li>
                                    </ul>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="ticket_assign" class="form-label">
                                            <i class="fas fa-user-cog"></i> Assign To
                                        </label>
                                        <select id="ticket_assign" name="ticket_assign" class="form-control form-control-lg">
                                            <option value="">-- Select Assignee --</option>
                                            <?php foreach ($itStaff as $it): ?>
                                                <option value="<?= (int) $it['employee_id'] ?>">
                                                    <?= htmlspecialchars($it['firstname'] . ' ' . $it['lastname']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="technical_purpose" class="form-label">
                                            <i class="fas fa-tools"></i> Technical Purpose
                                        </label>
                                        <select id="technical_purpose" name="technical_purpose" class="form-control form-control-lg" required>
                                            <option value="">-- Select Purpose --</option>
                                            <option value="CCTV & MAINTAINANCE">CCTV &amp; Maintenance</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="priority" class="form-label">
                                            <i class="fas fa-exclamation-triangle"></i> Priority
                                        </label>
                                        <select id="priority" name="priority" class="form-control form-control-lg" required>
                                            <option value="">-- Select Priority --</option>
                                            <option value="low">Low</option>
                                            <option value="medium">Medium</option>
                                            <option value="high">High</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="category" class="form-label">
                                            <i class="fas fa-tag"></i> Category
                                        </label>
                                        <select id="category" name="category" class="form-control form-control-lg" required>
                                            <option value="">-- Select Category --</option>
                                            <option value="Software,Hardware">Software &amp; Hardware</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="concern_details" class="form-label">
                                            <i class="fas fa-align-left"></i> Concern Details
                                        </label>
                                        <textarea id="concern_details" name="concern_details" class="form-control form-control-lg" rows="5" maxlength="1000" required></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="action" class="form-label">
                                            <i class="fas fa-wrench"></i> Action Taken
                                        </label>
                                        <textarea id="action" name="action" class="form-control form-control-lg" rows="5" maxlength="1000" required></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="result" class="form-label">
                                            <i class="fas fa-check-double"></i> Result Details
                                        </label>
                                        <textarea id="result" name="result" class="form-control form-control-lg" rows="5" maxlength="1000" required></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="remarks" class="form-label">
                                            <i class="fas fa-comment"></i> Remarks
                                        </label>
                                        <textarea id="remarks" name="remarks" class="form-control form-control-lg" rows="5" maxlength="1000" required></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="ticket-form-actions">
                                <button type="submit" class="btn btn-primary" name="btnSubmit">
                                    <i class="fas fa-check"></i> Submit Ticket
                                </button>
                                <a href="<?= htmlspecialchars($base) ?>/admin/tickets" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
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
<script src="<?= htmlspecialchars($base) ?>/assets/js/ticket/file_ticket.js"></script>
</body>
</html>
