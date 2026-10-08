<?php
$realtimeBase = rtrim($base ?? BASE_URL ?? '', '/');
?>
<meta name="base-url" content="<?= htmlspecialchars($realtimeBase) ?>">
<script>window.BASE_URL = <?= json_encode($realtimeBase) ?>;</script>
<script src="<?= htmlspecialchars($realtimeBase) ?>/assets/js/realtime.js" defer></script>
<script src="<?= htmlspecialchars($realtimeBase) ?>/assets/author/ouaaa.js"></script>
<?php if (!empty($_SESSION['account_id'])): ?>
<link href="<?= htmlspecialchars($realtimeBase) ?>/assets/css/ticket-chat.css?v=20261008" rel="stylesheet">
<script src="<?= htmlspecialchars($realtimeBase) ?>/assets/js/ticket-chat.js?v=20261008" defer></script>
<?php endif; ?>
<?php require_once __DIR__ . '/dev_livereload.php'; ?>
