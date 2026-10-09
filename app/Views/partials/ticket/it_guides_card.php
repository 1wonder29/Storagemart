<?php
/**
 * IT ticket page: troubleshooting guides that match this ticket ("Send in chat"),
 * the requester's "Need help" alert, and for email / Outlook tickets a quick
 * Namecheap / HostGator status check. Expects $ticket and $base.
 */
require_once __DIR__ . '/../../../Helpers/TicketManuals.php';
require_once __DIR__ . '/../../../Helpers/ServiceStatus.php';

global $pdo;
$guideTicketId = (int) ($ticket['ticket_id'] ?? 0);
$guideExtra = [];
try {
    $stmt = $pdo->prepare('SELECT subject, manual_sent_at, help_requested_at FROM tbltickets WHERE ticket_id = ? LIMIT 1');
    $stmt->execute([$guideTicketId]);
    $guideExtra = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $guideExtra = [];
}
$guideTicket = array_merge($ticket, $guideExtra);
$guideMatches = TicketManuals::matchesFor($pdo, (string) ($guideTicket['category'] ?? ''),
    ($guideTicket['subject'] ?? '') . ' ' . ($guideTicket['concern_details'] ?? ''), 3);
$guideIsEmail = ServiceStatus::isEmailTicket($guideTicket);
$guideClosed = in_array((string) ($ticket['status'] ?? ''), ['Resolved', 'Closed', 'Cancelled', 'Decline'], true);
?>
<style>
    .it-guides .guide-item { border:1px solid #e3e6f0; border-radius:10px; padding:10px 12px; margin-bottom:8px; }
    .it-guides .guide-item ol { margin:6px 0 0; padding-left:18px; font-size:.82rem; }
    .it-guides .status-row { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; padding:6px 0; border-bottom:1px dashed #e3e6f0; font-size:.85rem; }
    .it-guides .status-dot { display:inline-block; width:10px; height:10px; border-radius:50%; margin-right:6px; background:#9ca3af; }
    .it-guides .status-dot.ok { background:#10b981; } .it-guides .status-dot.bad { background:#ef4444; }
    html.it-dark .it-guides .guide-item { border-color:#343a4f; }
</style>
<div class="row it-guides mt-2">
    <div class="<?= $guideIsEmail ? 'col-xl-7' : 'col-12' ?>">
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-book-medical mr-1"></i>Troubleshooting guides for this ticket</h6>
                <a href="<?= htmlspecialchars($base) ?>/it/manuals" class="small">Manage guides</a>
            </div>
            <div class="card-body">
                <?php if (!empty($guideTicket['help_requested_at'])): ?>
                    <div class="alert alert-danger py-2"><i class="fas fa-life-ring mr-1"></i>
                        The requester tried the guide and asked for IT help on
                        <?= htmlspecialchars(date('M j, g:i A', strtotime((string) $guideTicket['help_requested_at']))) ?>.</div>
                <?php elseif (!empty($guideTicket['manual_sent_at'])): ?>
                    <div class="small text-muted mb-2"><i class="fas fa-check mr-1"></i>The first guide below was sent in the ticket chat
                        <?= htmlspecialchars(date('M j, g:i A', strtotime((string) $guideTicket['manual_sent_at']))) ?>.</div>
                <?php endif; ?>
                <?php foreach ($guideMatches as $guide):
                    $guideText = $guide['title'] . ':';
                    foreach ($guide['steps'] as $i => $step) {
                        $guideText .= "\n" . ($i + 1) . '. ' . $step;
                    }
                    $guideText .= "\nSlides: " . $guide['viewer'];
                ?>
                    <div class="guide-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?= htmlspecialchars($guide['title']) ?></strong>
                                <?php if ($guide['description'] !== ''): ?>
                                    <div class="small text-muted"><?= htmlspecialchars($guide['description']) ?></div>
                                <?php endif; ?>
                            </div>
                            <?php if (!$guideClosed): ?>
                                <button type="button" class="btn btn-sm btn-outline-primary js-send-guide text-nowrap"
                                        data-text="<?= htmlspecialchars($guideText, ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="fas fa-paper-plane mr-1"></i>Send in chat
                                </button>
                            <?php endif; ?>
                        </div>
                        <?php if ($guide['steps']): ?>
                            <ol><?php foreach ($guide['steps'] as $step): ?><li><?= htmlspecialchars($step) ?></li><?php endforeach; ?></ol>
                        <?php endif; ?>
                        <a class="small" href="<?= htmlspecialchars($guide['viewer']) ?>" target="_blank" rel="noopener">
                            <i class="fas fa-desktop mr-1"></i>View slides</a>
                        <?php if ($guide['manual_id'] > 0): ?>
                            · <a class="small" href="<?= htmlspecialchars($base) ?>/manuals/pptx?id=<?= (int) $guide['manual_id'] ?>">
                                <i class="fas fa-file-powerpoint mr-1"></i>PowerPoint</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php if ($guideIsEmail): ?>
        <div class="col-xl-5">
            <div class="card shadow mb-4" id="serviceStatusCard">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-heartbeat mr-1"></i>Email service check</h6>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="serviceStatusRefresh">
                        <i class="fas fa-sync-alt mr-1"></i>Check again</button>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Email ticket: checks the domain DNS, the mail server Outlook connects to (Namecheap Private Email) and the websites (HostGator) before you troubleshoot the PC.</p>
                    <div id="serviceStatusRows"><div class="small text-muted"><i class="fas fa-spinner fa-spin mr-1"></i>Checking...</div></div>
                    <div class="small mt-2">
                        Official pages:
                        <a href="<?= htmlspecialchars(ServiceStatus::NAMECHEAP_STATUS_URL) ?>" target="_blank" rel="noopener noreferrer">Namecheap status</a> ·
                        <a href="<?= htmlspecialchars(ServiceStatus::HOSTGATOR_STATUS_URL) ?>" target="_blank" rel="noopener noreferrer">HostGator status</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<script>
(function () {
    var base = <?= json_encode($base) ?>;
    var ticketId = <?= $guideTicketId ?>;
    document.querySelectorAll('.js-send-guide').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.disabled = true;
            var form = new FormData();
            form.append('ticket_id', String(ticketId));
            form.append('comment', btn.getAttribute('data-text'));
            fetch(base + '/ticket-comments/add', { method: 'POST', body: form, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    btn.innerHTML = res.success ? '<i class="fas fa-check mr-1"></i>Sent' : 'Not sent';
                    if (!res.success) { btn.disabled = false; alert(res.message || 'Could not send the guide.'); }
                })
                .catch(function () { btn.disabled = false; alert('Connection problem. Please try again.'); });
        });
    });

    var rows = document.getElementById('serviceStatusRows');
    if (!rows) return;
    function render(data) {
        rows.textContent = '';
        if (!data.success) { rows.textContent = 'Could not run the check.'; return; }
        data.checks.forEach(function (c) {
            var row = document.createElement('div');
            row.className = 'status-row';
            var left = document.createElement('div');
            var dot = document.createElement('span');
            dot.className = 'status-dot ' + (c.ok === true ? 'ok' : (c.ok === false ? 'bad' : ''));
            left.appendChild(dot);
            var name = document.createElement('strong');
            name.textContent = c.name;
            left.appendChild(name);
            var detail = document.createElement('div');
            detail.className = 'small text-muted';
            detail.textContent = c.provider + ': ' + c.detail;
            left.appendChild(detail);
            var right = document.createElement('span');
            right.className = 'small text-muted text-nowrap';
            right.textContent = c.ms + ' ms';
            row.appendChild(left);
            row.appendChild(right);
            rows.appendChild(row);
        });
        var summary = document.createElement('div');
        summary.className = 'small mt-2 font-weight-bold ' + (data.overall === 'ok' ? 'text-success' : 'text-danger');
        summary.textContent = data.overall === 'ok'
            ? 'All services answered. The problem is likely on the user\'s PC or Outlook settings.'
            : 'Some services did not answer. Check the official status pages before troubleshooting the PC.';
        rows.appendChild(summary);
    }
    function check(refresh) {
        rows.innerHTML = '<div class="small text-muted"><i class="fas fa-spinner fa-spin mr-1"></i>Checking...</div>';
        fetch(base + '/it/service-status' + (refresh ? '?refresh=1' : ''), { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(render)
            .catch(function () { rows.textContent = 'Could not run the check.'; });
    }
    document.getElementById('serviceStatusRefresh').addEventListener('click', function () { check(true); });
    check(false);
})();
</script>
