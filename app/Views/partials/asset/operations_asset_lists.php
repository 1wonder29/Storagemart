<?php
$myAssets = $myAssets ?? [];
$teamAssets = $teamAssets ?? [];
$branches = $branches ?? [];
$teamEmptyMessage = $teamEmptyMessage ?? 'No employee assets found.';

$myAssetCount = count($myAssets);
$teamAssetCount = count($teamAssets);
$teamEmployeeIds = [];
foreach ($teamAssets as $row) {
    $eid = (int) ($row['employee_id'] ?? 0);
    if ($eid > 0) {
        $teamEmployeeIds[$eid] = true;
    }
}

$renderAssetStatus = static function (array $row): string {
    $status = (string) ($row['status'] ?? 'Active');
    $statusClass = strtoupper($status) === 'ACTIVE' ? 'success' : 'secondary';
    return '<span class="badge badge-' . $statusClass . '">' . htmlspecialchars($status) . '</span>';
};
?>

<div class="card data-list-card shadow mb-4">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-user mr-1"></i> My Assets
        </h6>
        <span class="badge badge-info" style="border-radius:2rem;padding:0.4rem 0.75rem;">
            <?= (int) $myAssetCount ?> asset<?= $myAssetCount === 1 ? '' : 's' ?>
        </span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($myAssets)): ?>
            <div class="empty-state text-center py-5 text-muted">
                <i class="fas fa-archive fa-2x mb-3 d-block"></i>
                No assets are currently assigned to you.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="my-asset-table" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Asset Number</th>
                        <th>Model</th>
                        <th>Description</th>
                        <th>Serial Number</th>
                        <th>Item Info</th>
                        <th>Status</th>
                        <th>Branch</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($myAssets as $row): ?>
                        <tr>
                            <td class="font-weight-bold"><?= htmlspecialchars((string) ($row['assetNumber'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string) ($row['groupName'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string) ($row['description'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string) ($row['serialNumber'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string) ($row['itemInfo'] ?? '')) ?></td>
                            <td><?= $renderAssetStatus($row) ?></td>
                            <td><?= htmlspecialchars((string) ($row['branchName'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="filter-toolbar">
    <div class="row align-items-end">
        <div class="col-md-6 col-sm-6 mb-2 mb-md-0">
            <label for="assetBranchFilter">Branch</label>
            <select id="assetBranchFilter" class="form-control form-control-sm">
                <option value="">All Branches</option>
                <?php foreach ($branches as $branch): ?>
                    <option value="<?= (int) ($branch['branch_id'] ?? 0) ?>">
                        <?= htmlspecialchars($branch['branchName'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6 col-sm-6 text-md-right">
            <button type="button" id="assetClearFilters" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-undo mr-1"></i> Clear Filters
            </button>
        </div>
    </div>
</div>

<div class="card data-list-card shadow mb-4">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-users mr-1"></i> Employee Assets
        </h6>
        <span class="badge badge-primary" style="border-radius:2rem;padding:0.4rem 0.75rem;">
            <?= (int) $teamAssetCount ?> asset<?= $teamAssetCount === 1 ? '' : 's' ?>
            &middot; <?= count($teamEmployeeIds) ?> employee<?= count($teamEmployeeIds) === 1 ? '' : 's' ?>
        </span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($teamAssets)): ?>
            <div class="empty-state text-center py-5 text-muted">
                <i class="fas fa-boxes fa-2x mb-3 d-block"></i>
                <?= htmlspecialchars($teamEmptyMessage) ?>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="team-asset-table" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Asset Number</th>
                        <th>Model</th>
                        <th>Description</th>
                        <th>Serial Number</th>
                        <th>Item Info</th>
                        <th>Status</th>
                        <th>Employee</th>
                        <th>Branch</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teamAssets as $row): ?>
                        <?php
                        $employeeName = trim(($row['lastname'] ?? '') . ', ' . ($row['firstname'] ?? ''), ', ');
                        ?>
                        <tr data-branch-id="<?= (int) ($row['branch_id'] ?? 0) ?>">
                            <td class="font-weight-bold"><?= htmlspecialchars((string) ($row['assetNumber'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string) ($row['groupName'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string) ($row['description'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string) ($row['serialNumber'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string) ($row['itemInfo'] ?? '')) ?></td>
                            <td><?= $renderAssetStatus($row) ?></td>
                            <td><?= htmlspecialchars($employeeName) ?></td>
                            <td><?= htmlspecialchars((string) ($row['branchName'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
