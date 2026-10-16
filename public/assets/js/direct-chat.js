/*
 * Admin chat requests (DirectChatController). The Admin starts a chat from the header
 * (chat icon > New chat): person, concern, time to answer, or Urgent. The user gets a popup
 * with the concern and a countdown and accepts or declines; an urgent chat opens right away.
 * Config from window.TMS_WIDGETS (base, csrf).
 */
(function () {
  'use strict';

  function ask(message) { return window.TmsDialog ? window.TmsDialog.confirm(message) : Promise.resolve(window.confirm(message)); }
  var cfg = window.TMS_WIDGETS;
  if (!cfg || window.__directChatLoaded) return;
  window.__directChatLoaded = true;

  var BASE = String(cfg.base || '').replace(/\/$/, '');
  var MINUTES = [1, 3, 5, 10, 15, 30];
  var state = {
    canStart: false, chats: [], requests: [], offset: 0,
    btn: null, badge: null, panel: null, win: null, openId: 0, lastId: 0,
    pollTimer: null, msgTimer: null, shownRequests: {}, setupMissing: false,
  };

  // ---------- helpers ----------
  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined && text !== null) n.textContent = text;
    return n;
  }
  function icon(cls) { var i = el('i', cls); i.setAttribute('aria-hidden', 'true'); return i; }
  function api(path, form) {
    var opts = { credentials: 'same-origin', headers: { 'Accept': 'application/json' } };
    if (form) {
      form.append('csrf_token', cfg.csrf || '');
      opts.method = 'POST';
      opts.body = form;
    }
    return fetch(BASE + path, opts).then(function (r) {
      return r.json().catch(function () { return { success: false, message: 'Connection problem.' }; });
    });
  }
  function fd(obj) { var f = new FormData(); Object.keys(obj).forEach(function (k) { f.append(k, obj[k]); }); return f; }
  function serverNow() { return Date.now() + state.offset; }
  function parse(s) { return s ? new Date(String(s).replace(' ', 'T')).getTime() : 0; }
  function left(expires) {
    var ms = Math.max(0, parse(expires) - serverNow());
    var s = Math.ceil(ms / 1000);
    return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2);
  }
  function statusLabel(c) {
    return { PENDING: c.i_am_admin ? 'Waiting for answer' : 'Request', ACTIVE: c.is_urgent ? 'Urgent' : 'Open',
      DECLINED: 'Declined', EXPIRED: 'No answer', CLOSED: 'Ended' }[c.status] || c.status;
  }
  function timeOf(s) {
    var d = new Date(String(s).replace(' ', 'T'));
    return isNaN(d.getTime()) ? '' : d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
  }

  // ---------- header button and panel ----------
  function ensureButton() {
    if (state.btn) return;
    var slot = document.getElementById('appTopbarExtras');
    if (!slot) return;
    var btn = el('button', 'btn btn-link nav-link py-2 tmsw-header-btn dc-btn');
    btn.type = 'button';
    btn.title = state.canStart ? 'Chat with a user' : 'Chats with Admin';
    btn.setAttribute('aria-label', btn.title);
    btn.appendChild(icon('fas fa-comment-dots'));
    var badge = el('span', 'badge badge-danger badge-counter tmsw-badge');
    badge.hidden = true;
    btn.appendChild(badge);
    btn.addEventListener('click', function (e) { e.stopPropagation(); togglePanel(); });
    slot.appendChild(btn);
    state.btn = btn;
    state.badge = badge;
  }
  function updateBadge() {
    if (!state.badge) return;
    var n = state.chats.reduce(function (sum, c) { return sum + (c.unread || 0); }, 0)
      + state.requests.length;
    state.badge.hidden = n === 0;
    state.badge.textContent = n > 9 ? '9+' : String(n);
  }

  function togglePanel(showForm) {
    if (state.panel && !showForm) { state.panel.remove(); state.panel = null; return; }
    if (state.panel) state.panel.remove();
    var panel = el('div', 'tmsw-panel dc-panel');
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-label', 'Chats');
    var head = el('div', 'tmsw-panel-head');
    head.appendChild(el('strong', null, showForm ? 'New chat' : 'Chats'));
    var close = el('button', 'tmsw-x', '×');
    close.type = 'button';
    close.setAttribute('aria-label', 'Close');
    close.addEventListener('click', function () { panel.remove(); state.panel = null; });
    head.appendChild(close);
    panel.appendChild(head);
    if (showForm) {
      panel.appendChild(newChatForm());
    } else {
      if (state.canStart) {
        var start = el('button', 'btn btn-primary btn-sm dc-new');
        start.type = 'button';
        start.appendChild(icon('fas fa-plus mr-1'));
        start.appendChild(document.createTextNode(' New chat'));
        start.addEventListener('click', function (e) { e.stopPropagation(); togglePanel(true); });
        panel.appendChild(start);
      }
      var list = el('div', 'tmsw-panel-list');
      if (!state.chats.length) list.appendChild(el('div', 'tmsw-empty', state.canStart ? 'No chats yet. Start one with "New chat".' : 'No chats.'));
      state.chats.forEach(function (c) {
        var row = el('button', 'dc-row' + (c.unread ? ' dc-unread' : ''));
        row.type = 'button';
        var top = el('div', 'dc-row-top');
        top.appendChild(el('strong', null, c.other_name));
        top.appendChild(el('span', 'dc-chip dc-' + c.status.toLowerCase() + (c.is_urgent ? ' dc-urgent' : ''), statusLabel(c)));
        row.appendChild(top);
        row.appendChild(el('div', 'dc-row-text', c.last_message || c.concern));
        row.addEventListener('click', function () {
          panel.remove(); state.panel = null;
          if (c.status === 'PENDING' && !c.i_am_admin) showRequest(c); else openChat(c.chat_id);
        });
        list.appendChild(row);
      });
      panel.appendChild(list);
    }
    document.body.appendChild(panel);
    state.panel = panel;
    var rect = state.btn.getBoundingClientRect();
    panel.style.top = (rect.bottom + 8) + 'px';
    panel.style.right = Math.max(12, window.innerWidth - rect.right - 40) + 'px';
  }

  function newChatForm() {
    var form = el('form', 'dc-form');
    form.setAttribute('autocomplete', 'off');
    var who = el('input', 'form-control form-control-sm');
    who.placeholder = 'Search a name, department or branch';
    who.setAttribute('aria-label', 'Person');
    var results = el('div', 'dc-results');
    var chosen = { id: 0 };
    var pickedLabel = el('div', 'dc-picked small');
    var timer = null;
    function search() {
      api('/direct-chat/users?q=' + encodeURIComponent(who.value.trim())).then(function (res) {
        results.textContent = '';
        (res.users || []).forEach(function (u) {
          var b = el('button', 'dc-result');
          b.type = 'button';
          b.appendChild(el('strong', null, u.name));
          b.appendChild(el('span', null, u.detail));
          b.addEventListener('click', function () {
            chosen.id = u.account_id;
            pickedLabel.textContent = 'To: ' + u.name;
            who.value = u.name;
            results.textContent = '';
          });
          results.appendChild(b);
        });
      });
    }
    who.addEventListener('input', function () { chosen.id = 0; pickedLabel.textContent = ''; clearTimeout(timer); timer = setTimeout(search, 250); });
    who.addEventListener('focus', function () { if (!results.childNodes.length) search(); });
    var concern = el('textarea', 'form-control form-control-sm');
    concern.rows = 3;
    concern.maxLength = 500;
    concern.placeholder = 'Concern, e.g. Please check the CCTV at the Eran branch';
    concern.setAttribute('aria-label', 'Concern');
    var row = el('div', 'dc-form-row');
    var mins = el('select', 'custom-select custom-select-sm');
    mins.setAttribute('aria-label', 'Time to answer');
    MINUTES.forEach(function (m) { var o = el('option', null, m + ' min to answer'); o.value = m; if (m === 5) o.selected = true; mins.appendChild(o); });
    var urgentLabel = el('label', 'dc-urgent-label');
    var urgent = el('input');
    urgent.type = 'checkbox';
    urgentLabel.appendChild(urgent);
    urgentLabel.appendChild(document.createTextNode(' Urgent (opens right away)'));
    urgent.addEventListener('change', function () { mins.disabled = urgent.checked; });
    row.appendChild(mins);
    row.appendChild(urgentLabel);
    var msg = el('div', 'dc-error small');
    var send = el('button', 'btn btn-primary btn-sm btn-block', 'Send chat request');
    send.type = 'submit';
    [el('label', 'small font-weight-bold mb-1', 'Person'), who, pickedLabel, results,
      el('label', 'small font-weight-bold mb-1 mt-2', 'Concern'), concern, row, msg, send].forEach(function (n) { form.appendChild(n); });
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      msg.textContent = '';
      if (!chosen.id) { msg.textContent = 'Choose the person from the list.'; return; }
      if (!concern.value.trim()) { msg.textContent = 'Write the concern.'; return; }
      send.disabled = true;
      api('/direct-chat/start', fd({ user_id: chosen.id, concern: concern.value.trim(), minutes: mins.value, urgent: urgent.checked ? 1 : 0 }))
        .then(function (res) {
          send.disabled = false;
          if (!res.success) { msg.textContent = res.message || 'Not sent.'; return; }
          if (state.panel) { state.panel.remove(); state.panel = null; }
          poll().then(function () { openChat(res.chat.chat_id); });
        });
    });
    setTimeout(function () { who.focus(); }, 50);
    return form;
  }

  // ---------- request popup (user) ----------
  function showRequest(c) {
    if (document.getElementById('dcRequest' + c.chat_id)) return;
    state.shownRequests[c.chat_id] = true;
    var overlay = el('div', 'tmsw-modal dc-request');
    overlay.id = 'dcRequest' + c.chat_id;
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    var card = el('div', 'tmsw-card dc-card');
    var head = el('div', 'tmsw-card-head');
    head.appendChild(icon('fas fa-comment-dots'));
    head.appendChild(el('span', null, 'Chat request from ' + c.admin_name));
    card.appendChild(head);
    card.appendChild(el('h5', 'tmsw-card-title', 'Admin wants to chat with you'));
    card.appendChild(el('div', 'dc-concern', c.concern));
    var timer = el('div', 'dc-timer');
    card.appendChild(timer);
    var note = el('input', 'form-control form-control-sm mt-2');
    note.placeholder = 'Reason (optional, if you decline), e.g. In a meeting until 3 PM';
    note.maxLength = 255;
    card.appendChild(note);
    var actions = el('div', 'tmsw-actions');
    var decline = el('button', 'btn btn-light btn-sm', 'Decline');
    decline.type = 'button';
    var accept = el('button', 'btn btn-success btn-sm', 'Accept and chat');
    accept.type = 'button';
    actions.appendChild(decline);
    actions.appendChild(accept);
    card.appendChild(actions);
    overlay.appendChild(card);
    document.body.appendChild(overlay);
    accept.focus();
    var tick = setInterval(function () {
      var remaining = parse(c.expires_at) - serverNow();
      timer.textContent = remaining > 0 ? 'Please answer within ' + left(c.expires_at) : 'The time to answer has run out.';
      if (remaining <= 0) { clearInterval(tick); accept.disabled = decline.disabled = true; setTimeout(function () { overlay.remove(); poll(); }, 2500); }
    }, 500);
    function answer(yes) {
      accept.disabled = decline.disabled = true;
      api('/direct-chat/respond', fd({ chat_id: c.chat_id, accept: yes ? 1 : 0, note: note.value.trim() })).then(function (res) {
        clearInterval(tick);
        overlay.remove();
        poll();
        if (res.success && yes) openChat(c.chat_id);
        else if (!res.success && res.message) window.alert(res.message);
      });
    }
    accept.addEventListener('click', function () { answer(true); });
    decline.addEventListener('click', function () { answer(false); });
  }

  // ---------- chat window ----------
  function openChat(chatId) {
    closeChat();
    state.openId = chatId;
    state.lastId = 0;
    var win = el('div', 'dc-win');
    win.setAttribute('role', 'dialog');
    win.setAttribute('aria-label', 'Chat');
    var head = el('div', 'dc-win-head');
    var title = el('div', 'dc-win-title');
    var name = el('strong', null, 'Chat');
    var sub = el('span', 'dc-win-sub');
    title.appendChild(name);
    title.appendChild(sub);
    head.appendChild(title);
    var end = el('button', 'dc-win-end', 'End');
    end.type = 'button';
    end.title = 'End the chat';
    var x = el('button', 'tmsw-x', '×');
    x.type = 'button';
    x.setAttribute('aria-label', 'Close the window');
    head.appendChild(end);
    head.appendChild(x);
    var banner = el('div', 'dc-banner');
    banner.hidden = true;
    var body = el('div', 'dc-win-body');
    var form = el('form', 'dc-win-form');
    var input = el('textarea', 'form-control form-control-sm');
    input.rows = 1;
    input.placeholder = 'Type a message…';
    input.setAttribute('aria-label', 'Message');
    var sendBtn = el('button', 'btn btn-primary btn-sm');
    sendBtn.type = 'submit';
    sendBtn.appendChild(icon('fas fa-paper-plane'));
    sendBtn.setAttribute('aria-label', 'Send');
    form.appendChild(input);
    form.appendChild(sendBtn);
    win.appendChild(head);
    win.appendChild(banner);
    win.appendChild(body);
    win.appendChild(form);
    document.body.appendChild(win);
    state.win = { node: win, name: name, sub: sub, banner: banner, body: body, input: input, send: sendBtn, end: end };
    x.addEventListener('click', closeChat);
    end.addEventListener('click', function () {
      ask('End this chat?').then(function (ok) {
        if (!ok) return;
        api('/direct-chat/close', fd({ chat_id: chatId })).then(function () { load(); poll(); });
      });
    });
    input.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit ? form.requestSubmit() : sendBtn.click(); } });
    var sending = false;
    var noteTimer = null;
    // A short message under the header (e.g. "Slow down") that goes away by itself.
    function flashNote(text) {
      var tip = win.querySelector('.dc-flash') || el('div', 'dc-flash');
      tip.textContent = text;
      if (!tip.parentNode) win.insertBefore(tip, body);
      clearTimeout(noteTimer);
      noteTimer = setTimeout(function () { tip.remove(); }, 4000);
    }
    var lastSent = 0;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var text = input.value.trim();
      // Anti-spam: one message per second, never while the previous one is still sending.
      if (!text || sending || Date.now() - lastSent < 1000) return;
      lastSent = Date.now();
      sending = true;
      sendBtn.disabled = true;
      api('/direct-chat/send', fd({ chat_id: chatId, body: text })).then(function (res) {
        sending = false;
        sendBtn.disabled = false;
        if (!res.success) { flashNote(res.message || 'Not sent.'); return; }
        input.value = '';
        load();
      });
    });
    load();
    state.msgTimer = setInterval(function () { if (!document.hidden) load(); }, 3000);
    setTimeout(function () { input.focus(); }, 60);
  }

  function closeChat() {
    if (state.msgTimer) clearInterval(state.msgTimer);
    state.msgTimer = null;
    if (state.win) state.win.node.remove();
    state.win = null;
    state.openId = 0;
  }

  function load() {
    var w = state.win, chatId = state.openId;
    if (!w) return;
    api('/direct-chat/messages?chat_id=' + chatId + '&after=' + state.lastId).then(function (res) {
      if (!state.win || state.openId !== chatId || !res.success) return;
      var c = res.chat;
      w.name.textContent = c.other_name;
      var info = statusLabel(c);
      if (c.status === 'PENDING' && c.expires_at) info += ' · ' + left(c.expires_at) + ' left';
      w.sub.textContent = info;
      w.node.classList.toggle('dc-urgent-win', c.is_urgent);
      var open = c.status === 'ACTIVE';
      w.input.disabled = w.send.disabled = !open;
      w.end.hidden = !(c.status === 'ACTIVE' || (c.status === 'PENDING' && c.i_am_admin));
      w.end.textContent = c.status === 'PENDING' ? 'Cancel' : 'End';
      var note = '';
      if (c.is_urgent && open) note = 'Urgent chat from Admin about: ' + c.concern;
      else if (c.status === 'PENDING') note = c.i_am_admin ? 'Waiting for ' + c.other_name + ' to accept…' : '';
      else if (c.status === 'DECLINED') note = c.other_name + ' declined' + (c.decline_note ? ': ' + c.decline_note : '.');
      else if (c.status === 'EXPIRED') note = 'No answer within the time given.';
      else if (c.status === 'CLOSED') note = 'This chat has ended.';
      w.banner.hidden = note === '';
      w.banner.textContent = note;
      w.banner.className = 'dc-banner' + (c.is_urgent && open ? ' dc-banner-urgent' : '');
      (res.messages || []).forEach(function (m) {
        state.lastId = Math.max(state.lastId, m.id);
        var row = el('div', 'dc-msg' + (m.mine ? ' dc-mine' : ''));
        row.appendChild(el('div', 'dc-bubble', m.body));
        row.appendChild(el('div', 'dc-time', timeOf(m.at)));
        w.body.appendChild(row);
      });
      if (res.messages && res.messages.length) w.body.scrollTop = w.body.scrollHeight;
    });
  }

  // ---------- polling ----------
  function poll() {
    return api('/direct-chat/poll').then(function (res) {
      if (!res || !res.success) { if (res && res.setup === false) state.setupMissing = true; return; }
      state.offset = parse(res.server_now) - Date.now();
      state.canStart = !!res.can_start;
      state.chats = res.chats || [];
      state.requests = res.requests || [];
      if (state.canStart || state.chats.length) ensureButton();
      updateBadge();
      state.requests.forEach(function (c) { if (!state.shownRequests[c.chat_id]) showRequest(c); });
      // Urgent chats open by themselves (once per browser session).
      state.chats.forEach(function (c) {
        var key = 'dc_urgent_' + c.chat_id;
        var seen = false;
        try { seen = sessionStorage.getItem(key) === '1'; } catch (e) {}
        if (c.is_urgent && c.status === 'ACTIVE' && !c.i_am_admin && !seen && state.openId !== c.chat_id) {
          try { sessionStorage.setItem(key, '1'); } catch (e) {}
          openChat(c.chat_id);
        }
      });
    }).catch(function () {});
  }
  function schedule() {
    clearTimeout(state.pollTimer);
    if (state.setupMissing) return;
    state.pollTimer = setTimeout(function () { poll().then(schedule); }, document.hidden ? 30000 : 8000);
  }

  document.addEventListener('click', function (e) {
    // "New chat" and the search results replace themselves when clicked, so the clicked
    // element is already gone from the page here; that is not a click outside the panel.
    if (!e.target.isConnected) return;
    if (state.panel && !state.panel.contains(e.target) && !(state.btn && state.btn.contains(e.target))) {
      state.panel.remove();
      state.panel = null;
    }
  });
  function start() { poll().then(schedule); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
})();
