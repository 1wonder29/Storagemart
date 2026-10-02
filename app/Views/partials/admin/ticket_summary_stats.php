<?php
/** @var array<string, int> $summaryTicketStats */
/** @var string $base */
/** @var string $summaryActiveStatus */

require_once __DIR__ . '/../it/ticket_view_helpers.php';

$summaryTicketStats = $summaryTicketStats ?? [];
$summaryActiveStatus = $summaryActiveStatus ?? '';
$base = rtrim((string) ($base ?? (defined('BASE_URL') ? BASE_URL : '')), '/');
$summaryLinks = admin_ticket_summary_links($base);

if ($summaryTicketStats === []) {
    return;
}
?>
<div class="workspace-minimal">
    <p class="workspace-eyebrow">Ticket overview</p>
    <div class="personal-stat-grid personal-stat-grid-6">
        <?php foreach ($summaryTicketStats as $status => $count): ?>
            <?php
            $statusLabel = (string) $status;
            $tone = admin_ticket_workspace_tone($statusLabel);
            $icon = admin_ticket_workspace_icon($statusLabel);
            $href = $summaryLinks[$statusLabel] ?? '';
            $isActive = $summaryActiveStatus !== '' && $summaryActiveStatus === $statusLabel;
            ?>
            <a href="<?= htmlspecialchars($href !== '' ? $href : '#') ?>"
               class="personal-stat-card <?= htmlspecialchars($tone) ?><?= $isActive ? ' is-active' : '' ?>"
               <?= $href === '' ? 'aria-disabled="true" onclick="return false;"' : '' ?>
               aria-label="<?= htmlspecialchars($statusLabel) ?> tickets, <?= (int) $count ?>">
                <span class="stat-number"><?= (int) $count ?></span>
                <span class="stat-title"><i class="fas <?= htmlspecialchars($icon) ?>" aria-hidden="true"></i> <?= htmlspecialchars($statusLabel) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
