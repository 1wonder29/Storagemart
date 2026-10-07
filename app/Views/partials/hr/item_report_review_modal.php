<?php
/**
 * Review dialog for lost/damaged reports (HR reports page and HR employee profile).
 * Expected: $base, $csrf_token, $replacementStock (UniformReportModel::getReplacementStock()).
 * Optional: $reviewReturnTo — path to come back to after saving (e.g. /hr/employees/detail/123).
 * Include after jQuery/Bootstrap are loaded.
 */
$replacementStock = $replacementStock ?? [];
$reviewReturnTo = $reviewReturnTo ?? '';
?>
<div class="modal fade" id="resolveReportModal" tabindex="-1" role="dialog" aria-labelledby="resolveReportTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="<?= htmlspecialchars($base) ?>/hr/uniforms/reports/resolve">
                <div class="modal-header">
                    <h5 class="modal-title" id="resolveReportTitle"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                    <input type="hidden" name="report_id" id="resolveReportId">
                    <input type="hidden" name="decision" id="resolveDecision">
                    <?php if ($reviewReturnTo !== ''): ?>
                        <input type="hidden" name="return_to" value="<?= htmlspecialchars($reviewReturnTo) ?>">
                    <?php endif; ?>
                    <p id="resolveReportText"></p>

                    <div id="replacementSection" class="border rounded p-3 mb-3 d-none">
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="issueReplacement">
                            <label class="custom-control-label font-weight-bold" for="issueReplacement">Issue a replacement now</label>
                        </div>
                        <div id="replacementFields" class="d-none">
                            <?php if (empty($replacementStock)): ?>
                                <p class="small text-muted mb-0">No active items are in stock. Issue a replacement later from Assign Item.</p>
                            <?php else: ?>
                                <div class="form-group">
                                    <label for="replacementUniformId" class="small font-weight-bold">Item</label>
                                    <select class="form-control form-control-sm" id="replacementUniformId">
                                        <?php foreach ($replacementStock as $stock): ?>
                                            <option value="<?= (int) $stock['uniform_id'] ?>"
                                                    data-type="<?= htmlspecialchars((string) $stock['uniform_type']) ?>"
                                                    data-stock="<?= (int) $stock['quantity_in_stock'] ?>">
                                                <?= htmlspecialchars($stock['uniform_type'] . ' (' . $stock['size'] . ')') ?>
                                                — <?= (int) $stock['quantity_in_stock'] ?> in stock
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">Items of the same type are listed first.</small>
                                </div>
                                <div class="form-group mb-0">
                                    <label for="replacementQuantity" class="small font-weight-bold">Quantity</label>
                                    <input type="number" class="form-control form-control-sm" id="replacementQuantity" min="1" value="1">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="resolveRemarks" class="font-weight-bold">Remarks for the employee (optional)</label>
                        <textarea class="form-control" id="resolveRemarks" name="hr_remarks" rows="3" maxlength="500"></textarea>
                        <small class="form-text text-muted">The employee gets an in-app notification and an email.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="resolveSubmit">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    (function ($) {
        var $select = $('#replacementUniformId');
        var originalOptions = $select.find('option').toArray();

        function setReplacementEnabled(on) {
            $('#replacementFields').toggleClass('d-none', !on);
            // Only submit replacement fields when the box is ticked.
            $select.attr('name', on ? 'replacement_uniform_id' : null);
            $('#replacementQuantity').attr('name', on ? 'replacement_quantity' : null);
        }

        $('#issueReplacement').on('change', function () {
            setReplacementEnabled(this.checked);
        });

        $select.on('change', function () {
            var stock = parseInt($select.find('option:selected').data('stock'), 10) || 1;
            $('#replacementQuantity').attr('max', stock);
        });

        $(document).on('click', '.btn-resolve', function () {
            var $b = $(this);
            var confirmDecision = $b.data('decision') === 'CONFIRMED';
            $('#resolveReportId').val($b.data('report-id'));
            $('#resolveDecision').val($b.data('decision'));
            $('#resolveReportTitle').text($b.data('title'));
            $('#resolveReportText').text($b.data('text'));
            $('#resolveRemarks').val('');

            $('#replacementSection').toggleClass('d-none', !confirmDecision);
            $('#issueReplacement').prop('checked', false);
            setReplacementEnabled(false);
            if (confirmDecision && originalOptions.length) {
                // Same item type first, the reported item itself on top when it is still in stock.
                var type = String($b.data('item-type') || '');
                var uniformId = String($b.data('uniform-id') || '');
                var sorted = originalOptions.slice().sort(function (a, b) {
                    var rank = function (o) {
                        return o.value === uniformId ? 0 : ($(o).data('type') === type ? 1 : 2);
                    };
                    return rank(a) - rank(b);
                });
                $select.empty().append(sorted).prop('selectedIndex', 0).trigger('change');
                var stock = parseInt($select.find('option:selected').data('stock'), 10) || 1;
                $('#replacementQuantity').val(Math.min(parseInt($b.data('quantity'), 10) || 1, stock));
            }

            $('#resolveSubmit').toggleClass('btn-danger', confirmDecision).toggleClass('btn-success', !confirmDecision)
                .text(confirmDecision ? 'Confirm Report' : 'Mark Item OK');
            $('#resolveReportModal').modal('show');
        });
    })(jQuery);
</script>
