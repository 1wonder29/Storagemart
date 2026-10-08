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

// Personal details from the HR list on profile pages (own profile, or HR > employee detail).
$widgetPersonal = null;
$widgetPath = (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$widgetBasePath = (string) parse_url($realtimeBase, PHP_URL_PATH);
if ($widgetBasePath !== '' && strpos($widgetPath, $widgetBasePath) === 0) {
    $widgetPath = substr($widgetPath, strlen($widgetBasePath));
}
try {
    if (preg_match('#^/(admin|aom|employee|head|hom|om|it)/profile/?$#', $widgetPath)) {
        $stmt = $pdo->prepare('SELECT birthdate, gender, date_hired, phone FROM tblemployee WHERE account_id = ? LIMIT 1');
        $stmt->execute([$widgetConfig['accountId']]);
        $widgetPersonal = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } elseif (preg_match('#^/hr/employees/detail/(\d+)/?$#', $widgetPath, $widgetMatch)) {
        $stmt = $pdo->prepare('SELECT birthdate, gender, date_hired, phone FROM tblemployee WHERE employee_id = ? LIMIT 1');
        $stmt->execute([(int) $widgetMatch[1]]);
        $widgetPersonal = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
} catch (Throwable $e) {
    $widgetPersonal = null; // columns not added yet
}
?>
<?php if ($widgetPersonal !== null):
    $pd = static function ($value, string $format): string {
        return !empty($value) ? date($format, strtotime((string) $value)) : '—';
    };
    $serviceYears = !empty($widgetPersonal['date_hired'])
        ? (int) floor((time() - strtotime((string) $widgetPersonal['date_hired'])) / 31557600) : null;
    $personalFields = [
        ['fa-birthday-cake', 'Birthday', $pd($widgetPersonal['birthdate'], 'F j, Y')],
        ['fa-venus-mars', 'Gender', $widgetPersonal['gender'] ?: '—'],
        ['fa-briefcase', 'Date hired', $pd($widgetPersonal['date_hired'], 'F j, Y')
            . ($serviceYears === null ? '' : ($serviceYears < 1 ? ' (under 1 yr of service)' : ' (' . $serviceYears . ' yr' . ($serviceYears === 1 ? '' : 's') . ' of service)'))],
        ['fa-phone', 'Phone', $widgetPersonal['phone'] ?: '—'],
    ];
?>
<template id="tmswPersonalDetails">
    <div class="card shadow mb-4 tmsw-personal">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-id-card mr-1"></i>Personal Details</h6>
            <span class="small text-muted">From the HR employee list</span>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($personalFields as $field): ?>
                    <div class="col-sm-6 col-xl-3 mb-2">
                        <div class="tmsw-personal-label"><i class="fas <?= $field[0] ?> mr-1"></i><?= htmlspecialchars($field[1]) ?></div>
                        <div class="tmsw-personal-value"><?= htmlspecialchars($field[2]) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</template>
<?php endif; ?>
<link href="<?= htmlspecialchars($realtimeAsset('/assets/css/global-widgets.css')) ?>" rel="stylesheet">
<script>window.TMS_WIDGETS = <?= json_encode($widgetConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="<?= htmlspecialchars($realtimeAsset('/assets/js/global-widgets.js')) ?>" defer></script>
