<?php
/** @var array<string, int> $summaryTicketStats */
/** @var string $base */
/** @var string $summaryActiveStatus */

$summaryTicketStats = $summaryTicketStats ?? [];
$summaryActiveStatus = $summaryActiveStatus ?? '';
$base = rtrim((string) ($base ?? (defined('BASE_URL') ? BASE_URL : '')), '/');
$summaryLinks = it_ticket_summary_links($base);

if ($summaryTicketStats === []) {
    return;
}
?>
<div class="summary-stats">
    <?php foreach ($summaryTicketStats as $status => $count): ?>
        <?php
        $statusLabel = (string) $status;
        $tone = it_ticket_stat_tone($statusLabel);
        $href = $summaryLinks[$statusLabel] ?? '';
        $isActive = $summaryActiveStatus !== '' && $summaryActiveStatus === $statusLabel;
        ?>
        <a href="<?= htmlspecialchars($href !== '' ? $href : '#') ?>"
           class="summary-stat-card summary-stat-card-link stat-<?= htmlspecialchars($tone) ?><?= $isActive ? ' is-active' : '' ?>"
           <?= $href === '' ? 'aria-disabled="true" onclick="return false;"' : '' ?>
           aria-label="<?= htmlspecialchars($statusLabel) ?> tickets, <?= (int) $count ?>">
            <div class="stat-label"><?= htmlspecialchars($statusLabel) ?></div>
            <div class="stat-value"><?= (int) $count ?></div>
        </a>
    <?php endforeach; ?>
</div>
