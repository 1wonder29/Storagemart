<?php
/**
 * Confirm / Item OK buttons for one lost-damaged report row.
 * Expected: $r (a row from UniformReportModel::getReports()).
 * Pair with item_report_review_modal.php once per page.
 */
$reportItemLabel = trim($r['uniform_type'] . ' (' . $r['size'] . ')');
$reportWhat = strtolower((string) $r['report_type']);
?>
<?php if (($r['status'] ?? '') === 'PENDING'): ?>
    <button type="button" class="btn btn-sm btn-danger btn-resolve"
            data-report-id="<?= (int) $r['report_id'] ?>" data-decision="CONFIRMED"
            data-item-type="<?= htmlspecialchars((string) $r['uniform_type']) ?>"
            data-uniform-id="<?= (int) $r['uniform_id'] ?>"
            data-quantity="<?= (int) $r['quantity'] ?>"
            data-title="Confirm <?= htmlspecialchars($reportWhat) ?> report"
            data-text="<?= (int) $r['quantity'] ?> x <?= htmlspecialchars($reportItemLabel) ?> will be recorded as <?= htmlspecialchars($reportWhat) ?> and removed from <?= htmlspecialchars((string) $r['employee_name']) ?>'s issued items.">
        <i class="fas fa-check"></i> Confirm
    </button>
    <button type="button" class="btn btn-sm btn-success btn-resolve"
            data-report-id="<?= (int) $r['report_id'] ?>" data-decision="ITEM_OK"
            data-title="Mark item OK"
            data-text="The item stays active with <?= htmlspecialchars((string) $r['employee_name']) ?> and inventory is not changed.">
        <i class="fas fa-thumbs-up"></i> Item OK
    </button>
<?php else: ?>
    <span class="text-muted small">
        <?= !empty($r['reviewed_at']) ? htmlspecialchars(date('M j, Y', strtotime((string) $r['reviewed_at']))) : '' ?>
    </span>
<?php endif; ?>
