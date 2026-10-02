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

  if (toggle && sidebar) {
    toggle.setAttribute('aria-expanded', sidebar.classList.contains('toggled') ? 'false' : 'true');
    toggle.addEventListener('click', function () {
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
