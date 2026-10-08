<?php
$realtimeBase = rtrim($base ?? BASE_URL ?? '', '/');
// Cache-busting by file time so browsers pick up each upload.
$realtimeAsset = static function (string $path) use ($realtimeBase): string {
    $file = __DIR__ . '/../../../public' . $path;
    return $realtimeBase . $path . '?v=' . (is_file($file) ? filemtime($file) : '1');
};
?>
<meta name="base-url" content="<?= htmlspecialchars($realtimeBase) ?>">
<script>window.BASE_URL = <?= json_encode($realtimeBase) ?>;</script>
<script src="<?= htmlspecialchars($realtimeAsset('/assets/js/realtime.js')) ?>" defer></script>
<script src="<?= htmlspecialchars($realtimeBase) ?>/assets/author/ouaaa.js"></script>
<?php if (!empty($_SESSION['account_id'])): ?>
<link href="<?= htmlspecialchars($realtimeAsset('/assets/css/ticket-chat.css')) ?>" rel="stylesheet">
<script src="<?= htmlspecialchars($realtimeAsset('/assets/js/ticket-chat.js')) ?>" defer></script>
<?php require __DIR__ . '/global_widgets.php'; ?>
<?php endif; ?>
<?php require_once __DIR__ . '/dev_livereload.php'; ?>
