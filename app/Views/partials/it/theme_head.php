<?php
if (!isset($base)) {
    $base = rtrim(BASE_URL, '/');
}
?>
<link href="<?= htmlspecialchars($base) ?>/assets/css/it-dark-mode.css" rel="stylesheet">
<script>
(function () {
    try {
        if (localStorage.getItem('it-dark-mode') === '1') {
            document.documentElement.classList.add('it-dark');
        }
    } catch (e) {}
})();
</script>
