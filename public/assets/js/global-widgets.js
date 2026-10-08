/*
 * Site-wide widgets for signed-in pages (config in window.TMS_WIDGETS, see partials/global_widgets.php):
 *  - Announcements: pop up once per person, megaphone with unread count in the header.
 *  - Birthday: greeting with confetti on the employee's birthday + a header button to replay it (that day only).
 *  - Seasonal theme scheduled by Admin (Halloween, Christmas, ...): colors, greeting ribbon, falling effect.
 */
(function () {
  'use strict';

  var cfg = window.TMS_WIDGETS;
  if (!cfg || window.__tmsWidgetsLoaded) return;
  window.__tmsWidgetsLoaded = true;

  var BASE = String(cfg.base || '').replace(/\/$/, '');
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var now = new Date();
  var today = now.getFullYear() + '-' + ('0' + (now.getMonth() + 1)).slice(-2) + '-' + ('0' + now.getDate()).slice(-2);

  // ---------- helpers ----------
  function el(tag, cls, text) {
    var node = document.createElement(tag);
    if (cls) node.className = cls;
    if (text !== undefined && text !== null) node.textContent = text;
    return node;
  }
  function icon(cls) {
    var i = el('i', cls);
    i.setAttribute('aria-hidden', 'true');
    return i;
  }
  function store(key, value) {
    try {
      if (value === undefined) return localStorage.getItem(key);
      localStorage.setItem(key, value);
    } catch (e) { /* private mode */ }
    return null;
  }
  function api(path, options) {
    return fetch(BASE + path, Object.assign({ credentials: 'same-origin', headers: { 'Accept': 'application/json' } }, options || {}))
      .then(function (r) { return r.json(); });
  }
  function headerSlot() {
    return document.getElementById('appTopbarExtras');
  }
  function headerButton(cls, iconCls, label) {
    var slot = headerSlot();
    if (!slot) return null;
    var btn = el('button', 'btn btn-link nav-link py-2 tmsw-header-btn ' + cls);
    btn.type = 'button';
    btn.setAttribute('aria-label', label);
    btn.title = label;
    btn.appendChild(icon(iconCls));
    slot.appendChild(btn);
    return btn;
  }

  // ---------- falling / rising effects ----------
  function playEffect(kind, items, colors, seconds) {
    if (reduceMotion) return;
    var layer = el('div', 'tmsw-fx');
    layer.setAttribute('aria-hidden', 'true');
    document.body.appendChild(layer);
    var count = kind === 'confetti' ? 90 : 36;
    for (var i = 0; i < count; i++) {
      var piece;
      if (kind === 'confetti') {
        piece = el('span', 'tmsw-confetti');
        piece.style.background = colors[i % colors.length];
        piece.style.width = (6 + Math.random() * 6) + 'px';
        piece.style.height = (8 + Math.random() * 10) + 'px';
      } else {
        piece = el('span', 'tmsw-item', items[i % items.length]);
        piece.style.fontSize = (16 + Math.random() * 22) + 'px';
      }
      piece.classList.add(kind === 'rise' ? 'tmsw-rise' : 'tmsw-fall');
      piece.style.left = (Math.random() * 100) + 'vw';
      piece.style.animationDuration = (4 + Math.random() * 5) + 's';
      piece.style.animationDelay = (Math.random() * Math.max(1, seconds - 5)) + 's';
      if (kind === 'snow') piece.style.opacity = String(0.5 + Math.random() * 0.5);
      layer.appendChild(piece);
    }
    setTimeout(function () { layer.remove(); }, (seconds + 9) * 1000);
  }

  // ---------- announcements ----------
  var ann = { items: [], btn: null, badge: null, panel: null };
  var birthdayPending = false;

  function annBadge() {
    if (!ann.badge) return;
    var unread = ann.items.filter(function (i) { return !i.is_read; }).length;
    ann.badge.hidden = unread === 0;
    ann.badge.textContent = unread > 9 ? '9+' : String(unread);
    ann.btn.setAttribute('aria-label', unread ? 'Announcements, ' + unread + ' unread' : 'Announcements');
  }

  function markRead(item) {
    if (!item || item.is_read) return;
    item.is_read = true;
    annBadge();
    var form = new FormData();
    form.append('id', String(item.announcement_id));
    api('/announcements/read', { method: 'POST', body: form }).catch(function () {});
  }

  function annMeta(item) {
    var who = item.created_by_name || item.created_by_role || '';
    var when = new Date(String(item.starts_at).replace(' ', 'T'));
    var whenText = isNaN(when.getTime()) ? '' : when.toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric' });
    var to = item.audience === 'DEPARTMENT' ? ' · ' + item.department + ' department' : ' · Everyone';
    return (who ? who + ' · ' : '') + whenText + to;
  }

  function showAnnouncementModal(queue) {
    if (!queue.length || document.querySelector('.tmsw-modal')) return;
    // Let the birthday greeting finish first.
    if (birthdayPending || document.querySelector('.tmsw-bday')) {
      setTimeout(function () { showAnnouncementModal(queue); }, 800);
      return;
    }
    var item = queue.shift();
    var overlay = el('div', 'tmsw-modal');
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    var card = el('div', 'tmsw-card' + (item.importance === 'important' ? ' tmsw-important' : ''));
    var head = el('div', 'tmsw-card-head');
    head.appendChild(icon('fas fa-bullhorn'));
    head.appendChild(el('span', null, item.importance === 'important' ? 'Important announcement' : 'Announcement'));
    card.appendChild(head);
    var title = el('h5', 'tmsw-card-title', item.title);
    title.id = 'tmswAnnTitle';
    overlay.setAttribute('aria-labelledby', title.id);
    card.appendChild(title);
    card.appendChild(el('div', 'tmsw-card-body', item.body));
    card.appendChild(el('div', 'tmsw-card-meta', annMeta(item)));
    var actions = el('div', 'tmsw-actions');
    var later = el('button', 'btn btn-light btn-sm', 'Remind me later');
    later.type = 'button';
    var ok = el('button', 'btn btn-primary btn-sm', queue.length ? 'Got it (' + queue.length + ' more)' : 'Got it');
    ok.type = 'button';
    actions.appendChild(later);
    actions.appendChild(ok);
    card.appendChild(actions);
    overlay.appendChild(card);
    document.body.appendChild(overlay);
    ok.focus();

    function close() { overlay.remove(); document.removeEventListener('keydown', onKey); }
    function onKey(e) { if (e.key === 'Escape') { later.click(); } }
    document.addEventListener('keydown', onKey);
    later.addEventListener('click', function () {
      try { sessionStorage.setItem('tmsw_ann_later', '1'); } catch (e) {}
      close();
    });
    ok.addEventListener('click', function () {
      markRead(item);
      close();
      showAnnouncementModal(queue);
    });
  }

  function toggleAnnouncementPanel() {
    if (ann.panel) { ann.panel.remove(); ann.panel = null; return; }
    var panel = el('div', 'tmsw-panel');
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-label', 'Announcements');
    var head = el('div', 'tmsw-panel-head');
    head.appendChild(el('strong', null, 'Announcements'));
    var close = el('button', 'tmsw-x', '×');
    close.type = 'button';
    close.setAttribute('aria-label', 'Close');
    head.appendChild(close);
    panel.appendChild(head);
    var list = el('div', 'tmsw-panel-list');
    if (!ann.items.length) list.appendChild(el('div', 'tmsw-empty', 'No announcements right now.'));
    ann.items.forEach(function (item) {
      var row = el('div', 'tmsw-ann' + (item.is_read ? '' : ' tmsw-unread') + (item.importance === 'important' ? ' tmsw-important' : ''));
      row.appendChild(el('div', 'tmsw-ann-title', item.title));
      row.appendChild(el('div', 'tmsw-ann-body', item.body));
      row.appendChild(el('div', 'tmsw-card-meta', annMeta(item)));
      list.appendChild(row);
      markRead(item);
    });
    panel.appendChild(list);
    document.body.appendChild(panel);
    ann.panel = panel;
    var rect = ann.btn.getBoundingClientRect();
    panel.style.top = (rect.bottom + 8) + 'px';
    panel.style.right = Math.max(12, window.innerWidth - rect.right - 40) + 'px';
    close.addEventListener('click', toggleAnnouncementPanel);
  }

  function initAnnouncements() {
    api('/announcements/feed').then(function (res) {
      if (!res || !res.success) return;
      ann.items = res.items || [];
      if (!ann.items.length && !cfg.openAnnouncement) return;
      ann.btn = headerButton('tmsw-ann-btn', 'fas fa-bullhorn', 'Announcements');
      if (ann.btn) {
        ann.badge = el('span', 'badge badge-danger badge-counter tmsw-badge');
        ann.btn.appendChild(ann.badge);
        ann.btn.addEventListener('click', toggleAnnouncementPanel);
        annBadge();
      }
      var queue;
      if (cfg.openAnnouncement) {
        queue = ann.items.filter(function (i) { return i.announcement_id === cfg.openAnnouncement; });
      } else {
        var later = false;
        try { later = sessionStorage.getItem('tmsw_ann_later') === '1'; } catch (e) {}
        queue = later ? [] : ann.items.filter(function (i) { return !i.is_read; });
      }
      showAnnouncementModal(queue);
    }).catch(function () {});
  }

  // ---------- birthday ----------
  function playBirthday(name) {
    if (document.querySelector('.tmsw-bday')) return;
    var overlay = el('div', 'tmsw-bday');
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-label', 'Happy birthday');
    var card = el('div', 'tmsw-bday-card');
    card.appendChild(el('div', 'tmsw-bday-cake', '🎂'));
    card.appendChild(el('h4', null, 'Happy Birthday' + (name ? ', ' + name : '') + '!'));
    card.appendChild(el('p', null, 'Everyone at StorageMart wishes you a wonderful day. 🎉'));
    var ok = el('button', 'btn btn-primary btn-sm', 'Thank you!');
    ok.type = 'button';
    card.appendChild(ok);
    overlay.appendChild(card);
    document.body.appendChild(overlay);
    ok.focus();
    ok.addEventListener('click', function () { overlay.remove(); });
    playEffect('confetti', [], ['#f26522', '#1f3a93', '#f6c23e', '#1cc88a', '#e74a3b', '#36b9cc'], 6);
    playEffect('rise', ['🎈', '🎈', '🎉', '🎁'], [], 6);
  }

  function initBirthday() {
    var b = cfg.birthday;
    if (!b) return;
    var btn = headerButton('tmsw-bday-btn', 'fas fa-birthday-cake', 'Happy birthday! Play the greeting again');
    if (btn) btn.addEventListener('click', function () { playBirthday(b.name); });
    var key = 'tmsw_bday_' + cfg.accountId + '_' + b.date;
    if (cfg.preview || !store(key)) {
      store(key, '1');
      birthdayPending = true;
      setTimeout(function () { birthdayPending = false; playBirthday(b.name); }, 600);
    }
  }

  // ---------- seasonal theme ----------
  function initTheme() {
    var t = cfg.theme;
    if (!t) return;
    var root = document.documentElement;
    root.classList.add('tms-theme', 'tms-theme-' + t.key);
    root.style.setProperty('--tms-theme-main', t.colors[0]);
    root.style.setProperty('--tms-theme-accent', t.colors[1]);

    var effect = function () { playEffect(t.effect, t.items, t.colors.concat(['#ffffff']), 8); };
    var btn = headerButton('tmsw-theme-btn', 'fas fa-magic', t.name + ': play the effect');
    if (btn) btn.addEventListener('click', effect);

    var ribbonKey = 'tmsw_theme_' + t.key + '_' + today;
    var topbar = document.querySelector('.app-topbar');
    if (topbar && (cfg.preview || !store(ribbonKey + '_ribbon'))) {
      var ribbon = el('div', 'tmsw-ribbon');
      ribbon.appendChild(el('span', 'tmsw-ribbon-items', t.items.slice(0, 3).join(' ')));
      ribbon.appendChild(el('span', null, (cfg.preview ? 'Preview (only you see this): ' : '') + t.message));
      var x = el('button', 'tmsw-x', '×');
      x.type = 'button';
      x.setAttribute('aria-label', 'Hide');
      x.addEventListener('click', function () { store(ribbonKey + '_ribbon', '1'); ribbon.remove(); });
      ribbon.appendChild(x);
      topbar.parentNode.insertBefore(ribbon, topbar.nextSibling);
    }
    if (cfg.preview || !store(ribbonKey)) {
      store(ribbonKey, '1');
      setTimeout(effect, 400);
    }
  }

  // Personal details card (rendered by the server as a <template> on profile pages).
  function initPersonalDetails() {
    var tpl = document.getElementById('tmswPersonalDetails');
    var page = document.querySelector('#content .container-fluid');
    if (!tpl || !page || !tpl.content) return;
    page.appendChild(tpl.content.cloneNode(true));
  }

  function start() {
    initPersonalDetails();
    initTheme();
    initBirthday();
    initAnnouncements();
    document.addEventListener('click', function (e) {
      if (ann.panel && !ann.panel.contains(e.target) && !(ann.btn && ann.btn.contains(e.target))) {
        ann.panel.remove();
        ann.panel = null;
      }
    });
  }

  // Exposed for the Admin themes page ("Test" buttons).
  window.TMSWidgets = {
    playEffect: playEffect,
    playBirthday: playBirthday,
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
