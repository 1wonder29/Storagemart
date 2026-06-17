<?php
/** @var array $tickets */
/** @var string $base */
/** @var int $employeeId */

require_once __DIR__ . '/../../partials/it/ticket_view_helpers.php';

foreach ($tickets as $row):
    $ticketId = (int) ($row['ticket_id'] ?? 0);
    $isAssignedToMe = (
        $row['assigned_to'] !== null &&
        (int) $row['assigned_to'] === (int) $employeeId
    );
    $status = (string) ($row['status'] ?? '');
    $priority = (string) ($row['priority'] ?? '');
    $date = it_ticket_format_date((string) ($row['date_filed'] ?? ''));
?>
<tr data-ticket-id="<?= $ticketId ?>"
    data-branch="<?= htmlspecialchars(strtolower(trim((string) ($row['branchName'] ?? '')))) ?>"
    data-priority="<?= htmlspecialchars(strtolower(trim($priority))) ?>"
    data-status="<?= htmlspecialchars(strtolower(trim($status))) ?>">
    <td>
        <div class="ticket-id-wrap">
            <span class="ticket-id"><?= htmlspecialchars($row['ticket_number']) ?></span>
        </div>
    </td>
    <td>
        <div class="employee-name"><?= htmlspecialchars($row['employee_name'] ?? '') ?></div>
        <?php if (!empty($row['branchName'])): ?>
            <span class="branch-pill">
                <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($row['branchName']) ?>
            </span>
        <?php endif; ?>
    </td>
    <td>
        <?php if (!empty($row['category'])): ?>
            <span class="category-pill" title="<?= htmlspecialchars($row['category']) ?>">
                <?= htmlspecialchars($row['category']) ?>
            </span>
        <?php endif; ?>
        <?php if (!empty($row['concern_details'])): ?>
            <div class="concern-text" title="<?= htmlspecialchars($row['concern_details']) ?>">
                <?= htmlspecialchars(it_ticket_truncate((string) $row['concern_details'], 80)) ?>
            </div>
        <?php endif; ?>
        <?php
        $asset = (string) ($row['asset_info'] ?? '');
        if ($asset !== '' && $asset !== 'N/A - General'):
        ?>
            <div class="asset-hint" title="<?= htmlspecialchars($asset) ?>">
                <i class="fas fa-laptop mr-1"></i><?= htmlspecialchars($asset) ?>
            </div>
        <?php endif; ?>
    </td>
    <td>
        <?php if ($priority !== ''): ?>
            <span class="priority-pill <?= it_ticket_priority_class($priority) ?>" data-ticket-priority>
                <i class="fas fa-flag"></i> <?= htmlspecialchars($priority) ?>
            </span>
        <?php else: ?>
            <span class="text-muted">—</span>
        <?php endif; ?>
    </td>
    <td>
        <?php if ($status !== ''): ?>
            <span class="status-badge <?= it_ticket_status_class($status) ?>" data-ticket-status>
                <?= htmlspecialchars($status) ?>
            </span>
        <?php else: ?>
            <span class="text-muted">—</span>
        <?php endif; ?>
    </td>
    <td>
        <div class="employee-name"><?= htmlspecialchars($row['assigned_to_name'] ?? 'Unassigned') ?></div>
        <?php if ($isAssignedToMe): ?>
            <span class="mine-badge"><i class="fas fa-user-check mr-1"></i>Assigned to you</span>
        <?php endif; ?>
        <?php if (!empty($row['remarks'])): ?>
            <div class="remarks-hint" title="<?= htmlspecialchars($row['remarks']) ?>">
                <?= htmlspecialchars(it_ticket_truncate((string) $row['remarks'], 50)) ?>
            </div>
        <?php endif; ?>
    </td>
    <td class="date-cell" data-order="<?= (int) $date['order'] ?>">
        <div class="date-main"><?= htmlspecialchars($date['main']) ?></div>
        <?php if ($date['time'] !== ''): ?>
            <div class="date-time"><?= htmlspecialchars($date['time']) ?></div>
        <?php endif; ?>
    </td>
    <td class="text-right">
        <?php if ($isAssignedToMe): ?>
            <div class="action-btn-group">
                <a href="<?= htmlspecialchars($base) ?>/it/tickets/view?id=<?= $ticketId ?>&from=in_progress"
                   class="btn btn-sm btn-outline-primary" title="View full detail">
                    <i class="fas fa-eye"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-info viewTicketBtn"
                    title="View &amp; comments"
                    data-ticket-id="<?= $ticketId ?>"
                    data-ticket-num="<?= htmlspecialchars($row['ticket_number']) ?>"
                    data-employee="<?= htmlspecialchars($row['employee_name'] ?? '') ?>"
                    data-priority="<?= htmlspecialchars($priority) ?>"
                    data-status="<?= htmlspecialchars($status) ?>"
                    data-concern="<?= htmlspecialchars($row['concern_details'] ?? '') ?>">
                    <i class="fas fa-comments"></i>
                </button>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Update ticket">
                        <i class="fas fa-cog"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right shadow">
                        <h6 class="dropdown-header">Update Status</h6>
                        <a href="#" class="dropdown-item openModalBtn" data-action="Resolve"
                            data-ticket-id="<?= $ticketId ?>"
                            data-ticket-num="<?= htmlspecialchars($row['ticket_number'] ?? '') ?>"
                            data-employee="<?= htmlspecialchars($row['employee_name'] ?? '') ?>"
                            data-branch="<?= htmlspecialchars($row['branchName'] ?? '') ?>"
                            data-priority="<?= htmlspecialchars($priority) ?>"
                            data-status="<?= htmlspecialchars($status) ?>"
                            data-category="<?= htmlspecialchars($row['category'] ?? '') ?>"
                            data-department="<?= htmlspecialchars($row['department'] ?? '') ?>"
                            data-concern="<?= htmlspecialchars($row['concern_details'] ?? '') ?>"
                            data-filed="<?= !empty($row['date_filed']) ? date('M d, Y', strtotime((string) $row['date_filed'])) : '' ?>"
                            data-assigned="<?= $row['assigned_to'] ?>">
                            <i class="fas fa-check fa-sm fa-fw mr-2 text-success"></i> Resolved
                        </a>
                        <a href="#" class="dropdown-item openModalBtn" data-action="Pending"
                            data-ticket-id="<?= $ticketId ?>"
                            data-ticket-num="<?= htmlspecialchars($row['ticket_number'] ?? '') ?>"
                            data-employee="<?= htmlspecialchars($row['employee_name'] ?? '') ?>"
                            data-branch="<?= htmlspecialchars($row['branchName'] ?? '') ?>"
                            data-priority="<?= htmlspecialchars($priority) ?>"
                            data-status="<?= htmlspecialchars($status) ?>"
                            data-category="<?= htmlspecialchars($row['category'] ?? '') ?>"
                            data-department="<?= htmlspecialchars($row['department'] ?? '') ?>"
                            data-concern="<?= htmlspecialchars($row['concern_details'] ?? '') ?>"
                            data-filed="<?= !empty($row['date_filed']) ? date('M d, Y', strtotime((string) $row['date_filed'])) : '' ?>"
                            data-assigned="<?= $row['assigned_to'] ?>">
                            <i class="fas fa-clock fa-sm fa-fw mr-2 text-warning"></i> Pending
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item cancelTicketBtn"
                            data-ticket-id="<?= $ticketId ?>"
                            data-ticket-num="<?= htmlspecialchars($row['ticket_number'] ?? '') ?>">
                            <i class="fas fa-ban fa-sm fa-fw mr-2 text-secondary"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <span class="not-assigned-label">Not assigned to you</span>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
