<?php
if ((getenv('APP_ENV') ?: '') !== 'development') {
    return;
}

$devBase = rtrim($base ?? $realtimeBase ?? (defined('BASE_URL') ? BASE_URL : '') ?? '', '/');
?>
<script src="<?= htmlspecialchars($devBase) ?>/assets/js/dev-reload.js" defer></script>
