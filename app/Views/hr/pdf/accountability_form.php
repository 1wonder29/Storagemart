<?php
/**
 * HR Accountability Form Template for PDF
 * This template generates a printable accountability form for an employee
 * showing all assigned assets and uniforms
 */
$base = rtrim(BASE_URL, '/');
$employee = $employee ?? [];
$assets = $assets ?? [];
$uniforms = $uniforms ?? [];
$currentDate = date('M d, Y');
$currentTime = date('H:i');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Accountability Form - <?= htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        .page {
            width: 8.5in;
            height: 11in;
            padding: 0.5in;
            margin: 0 auto;
            border: 1px solid #ddd;
            background: white;
            page-break-after: always;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .section {
            margin-bottom: 12px;
        }
        .section-title {
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 5px;
            margin-bottom: 5px;
            border-left: 3px solid #0066cc;
        }
        .info-row {
            display: flex;
            margin-bottom: 4px;
        }
        .label {
            font-weight: bold;
            width: 120px;
            flex-shrink: 0;
        }
        .value {
            flex: 1;
            padding-left: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
        }
        th {
            background-color: #0066cc;
            color: white;
            padding: 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #0066cc;
        }
        td {
            padding: 4px;
            border: 1px solid #999;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .signature-section {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 30px;
            padding-top: 5px;
            text-align: center;
            font-size: 9px;
        }
        .footer {
            text-align: center;
            font-size: 9px;
            color: #666;
            margin-top: 20px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        .no-data {
            font-style: italic;
            color: #999;
            padding: 5px;
        }
    </style>
</head>
<body>

<div class="page">
    <!-- Header -->
    <div class="header">
        <div class="company-name">STORAGEMART INC.</div>
        <div class="form-title">EMPLOYEE ACCOUNTABILITY FORM</div>
    </div>

    <!-- Employee Information -->
    <div class="section">
        <div class="section-title">EMPLOYEE INFORMATION</div>
        <div class="info-row">
            <div class="label">Employee ID:</div>
            <div class="value"><?= htmlspecialchars($employee['employee_id'] ?? '') ?></div>
        </div>
        <div class="info-row">
            <div class="label">Name:</div>
            <div class="value"><?= htmlspecialchars($employee['firstname'] . ' ' . $employee['lastname'] . ' ' . ($employee['middlename'] ?? '')) ?></div>
        </div>
        <div class="info-row">
            <div class="label">Position:</div>
            <div class="value"><?= htmlspecialchars($employee['position'] ?? '') ?></div>
        </div>
        <div class="info-row">
            <div class="label">Department:</div>
            <div class="value"><?= htmlspecialchars($employee['department'] ?? '') ?></div>
        </div>
        <div class="info-row">
            <div class="label">Branch:</div>
            <div class="value"><?= htmlspecialchars($employee['branchName'] ?? '') ?></div>
        </div>
        <div class="info-row">
            <div class="label">Email:</div>
            <div class="value"><?= htmlspecialchars($employee['email'] ?? '') ?></div>
        </div>
    </div>

    <!-- IT Assets Section -->
    <div class="section">
        <div class="section-title">IT ASSETS ASSIGNED</div>
        <?php if (empty($assets)): ?>
            <div class="no-data">No IT assets assigned.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th style="width: 20%;">Asset #</th>
                        <th style="width: 20%;">Description</th>
                        <th style="width: 15%;">Serial #</th>
                        <th style="width: 20%;">Category</th>
                        <th style="width: 15%;">Issued Date</th>
                        <th style="width: 10%;">Condition</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assets as $asset): ?>
                        <tr>
                            <td><?= htmlspecialchars($asset['assetNumber'] ?? '') ?></td>
                            <td><?= htmlspecialchars($asset['itemInfo'] ?? '') ?></td>
                            <td><?= htmlspecialchars($asset['serialNumber'] ?? '') ?></td>
                            <td><?= htmlspecialchars($asset['categoryName'] ?? '') ?></td>
                            <td><?= $asset['dateIssued'] ? date('m/d/Y', strtotime($asset['dateIssued'])) : '-' ?></td>
                            <td><?= htmlspecialchars($asset['asset_status'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Uniforms Section -->
    <div class="section">
        <div class="section-title">UNIFORMS ASSIGNED</div>
        <?php if (empty($uniforms)): ?>
            <div class="no-data">No uniforms assigned.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th style="width: 25%;">Uniform Type</th>
                        <th style="width: 12%;">Size</th>
                        <th style="width: 15%;">Color</th>
                        <th style="width: 12%;">Qty</th>
                        <th style="width: 16%;">Issued Date</th>
                        <th style="width: 20%;">Condition</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($uniforms as $uniform): ?>
                        <tr>
                            <td><?= htmlspecialchars($uniform['uniform_type'] ?? '') ?></td>
                            <td><?= htmlspecialchars($uniform['size'] ?? '') ?></td>
                            <td><?= htmlspecialchars($uniform['color'] ?? '') ?></td>
                            <td><?= (int)($uniform['quantity_issued'] ?? 1) ?></td>
                            <td><?= date('m/d/Y', strtotime($uniform['date_issued'])) ?></td>
                            <td><?= htmlspecialchars($uniform['condition_upon_issue'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div>Employee Signature:</div>
            <div class="signature-line"></div>
        </div>
        <div class="signature-box">
            <div>HR Authorized Signature:</div>
            <div class="signature-line"></div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Generated on: <?= $currentDate ?> at <?= $currentTime ?></p>
        <p>This form certifies that the above employee has received and will be accountable for the assigned assets and uniforms.</p>
    </div>
</div>

</body>
</html>
