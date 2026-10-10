<?php /* "Manuals & Guides" in every role's menu: the IT manuals library and the "How to use the TMS" guides (/manuals). */ ?>
<hr class="sidebar-divider">
<div class="sidebar-heading">Help</div>
<li class="nav-item <?= (($activePage ?? '') === 'manuals') ? 'active' : '' ?>">
    <a class="nav-link" href="<?= htmlspecialchars(rtrim(BASE_URL, '/')) ?>/manuals">
        <i class="fas fa-book-open"></i>
        <span>Manuals &amp; Guides</span>
    </a>
</li>
