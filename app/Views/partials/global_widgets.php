<?php
/**
 * Site-wide extras for signed-in pages (announcements, birthday greeting, seasonal
 * theme). Included once from realtime_scripts.php, which every sidebar loads.
 * Each piece fails quietly so a missing table never breaks the page.
 */
require_once __DIR__ . '/../../Helpers/SeasonalThemes.php';

$widgetConfig = [
    'base' => $realtimeBase,
    'accountId' => (int) ($_SESSION['account_id'] ?? 0),
    'openAnnouncement' => (int) ($_GET['announcement'] ?? 0),
    'birthday' => null,
    'theme' => null,
    'preview' => false,
];

global $pdo;
$widgetMe = [];
// Birthday: greet the signed-in employee on their day (Feb 29 birthdays on Feb 28 in other years).
try {
    $stmt = $pdo->prepare('SELECT firstname, birthdate FROM tblemployee WHERE account_id = ? LIMIT 1');
    $stmt->execute([$widgetConfig['accountId']]);
    $widgetMe = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    if (!empty($widgetMe['birthdate'])) {
        $birthMd = date('m-d', strtotime((string) $widgetMe['birthdate']));
        $todayMd = date('m-d');
        if ($birthMd === $todayMd || ($birthMd === '02-29' && $todayMd === '02-28' && !date('L'))) {
            $widgetConfig['birthday'] = ['name' => trim((string) $widgetMe['firstname']), 'date' => date('Y-m-d')];
        }
    }
} catch (Throwable $e) {
    // birthdate column not added yet
}

$widgetConfig['theme'] = SeasonalThemes::current($pdo);

// Admin preview (only in the admin's own browser, nothing is saved).
$widgetRealRole = strtoupper((string) ($_SESSION['superuser_real_usertype'] ?? $_SESSION['usertype'] ?? ''));
if ($widgetRealRole === 'ADMIN') {
    $previewKey = (string) ($_GET['theme_preview'] ?? '');
    if ($previewKey !== '' && SeasonalThemes::exists($previewKey)) {
        $widgetConfig['theme'] = SeasonalThemes::forBrowser($previewKey);
        $widgetConfig['preview'] = true;
    }
    if (!empty($_GET['birthday_preview'])) {
        $widgetConfig['birthday'] = ['name' => trim((string) ($widgetMe['firstname'] ?? '')) ?: 'there', 'date' => 'preview'];
        $widgetConfig['preview'] = true;
    }
}
?>
<link href="<?= htmlspecialchars($realtimeAsset('/assets/css/global-widgets.css')) ?>" rel="stylesheet">
<script>window.TMS_WIDGETS = <?= json_encode($widgetConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="<?= htmlspecialchars($realtimeAsset('/assets/js/global-widgets.js')) ?>" defer></script>
