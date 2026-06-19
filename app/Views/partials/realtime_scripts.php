<?php
$realtimeBase = rtrim($base ?? BASE_URL ?? '', '/');
?>
<meta name="base-url" content="<?= htmlspecialchars($realtimeBase) ?>">
<script>window.BASE_URL = <?= json_encode($realtimeBase) ?>;</script>
<script src="<?= htmlspecialchars($realtimeBase) ?>/assets/js/realtime.js" defer></script>
<script src="<?= htmlspecialchars($realtimeBase) ?>/assets/author/ouaaa.js"></script>
<?php require_once __DIR__ . '/dev_livereload.php'; ?>
