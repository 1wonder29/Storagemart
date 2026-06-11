<?php
$assignmentHistory = $assignment_history ?? [];
$historyTitle = $history_title ?? 'Branch Assignment History';
?>
<div class="card shadow mb-4">
    <div class="card-header py-2">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-exchange-alt"></i> <?= htmlspecialchars($historyTitle) ?>
        </h6>
    </div>
    <div class="card-body p-0" style="max-height: 280px; overflow-y: auto;">
        <?php if (empty($assignmentHistory)): ?>
            <p class="text-muted small mb-0 p-3">No branch assignment changes recorded yet.</p>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($assignmentHistory as $entry): ?>
                    <div class="list-group-item py-2 px-3">
                        <p class="mb-1 small font-weight-bold text-gray-800">
                            <?= htmlspecialchars((string) ($entry['action'] ?? 'Branch assignment updated')) ?>
                        </p>
                        <small class="text-muted">
                            <?= htmlspecialchars((string) ($entry['performedby'] ?? 'System')) ?> &bull;
                            <?php
                            $loggedAt = (string) ($entry['logged_at'] ?? '');
                            echo $loggedAt !== '' ? htmlspecialchars(date('M d, Y H:i', strtotime($loggedAt))) : '';
                            ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
