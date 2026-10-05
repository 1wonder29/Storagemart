<?php
$base = rtrim(BASE_URL, '/');
?>
<link href="<?= htmlspecialchars($base) ?>/assets/css/app-sidebar.css?v=4" rel="stylesheet">
<link href="<?= htmlspecialchars($base) ?>/assets/css/app-shell.css?v=20261005" rel="stylesheet">
<link href="<?= htmlspecialchars($base) ?>/assets/css/logout-modal.css?v=1" rel="stylesheet">
<link href="<?= htmlspecialchars($base) ?>/assets/css/ui-readonly-interaction.css?v=2" rel="stylesheet">
<?php /* Dark mode is shared by every role; the "it-dark" class name is historical. */ ?>
<link href="<?= htmlspecialchars($base) ?>/assets/css/it-dark-mode.css" rel="stylesheet">
<link href="<?= htmlspecialchars($base) ?>/assets/css/app-dark-pages.css?v=1" rel="stylesheet">
<script>
(function () {
    try {
        if (localStorage.getItem('it-dark-mode') === '1') {
            document.documentElement.classList.add('it-dark');
        }
    } catch (e) {}
})();
</script>
