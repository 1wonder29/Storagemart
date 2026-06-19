<?php
$ticketBackUrl = $ticketBackUrl ?? '#';
$ticketNumber = $ticketNumber ?? ($ticket['ticket_number'] ?? '');
$ticketStatusLabel = $ticketStatusLabel ?? ($ticket['status'] ?? '');
?>
<div class="page-hero ticket-detail-hero">
    <div class="d-flex flex-wrap align-items-center justify-content-between">
        <div>
            <h1><i class="fas fa-ticket-alt mr-2"></i>Ticket Details</h1>
            <?php if ($ticketNumber !== ''): ?>
                <p class="ticket-detail-ref mb-0">
                    <?= htmlspecialchars($ticketNumber) ?>
                    <?php if ($ticketStatusLabel !== ''): ?>
                        <span class="ml-2 opacity-75">· <?= htmlspecialchars($ticketStatusLabel) ?></span>
                    <?php endif; ?>
                </p>
            <?php else: ?>
                <p class="mb-0">View ticket information, history, and actions.</p>
            <?php endif; ?>
        </div>
        <div class="ticket-detail-hero-actions mt-3 mt-md-0">
            <?php if (!empty($ticketDetailHeaderExtra)) {
                echo $ticketDetailHeaderExtra;
            } ?>
            <a href="<?= htmlspecialchars($ticketBackUrl) ?>" class="btn btn-sm btn-back-ticket">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>
</div>
