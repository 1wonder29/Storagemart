(function () {
  'use strict';

  var STORAGE_KEY = 'app-sidebar-collapsed';
  var sidebar = document.getElementById('accordionSidebar');
  var toggle = document.getElementById('appSidebarToggle');

  /* ---------- Sidebar collapse (remembered between pages) ---------- */

  function setCollapsed(collapsed) {
    if (!sidebar) return;
    sidebar.classList.toggle('toggled', collapsed);
    document.body.classList.toggle('sidebar-toggled', collapsed);
    if (toggle) toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');

    if (collapsed && window.jQuery) {
      window.jQuery('.sidebar .collapse').collapse('hide');
    }
    try {
      localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
    } catch (e) {}
  }

  /* ---------- Phones: the menu slides in over the page and closes again ---------- */
  var phone = window.matchMedia ? window.matchMedia('(max-width: 767.98px)') : null;
  var backdrop = null;

  function isPhone() {
    return !!(phone && phone.matches);
  }

  function setMobileOpen(open) {
    document.body.classList.toggle('sidebar-mobile-open', open);
    if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  if (sidebar && isPhone()) {
    // The saved desktop "collapsed" state does not apply to the phone menu.
    sidebar.classList.remove('toggled');
    document.body.classList.remove('sidebar-toggled');
  }
  if (sidebar) {
    backdrop = document.createElement('div');
    backdrop.className = 'app-sidebar-backdrop';
    backdrop.setAttribute('aria-hidden', 'true');
    document.body.appendChild(backdrop);
    backdrop.addEventListener('click', function () { setMobileOpen(false); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && document.body.classList.contains('sidebar-mobile-open')) setMobileOpen(false);
    });
    if (phone && phone.addEventListener) {
      phone.addEventListener('change', function () {
        setMobileOpen(false);
        if (isPhone()) {
          sidebar.classList.remove('toggled');
          document.body.classList.remove('sidebar-toggled');
        }
      });
    }
  }

  if (toggle && sidebar) {
    toggle.setAttribute('aria-expanded', isPhone() ? 'false' : (sidebar.classList.contains('toggled') ? 'false' : 'true'));
    toggle.addEventListener('click', function () {
      if (isPhone()) {
        setMobileOpen(!document.body.classList.contains('sidebar-mobile-open'));
        return;
      }
      setCollapsed(!sidebar.classList.contains('toggled'));
    });
  }

  /* ---------- Clock (Philippine time) ---------- */

  var timeEl = document.getElementById('appClockTime');
  var dateEl = document.getElementById('appClockDate');

  function tick() {
    var now = new Date();
    try {
      if (timeEl) {
        timeEl.textContent = now.toLocaleTimeString('en-US', {
          hour: 'numeric', minute: '2-digit', timeZone: 'Asia/Manila'
        });
      }
      if (dateEl) {
        dateEl.textContent = now.toLocaleDateString('en-US', {
          weekday: 'short', month: 'short', day: 'numeric', year: 'numeric', timeZone: 'Asia/Manila'
        });
      }
    } catch (e) {}
  }

  if (timeEl || dateEl) {
    tick();
    window.setInterval(tick, 15000);
  }
})();
