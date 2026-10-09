/*
 * Site-wide widgets for signed-in pages (config in window.TMS_WIDGETS, see partials/global_widgets.php):
 *  - Announcements: pop up first after signing in (important ones can be closed after 5 seconds),
 *    megaphone with unread count in the header.
 *  - Birthday: greeting for the celebrant (with the colleagues' greetings), and on the dashboards
 *    a panel on the right with today's birthdays (Greet button) and a countdown 3 days before.
 *  - Seasonal theme scheduled by Admin: colors, greeting ribbon and its screen effects (theme-effects.js).
 *  - Employees' first-time guide ("How to use the TMS").
 * Popups come one at a time: announcements, then the birthday greeting, then the guide.
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
  var IMPORTANT_WAIT = 5; // seconds before an important announcement can be closed

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
  function post(path, data) {
    var form = new FormData();
    Object.keys(data || {}).forEach(function (k) { form.append(k, data[k]); });
    form.append('csrf_token', cfg.csrf || '');
    return api(path, { method: 'POST', body: form });
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
  function initials(name) {
    return String(name || '?').split(/\s+/).filter(Boolean).slice(0, 2).map(function (p) { return p.charAt(0).toUpperCase(); }).join('');
  }

  // ---------- popups one at a time: announcements first ----------
  var annDone = false;
  var annWaiters = [];
  function afterAnnouncements(fn) {
    if (annDone) setTimeout(fn, 300); else annWaiters.push(fn);
  }
  function announcementsDone() {
    if (annDone) return;
    annDone = true;
    window.__tmsAnnDone = true;
    try { document.dispatchEvent(new Event('tms:announcements-done')); } catch (e) { /* old browser */ }
    annWaiters.splice(0).forEach(function (fn) { setTimeout(fn, 300); });
  }

  // ---------- confetti / balloons (birthday) ----------
  function playEffect(kind, items, colors, seconds) {
    var layer = el('div', 'tmsw-fx');
    layer.setAttribute('aria-hidden', 'true');
    document.body.appendChild(layer);
    var count = kind === 'confetti' ? 90 : 36;
    if (reduceMotion) count = Math.round(count / 2);
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
      layer.appendChild(piece);
    }
    setTimeout(function () { layer.remove(); }, (seconds + 9) * 1000);
  }

  // ---------- announcements ----------
  var ann = { items: [], btn: null, badge: null, panel: null };

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
    if (!queue.length) { announcementsDone(); return; }
    if (document.querySelector('.tmsw-modal.tmsw-ann-modal')) return;
    var item = queue.shift();
    var important = item.importance === 'important';
    var overlay = el('div', 'tmsw-modal tmsw-ann-modal');
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    var card = el('div', 'tmsw-card' + (important ? ' tmsw-important' : ''));
    var head = el('div', 'tmsw-card-head');
    head.appendChild(icon('fas fa-bullhorn'));
    head.appendChild(el('span', null, important ? 'Important announcement' : 'Announcement'));
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
    var okText = queue.length ? 'Got it (' + queue.length + ' more)' : 'Got it';
    var ok = el('button', 'btn btn-primary btn-sm', okText);
    ok.type = 'button';
    actions.appendChild(later);
    actions.appendChild(ok);
    card.appendChild(actions);
    overlay.appendChild(card);
    document.body.appendChild(overlay);

    // Important: the buttons unlock after a few seconds so it gets read.
    if (important) {
      var wait = IMPORTANT_WAIT;
      var hint = el('div', 'tmsw-wait', 'Please read this. You can close it in ' + wait + ' s.');
      card.insertBefore(hint, actions);
      ok.disabled = later.disabled = true;
      ok.textContent = okText + ' (' + wait + ')';
      var timer = setInterval(function () {
        wait--;
        if (wait > 0) {
          ok.textContent = okText + ' (' + wait + ')';
          hint.textContent = 'Please read this. You can close it in ' + wait + ' s.';
          return;
        }
        clearInterval(timer);
        ok.disabled = later.disabled = false;
        ok.textContent = okText;
        hint.remove();
        ok.focus();
      }, 1000);
    } else {
      ok.focus();
    }

    function close() { overlay.remove(); document.removeEventListener('keydown', onKey); }
    function onKey(e) { if (e.key === 'Escape' && !later.disabled) { later.click(); } }
    document.addEventListener('keydown', onKey);
    later.addEventListener('click', function () {
      try { sessionStorage.setItem('tmsw_ann_later', '1'); } catch (e) {}
      close();
      announcementsDone();
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
      if (!res || !res.success) { announcementsDone(); return; }
      ann.items = res.items || [];
      if (!ann.items.length && !cfg.openAnnouncement) { announcementsDone(); return; }
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
      // Important announcements first.
      queue.sort(function (a, b) { return (b.importance === 'important') - (a.importance === 'important'); });
      showAnnouncementModal(queue);
    }).catch(function () { announcementsDone(); });
  }

  // ---------- birthday (celebrant) ----------
  function playBirthday(name) {
    if (document.querySelector('.tmsw-bday')) return;
    var overlay = el('div', 'tmsw-bday');
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-label', 'Happy birthday');
    var card = el('div', 'tmsw-bday-card');
    card.appendChild(el('div', 'tmsw-bday-cake', '🎂'));
    card.appendChild(el('h4', null, 'Happy Birthday' + (name ? ', ' + name : '') + '!'));
    card.appendChild(el('p', null, 'Everyone at StorageMart wishes you a wonderful day. 🎉'));
    var wishes = el('div', 'tmsw-bday-wishes');
    card.appendChild(wishes);
    var ok = el('button', 'btn btn-primary btn-sm', 'Thank you!');
    ok.type = 'button';
    card.appendChild(ok);
    overlay.appendChild(card);
    document.body.appendChild(overlay);
    ok.focus();
    ok.addEventListener('click', function () { overlay.remove(); });
    playEffect('confetti', [], ['#f26522', '#1f3a93', '#f6c23e', '#1cc88a', '#e74a3b', '#36b9cc'], 6);
    playEffect('rise', ['🎈', '🎈', '🎉', '🎁'], [], 6);
    if (cfg.birthday && cfg.birthday.date !== 'preview') {
      api('/birthdays/feed').then(function (res) { if (res && res.success) fillWishes(wishes, res.my_greetings || []); }).catch(function () {});
    }
  }
  function fillWishes(box, list) {
    box.textContent = '';
    if (!list.length) return;
    box.appendChild(el('div', 'tmsw-bday-wishes-title', list.length + (list.length === 1 ? ' colleague greeted you' : ' colleagues greeted you')));
    list.slice(0, 6).forEach(function (g) {
      var row = el('div', 'tmsw-bday-wish');
      row.appendChild(el('strong', null, g.from + ': '));
      row.appendChild(document.createTextNode(g.message));
      box.appendChild(row);
    });
    if (list.length > 6) box.appendChild(el('div', 'tmsw-bday-wish small text-muted', 'and ' + (list.length - 6) + ' more'));
  }

  function initBirthday() {
    var b = cfg.birthday;
    if (!b) return;
    var btn = headerButton('tmsw-bday-btn', 'fas fa-birthday-cake', 'Happy birthday! Play the greeting again');
    if (btn) btn.addEventListener('click', function () { playBirthday(b.name); });
    var key = 'tmsw_bday_' + cfg.accountId + '_' + b.date;
    if (cfg.preview || !store(key)) {
      store(key, '1');
      afterAnnouncements(function () { playBirthday(b.name); });
    }
  }

  // ---------- birthday panel on the dashboards ----------
  var bd = { offset: 0, panel: null, timer: null };

  function initBirthdayPanel() {
    if (!cfg.isDashboard) return;
    api('/birthdays/feed').then(function (res) {
      if (!res || !res.success || (!res.today.length && !res.upcoming.length)) return;
      bd.offset = new Date(String(res.server_now)).getTime() - Date.now();
      renderBirthdayPanel(res);
    }).catch(function () {});
  }

  function countdown(target) {
    var ms = Math.max(0, new Date(target).getTime() - (Date.now() + bd.offset));
    var s = Math.floor(ms / 1000);
    var d = Math.floor(s / 86400), h = Math.floor(s % 86400 / 3600), m = Math.floor(s % 3600 / 60), sec = s % 60;
    var pad = function (n) { return ('0' + n).slice(-2); };
    return (d ? d + 'd ' : '') + pad(h) + ':' + pad(m) + ':' + pad(sec);
  }

  function renderBirthdayPanel(feed) {
    if (bd.panel) bd.panel.remove();
    var minimizedKey = 'tmsw_bdp_min_' + today;
    var small = window.innerWidth < 768;
    var panel = el('aside', 'tmsw-bdp');
    panel.setAttribute('aria-label', 'Birthdays');
    var head = el('div', 'tmsw-bdp-head');
    head.appendChild(el('span', 'tmsw-bdp-cake', '🎂'));
    head.appendChild(el('strong', null, feed.today.length ? 'Birthday today!' : 'Birthdays coming up'));
    var min = el('button', 'tmsw-x tmsw-bdp-min', '–');
    min.type = 'button';
    min.setAttribute('aria-label', 'Minimize');
    head.appendChild(min);
    panel.appendChild(head);
    var body = el('div', 'tmsw-bdp-body');
    panel.appendChild(body);

    if (feed.today.length) {
      body.appendChild(el('div', 'tmsw-bdp-label', 'Today'));
      feed.today.forEach(function (p) { body.appendChild(todayRow(p)); });
    }
    var counters = [];
    if (feed.upcoming.length) {
      body.appendChild(el('div', 'tmsw-bdp-label', 'Coming up'));
      feed.upcoming.forEach(function (p) {
        var row = el('div', 'tmsw-bdp-row');
        row.appendChild(el('span', 'tmsw-bdp-avatar tmsw-bdp-soon', initials(p.name)));
        var info = el('div', 'tmsw-bdp-info');
        info.appendChild(el('strong', null, p.is_me ? 'Your birthday' : p.name));
        info.appendChild(el('span', null, [p.department, p.branch].filter(Boolean).join(' · ')));
        var when = el('span', 'tmsw-bdp-count');
        info.appendChild(when);
        counters.push({ node: when, target: p.starts_at, days: p.days_left });
        row.appendChild(info);
        body.appendChild(row);
      });
    }
    function tick() {
      counters.forEach(function (c) {
        c.node.textContent = (c.days === 1 ? 'Tomorrow' : 'In ' + c.days + ' days') + ' · ' + countdown(c.target);
      });
    }
    tick();
    if (counters.length) bd.timer = setInterval(tick, 1000);

    var bubble = el('button', 'tmsw-bdp-bubble');
    bubble.type = 'button';
    bubble.setAttribute('aria-label', 'Show birthdays');
    bubble.appendChild(el('span', null, '🎂'));
    if (feed.today.length) bubble.appendChild(el('span', 'tmsw-bdp-bubble-n', String(feed.today.length)));

    function setMin(on) {
      panel.hidden = on;
      bubble.hidden = !on;
      store(minimizedKey, on ? '1' : '0');
    }
    min.addEventListener('click', function () { setMin(true); });
    bubble.addEventListener('click', function () { setMin(false); });
    document.body.appendChild(panel);
    document.body.appendChild(bubble);
    var saved = store(minimizedKey);
    setMin(saved === '1' || (saved === null && small));
    bd.panel = panel;
  }

  function todayRow(p) {
    var row = el('div', 'tmsw-bdp-row tmsw-bdp-today');
    row.appendChild(el('span', 'tmsw-bdp-avatar', initials(p.name)));
    var info = el('div', 'tmsw-bdp-info');
    info.appendChild(el('strong', null, p.is_me ? 'It\'s your birthday! 🎉' : p.name));
    info.appendChild(el('span', null, p.is_me ? (p.greet_count + (p.greet_count === 1 ? ' greeting' : ' greetings') + ' from colleagues')
      : [p.department, p.branch].filter(Boolean).join(' · ')));
    row.appendChild(info);
    if (p.is_me) {
      var see = el('button', 'btn btn-sm btn-outline-primary tmsw-bdp-btn', 'See');
      see.type = 'button';
      see.addEventListener('click', function () { playBirthday(cfg.birthday ? cfg.birthday.name : ''); });
      row.appendChild(see);
      return row;
    }
    var status = el('span', 'tmsw-bdp-greeted', '✓ Greeted');
    status.hidden = !p.greeted;
    var greet = el('button', 'btn btn-sm btn-warning tmsw-bdp-btn', 'Greet');
    greet.type = 'button';
    greet.hidden = p.greeted;
    row.appendChild(status);
    row.appendChild(greet);
    var form = el('form', 'tmsw-bdp-form');
    form.hidden = true;
    var input = el('input', 'form-control form-control-sm');
    input.maxLength = 200;
    input.value = 'Happy birthday, ' + (p.firstname || p.name) + '! 🎂';
    input.setAttribute('aria-label', 'Birthday greeting for ' + p.name);
    var send = el('button', 'btn btn-sm btn-primary', 'Send');
    send.type = 'submit';
    form.appendChild(input);
    form.appendChild(send);
    var wrap = el('div', 'tmsw-bdp-today-wrap');
    wrap.appendChild(row);
    wrap.appendChild(form);
    greet.addEventListener('click', function () { form.hidden = false; greet.hidden = true; input.focus(); input.select(); });
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      send.disabled = true;
      post('/birthdays/greet', { celebrant_id: p.account_id, message: input.value.trim() }).then(function (res) {
        send.disabled = false;
        if (!res || !res.success) { window.alert((res && res.message) || 'Could not send the greeting.'); return; }
        form.hidden = true;
        status.hidden = false;
        playEffect('confetti', [], ['#f26522', '#1f3a93', '#f6c23e', '#1cc88a'], 4);
      }).catch(function () { send.disabled = false; });
    });
    return wrap;
  }

  // ---------- seasonal theme ----------
  function initTheme() {
    var t = cfg.theme;
    if (!t) return;
    var root = document.documentElement;
    root.classList.add('tms-theme', 'tms-theme-' + t.key);
    root.style.setProperty('--tms-theme-main', t.colors[0]);
    root.style.setProperty('--tms-theme-accent', t.colors[1]);

    var effect = function () {
      if (window.TMSEffects) window.TMSEffects.play(t.effects, t, 10);
      else playEffect('fall', t.items, t.colors.concat(['#ffffff']), 8);
    };
    var btn = headerButton('tmsw-theme-btn', 'fas fa-magic', t.name + ': play the effects');
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
      setTimeout(effect, 500);
    }
  }

  // ---------- employees' first-time guide ----------
  function showGuide(firstTime) {
    if (document.querySelector('.tmsw-bday') || document.querySelector('.tmsw-ann-modal')) {
      setTimeout(function () { showGuide(firstTime); }, 800);
      return;
    }
    if (document.querySelector('.tmsw-guide')) return;
    var overlay = el('div', 'tmsw-modal tmsw-guide');
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('aria-label', 'How to use the TMS');
    var box = el('div', 'tmsw-guide-box');
    var head = el('div', 'tmsw-guide-head');
    head.appendChild(el('strong', null, firstTime ? 'Welcome to the StorageMart TMS!' : 'How to use the TMS'));
    var skip = el('button', 'btn btn-sm btn-light', firstTime ? 'Skip for now' : 'Close');
    skip.type = 'button';
    head.appendChild(skip);
    box.appendChild(head);
    var frame = el('iframe', 'tmsw-guide-frame');
    frame.src = BASE + '/guide?embed=1';
    frame.title = 'How to use the TMS';
    box.appendChild(frame);
    overlay.appendChild(box);
    document.body.appendChild(overlay);
    function done() {
      overlay.remove();
      window.removeEventListener('message', onMessage);
      if (firstTime) post('/onboarding/done', {}).catch(function () {});
    }
    function onMessage(e) {
      if (e.origin === window.location.origin && e.data === 'tms-guide-done') done();
    }
    window.addEventListener('message', onMessage);
    skip.addEventListener('click', done);
  }

  function initGuide() {
    if (!cfg.isEmployee) return;
    var btn = headerButton('tmsw-guide-btn', 'fas fa-question-circle', 'How to use the TMS');
    if (btn) btn.addEventListener('click', function () { showGuide(false); });
    if (cfg.onboarding) afterAnnouncements(function () { showGuide(true); });
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
    initAnnouncements();
    initBirthday();
    initBirthdayPanel();
    initGuide();
    document.addEventListener('click', function (e) {
      if (ann.panel && !ann.panel.contains(e.target) && !(ann.btn && ann.btn.contains(e.target))) {
        ann.panel.remove();
        ann.panel = null;
      }
    });
  }

  // Phone photos are several MB; shrink them (max 1600 px, JPEG) before uploading.
  window.TMSShrinkImage = function (file, maxSide, quality) {
    maxSide = maxSide || 1600;
    quality = quality || 0.85;
    return new Promise(function (resolve) {
      if (!file || !/^image\/(jpeg|png|webp)$/.test(file.type) || file.size < 400 * 1024 || !window.URL || !URL.createObjectURL) {
        resolve(file);
        return;
      }
      var url = URL.createObjectURL(file);
      var img = new Image();
      img.onload = function () {
        var scale = Math.min(1, maxSide / Math.max(img.naturalWidth, img.naturalHeight));
        var canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(img.naturalWidth * scale));
        canvas.height = Math.max(1, Math.round(img.naturalHeight * scale));
        var ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        canvas.toBlob(function (blob) {
          URL.revokeObjectURL(url);
          if (!blob || blob.size >= file.size) { resolve(file); return; }
          try {
            resolve(new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' }));
          } catch (e) {
            resolve(file);
          }
        }, 'image/jpeg', quality);
      };
      img.onerror = function () { URL.revokeObjectURL(url); resolve(file); };
      img.src = url;
    });
  };

  // Exposed for the Admin themes page ("Test" buttons) and other scripts.
  window.TMSWidgets = {
    playEffect: playEffect,
    playBirthday: playBirthday,
    afterAnnouncements: afterAnnouncements,
    showGuide: showGuide,
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
