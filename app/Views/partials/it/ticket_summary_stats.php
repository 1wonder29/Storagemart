<?php
/** @var array<string, int> $summaryTicketStats */
/** @var string $base */
/** @var string $summaryActiveStatus */

$summaryTicketStats = $summaryTicketStats ?? [];
$summaryActiveStatus = $summaryActiveStatus ?? '';
$summaryLinks = it_ticket_summary_links($base);

if ($summaryTicketStats === []) {
    return;
}
?>
<div class="summary-stats">
    <?php foreach ($summaryTicketStats as $status => $count): ?>
        <?php
        $tone = it_ticket_stat_tone((string) $status);
        $href = $summaryLinks[$status] ?? '';
        $isActive = $summaryActiveStatus !== '' && $summaryActiveStatus === $status;
        ?>
        <?php if ($href !== ''): ?>
            <a href="<?= htmlspecialchars($href) ?>"
               class="summary-stat-card summary-stat-card-link stat-<?= htmlspecialchars($tone) ?><?= $isActive ? ' is-active' : '' ?>">
                <div class="stat-label"><?= htmlspecialchars((string) $status) ?></div>
                <div class="stat-value"><?= (int) $count ?></div>
            </a>
        <?php else: ?>
            <div class="summary-stat-card stat-<?= htmlspecialchars($tone) ?>">
                <div class="stat-label"><?= htmlspecialchars((string) $status) ?></div>
                <div class="stat-value"><?= (int) $count ?></div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
