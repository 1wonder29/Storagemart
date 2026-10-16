<?php
/**
 * Confirm / Item OK buttons for one lost-damaged report row.
 * Expected: $r (a row from UniformReportModel::getReports()).
 * Pair with item_report_review_modal.php once per page.
 */
$reportItemLabel = trim($r['uniform_type'] . ' (' . $r['size'] . ')');
$reportWhat = strtolower((string) $r['report_type']);
$reportFromReturn = ($r['source'] ?? '') === 'HR_RETURN' && !empty($r['return_id']);
// Confirming writes off at most what is still on the issuance (a held return is already counted in full).
$reportQty = $reportFromReturn
    ? (int) $r['quantity']
    : min((int) $r['quantity'], max(0, (int) ($r['quantity_issued'] ?? $r['quantity'])));
$reportOtherType = $r['report_type'] === 'LOST' ? 'DAMAGED' : 'LOST';
$reportCanSwitch = ($r['status'] ?? '') === 'PENDING' || (($r['status'] ?? '') === 'CONFIRMED' && !empty($r['return_id']));
?>
<?php if (($r['status'] ?? '') === 'PENDING'): ?>
    <button type="button" class="btn btn-sm btn-danger btn-resolve"
            data-report-id="<?= (int) $r['report_id'] ?>" data-decision="CONFIRMED"
            data-item-type="<?= htmlspecialchars((string) $r['uniform_type']) ?>"
            data-uniform-id="<?= (int) $r['uniform_id'] ?>"
            data-quantity="<?= $reportQty ?>"
            data-title="Confirm <?= htmlspecialchars($reportWhat) ?> report"
            data-text="<?= $reportQty ?> x <?= htmlspecialchars($reportItemLabel) ?> will be recorded as <?= htmlspecialchars($reportWhat) ?><?= $reportFromReturn ? '' : ' and removed from ' . htmlspecialchars((string) $r['employee_name']) . '\'s issued items' ?>.">
        <i class="fas fa-check"></i> Confirm
    </button>
    <button type="button" class="btn btn-sm btn-success btn-resolve"
            data-report-id="<?= (int) $r['report_id'] ?>" data-decision="ITEM_OK"
            data-title="Mark item OK"
            data-text="<?= $reportFromReturn
                ? 'The returned item is fine: ' . $reportQty . ' x ' . htmlspecialchars($reportItemLabel) . ' go back to stock as good.'
                : 'The item stays active with ' . htmlspecialchars((string) $r['employee_name']) . ' and inventory is not changed.' ?>">
        <i class="fas fa-thumbs-up"></i> Item OK
    </button>
<?php else: ?>
    <span class="text-muted small">
        <?= !empty($r['reviewed_at']) ? htmlspecialchars(date('M j, Y', strtotime((string) $r['reviewed_at']))) : '' ?>
    </span>
<?php endif; ?>
<?php if ($reportCanSwitch): ?>
    <form method="POST" action="<?= htmlspecialchars($base) ?>/hr/uniforms/reports/change-type" class="d-inline"
          data-confirm="Change this report to <?= strtolower($reportOtherType) ?>?">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? ($_SESSION['csrf_token'] ?? '')) ?>">
        <input type="hidden" name="report_id" value="<?= (int) $r['report_id'] ?>">
        <input type="hidden" name="report_type" value="<?= $reportOtherType ?>">
        <?php if (!empty($reviewReturnTo)): ?><input type="hidden" name="return_to" value="<?= htmlspecialchars($reviewReturnTo) ?>"><?php endif; ?>
        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Recorded the wrong one? Switch it.">
            <i class="fas fa-exchange-alt"></i> Mark <?= strtolower($reportOtherType) ?>
        </button>
    </form>
<?php endif; ?>
