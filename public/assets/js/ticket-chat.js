/*
 * Ticket chat head: a floating bubble shown while the user has an active (unresolved) ticket.
 * Drag it left/right along the bottom of the screen; click it to chat about the ticket,
 * messenger style. Messages are the ticket's comments (/ticket-comments/*), so they also
 * appear on the ticket page and trigger the usual notifications.
 */
(function () {
  'use strict';

  if (window.__ticketChatLoaded) return;
  window.__ticketChatLoaded = true;

  var meta = document.querySelector('meta[name="base-url"]');
  var BASE = ((meta && meta.getAttribute('content')) || window.BASE_URL || '').replace(/\/$/, '');
  var HEAD_SIZE = 58;
  var THREADS_EVERY = 30000;
  var MESSAGES_EVERY = 5000;

  var state = {
    accountId: 0,
    threads: [],
    open: false,
    view: 'list',          // 'list' | 'chat'
    ticketId: 0,
    lastCommentId: 0,
    sending: false,
    threadsTimer: null,
    messagesTimer: null,
  };

  // ---------- storage (per browser, per account) ----------
  function store(key, value) {
    try {
      if (value === undefined) return JSON.parse(localStorage.getItem(key) || 'null');
      localStorage.setItem(key, JSON.stringify(value));
    } catch (e) { return null; }
    return value;
  }
  function seenKey() { return 'tc_seen_' + state.accountId; }
  function seenMap() { return store(seenKey()) || {}; }
  function markSeen(ticketId, commentId) {
    var map = seenMap();
    if (!map[ticketId] || commentId > map[ticketId]) {
      map[ticketId] = commentId;
      store(seenKey(), map);
    }
  }

  // ---------- tiny DOM helpers (text only, never innerHTML with data) ----------
  function el(tag, cls, text) {
    var node = document.createElement(tag);
    if (cls) node.className = cls;
    if (text !== undefined && text !== null) node.textContent = text;
    return node;
  }
  function initials(name) {
    var parts = String(name || '?').trim().split(/\s+/);
    return ((parts[0] || '?')[0] + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase();
  }
  function parseDate(s) {
    var d = new Date(String(s || '').replace(' ', 'T'));
    return isNaN(d.getTime()) ? null : d;
  }
  function timeLabel(s) {
    var d = parseDate(s);
    if (!d) return '';
    var now = new Date();
    var sameDay = d.toDateString() === now.toDateString();
    var t = d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    return sameDay ? t : d.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ' ' + t;
  }

  function api(path, options) {
    return fetch(BASE + path, Object.assign({ credentials: 'same-origin', headers: { 'Accept': 'application/json' } }, options || {}))
      .then(function (r) {
        return r.json().catch(function () { return { success: false }; }).then(function (body) {
          body.__status = r.status;
          return body;
        });
      });
  }

  // ---------- build UI ----------
  var head, badge, panel, titleName, titleSub, avatar, backBtn, openLink, body, composer, input, sendBtn, errorBox;

  function build() {
    head = el('button', 'tc-head');
    head.type = 'button';
    head.setAttribute('aria-label', 'Open ticket chat');
    head.setAttribute('title', 'Ticket chat (drag to move)');
    var icon = el('i', 'fas fa-comments');
    icon.setAttribute('aria-hidden', 'true');
    head.appendChild(icon);
    badge = el('span', 'tc-badge');
    badge.hidden = true;
    head.appendChild(badge);

    panel = el('section', 'tc-panel');
    panel.hidden = true;
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-label', 'Ticket chat');

    var header = el('div', 'tc-header');
    backBtn = el('button', 'tc-icon-btn');
    backBtn.type = 'button';
    backBtn.setAttribute('aria-label', 'Back to conversations');
    backBtn.appendChild(el('i', 'fas fa-arrow-left'));
    avatar = el('div', 'tc-avatar', '');
    var title = el('div', 'tc-title');
    titleName = el('strong', null, 'Ticket chat');
    titleSub = el('small', null, '');
    title.appendChild(titleName);
    title.appendChild(titleSub);
    openLink = el('a', 'tc-icon-btn');
    openLink.setAttribute('aria-label', 'Open the ticket page');
    openLink.setAttribute('title', 'Open ticket');
    openLink.appendChild(el('i', 'fas fa-external-link-alt'));
    var closeBtn = el('button', 'tc-icon-btn');
    closeBtn.type = 'button';
    closeBtn.setAttribute('aria-label', 'Close chat');
    closeBtn.appendChild(el('i', 'fas fa-times'));
    header.appendChild(backBtn);
    header.appendChild(avatar);
    header.appendChild(title);
    header.appendChild(openLink);
    header.appendChild(closeBtn);

    body = el('div', 'tc-body');
    errorBox = el('div', 'tc-error');
    errorBox.hidden = true;

    composer = el('form', 'tc-composer');
    input = el('textarea');
    input.rows = 1;
    input.maxLength = 2000;
    input.placeholder = 'Type a message…';
    input.setAttribute('aria-label', 'Message');
    sendBtn = el('button', 'tc-send');
    sendBtn.type = 'submit';
    sendBtn.setAttribute('aria-label', 'Send');
    sendBtn.appendChild(el('i', 'fas fa-paper-plane'));
    composer.appendChild(input);
    composer.appendChild(sendBtn);

    panel.appendChild(header);
    panel.appendChild(body);
    panel.appendChild(errorBox);
    panel.appendChild(composer);
    document.body.appendChild(panel);
    document.body.appendChild(head);

    closeBtn.addEventListener('click', function () { setOpen(false); });
    backBtn.addEventListener('click', function () { showList(); });
    composer.addEventListener('submit', function (e) { e.preventDefault(); send(); });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
    });
    input.addEventListener('input', function () {
      input.style.height = 'auto';
      input.style.height = Math.min(input.scrollHeight, 96) + 'px';
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && state.open) setOpen(false);
    });

    setupDrag();
    if (!state.resizeBound) {
      state.resizeBound = true;
      window.addEventListener('resize', function () { if (head) { placeHead(currentRatio()); placePanel(); } });
    }
  }

  // ---------- drag along the bottom edge ----------
  function currentRatio() {
    var r = parseFloat(store('tc_pos'));
    return isNaN(r) ? 1 : Math.min(1, Math.max(0, r));
  }
  function placeHead(ratio) {
    var max = window.innerWidth - HEAD_SIZE - 16;
    head.style.left = Math.round(16 + (max - 16) * ratio) + 'px';
  }
  function setupDrag() {
    var startX = 0, startLeft = 0, dragging = false, pointerId = null;
    placeHead(currentRatio());

    head.addEventListener('pointerdown', function (e) {
      if (e.button !== 0) return;
      pointerId = e.pointerId;
      startX = e.clientX;
      startLeft = head.offsetLeft;
      dragging = false;
      head.setPointerCapture(pointerId);
    });
    head.addEventListener('pointermove', function (e) {
      if (pointerId !== e.pointerId) return;
      var dx = e.clientX - startX;
      if (!dragging && Math.abs(dx) < 6) return;
      dragging = true;
      head.classList.add('tc-dragging');
      var max = window.innerWidth - HEAD_SIZE - 16;
      head.style.left = Math.min(max, Math.max(16, startLeft + dx)) + 'px';
      placePanel();
    });
    function end(e) {
      if (pointerId !== e.pointerId) return;
      head.releasePointerCapture(pointerId);
      pointerId = null;
      head.classList.remove('tc-dragging');
      if (dragging) {
        var max = window.innerWidth - HEAD_SIZE - 16;
        store('tc_pos', max > 16 ? (head.offsetLeft - 16) / (max - 16) : 1);
      }
    }
    head.addEventListener('pointerup', end);
    head.addEventListener('pointercancel', end);
    head.addEventListener('click', function (e) {
      // A drag ends with a click event; only a real click toggles the chat.
      if (dragging) { dragging = false; e.preventDefault(); return; }
      setOpen(!state.open);
    });
    head.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
        e.preventDefault();
        var r = Math.min(1, Math.max(0, currentRatio() + (e.key === 'ArrowLeft' ? -0.05 : 0.05)));
        store('tc_pos', r);
        placeHead(r);
        placePanel();
      }
    });
  }
  function placePanel() {
    if (!panel || panel.hidden) return;
    var width = panel.offsetWidth || 350;
    var headLeft = head.offsetLeft;
    var left = headLeft + HEAD_SIZE / 2 < window.innerWidth / 2 ? headLeft : headLeft + HEAD_SIZE - width;
    panel.style.left = Math.max(12, Math.min(left, window.innerWidth - width - 12)) + 'px';
  }

  // ---------- open / views ----------
  function setOpen(open) {
    state.open = open;
    panel.hidden = !open;
    head.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (!open) {
      stopMessages();
      return;
    }
    placePanel();
    if (state.threads.length === 1) {
      openChat(state.threads[0].ticket_id);
    } else if (state.view === 'chat' && findThread(state.ticketId)) {
      openChat(state.ticketId);
    } else {
      showList();
    }
  }

  function findThread(id) {
    for (var i = 0; i < state.threads.length; i++) {
      if (state.threads[i].ticket_id === id) return state.threads[i];
    }
    return null;
  }

  function unreadFor(thread, seen) {
    var last = seen[thread.ticket_id] || 0;
    return thread.other_comment_ids.filter(function (id) { return id > last; }).length;
  }

  function showList() {
    state.view = 'list';
    stopMessages();
    backBtn.hidden = true;
    openLink.hidden = true;
    composer.hidden = true;
    errorBox.hidden = true;
    avatar.textContent = '';
    avatar.appendChild(el('i', 'fas fa-ticket-alt'));
    titleName.textContent = 'Ticket chat';
    titleSub.textContent = state.threads.length + ' active ticket' + (state.threads.length === 1 ? '' : 's');
    body.textContent = '';

    var seen = seenMap();
    if (!state.threads.length) {
      body.appendChild(el('div', 'tc-empty', 'No active tickets.'));
      return;
    }
    state.threads.forEach(function (t) {
      var unread = unreadFor(t, seen);
      var row = el('button', 'tc-thread' + (unread ? ' tc-unread' : ''));
      row.type = 'button';
      row.appendChild(el('div', 'tc-avatar', initials(t.other_name)));
      var m = el('div', 'tc-meta');
      var name = el('div', 'tc-name');
      name.appendChild(el('span', null, t.other_name));
      name.appendChild(el('span', null, timeLabel(t.last_activity)));
      m.appendChild(name);
      m.appendChild(el('div', 'tc-sub', t.ticket_number + ' · ' + (t.category || 'Ticket') + ' · ' + t.status));
      m.appendChild(el('div', 'tc-preview', t.last_text
        ? (t.last_is_mine ? 'You: ' : '') + t.last_text
        : 'No messages yet — say hi'));
      row.appendChild(m);
      if (unread) row.appendChild(el('span', 'tc-dot'));
      row.addEventListener('click', function () { openChat(t.ticket_id); });
      body.appendChild(row);
    });
  }

  function openChat(ticketId) {
    var t = findThread(ticketId);
    if (!t) { showList(); return; }
    state.view = 'chat';
    state.ticketId = ticketId;
    state.lastCommentId = 0;
    backBtn.hidden = state.threads.length < 2;
    openLink.hidden = false;
    openLink.href = t.view_url;
    composer.hidden = false;
    errorBox.hidden = true;
    avatar.textContent = initials(t.other_name);
    titleName.textContent = t.other_name;
    titleSub.textContent = t.ticket_number + ' · ' + t.other_role + ' · ' + t.status;
    body.textContent = '';
    var list = el('div', 'tc-messages');
    list.appendChild(el('div', 'tc-empty', 'Loading…'));
    body.appendChild(list);
    loadMessages(true);
    stopMessages();
    state.messagesTimer = setInterval(function () { if (!document.hidden) loadMessages(false); }, MESSAGES_EVERY);
    setTimeout(function () { input.focus(); }, 50);
  }

  function stopMessages() {
    if (state.messagesTimer) clearInterval(state.messagesTimer);
    state.messagesTimer = null;
  }

  function loadMessages(full) {
    var ticketId = state.ticketId;
    var since = full ? 0 : state.lastCommentId;
    api('/ticket-comments/fetch?ticket_id=' + ticketId + (since ? '&since_id=' + since : '')).then(function (res) {
      if (state.view !== 'chat' || state.ticketId !== ticketId) return;
      if (!res.success) {
        showError(res.__status === 403 ? 'You cannot view this ticket chat.' : 'Could not load messages.');
        return;
      }
      composer.hidden = res.canPost === false;
      renderMessages(res.comments || [], full);
    }).catch(function () { showError('Connection problem. Retrying…'); });
  }

  function renderMessages(comments, full) {
    var list = body.querySelector('.tc-messages');
    if (!list) return;
    if (full) list.textContent = '';
    if (full && !comments.length) {
      list.appendChild(el('div', 'tc-empty', 'No messages yet. Ask a question or share an update about this ticket.'));
    }
    var nearBottom = body.scrollHeight - body.scrollTop - body.clientHeight < 80;
    var lastDay = list.getAttribute('data-last-day') || '';
    // Order by id: the server sorts by time, which can disagree with ids after clock changes.
    comments = comments.slice().sort(function (a, b) { return (parseInt(a.comment_id, 10) || 0) - (parseInt(b.comment_id, 10) || 0); });
    comments.forEach(function (c) {
      var id = parseInt(c.comment_id, 10) || 0;
      if (id <= state.lastCommentId) return;
      var empty = list.querySelector('.tc-empty');
      if (empty) empty.remove();
      var d = parseDate(c.created_at);
      var day = d ? d.toDateString() : '';
      if (day && day !== lastDay) {
        list.appendChild(el('div', 'tc-day', d.toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric' })));
        lastDay = day;
      }
      var mine = parseInt(c.account_id, 10) === state.accountId;
      var msg = el('div', 'tc-msg ' + (mine ? 'tc-mine' : 'tc-theirs'));
      if (!mine) msg.appendChild(el('div', 'tc-author', c.author_name + (c.author_role ? ' · ' + c.author_role : '')));
      msg.appendChild(el('div', 'tc-bubble', c.comment_text));
      msg.appendChild(el('div', 'tc-time', timeLabel(c.created_at)));
      list.appendChild(msg);
      state.lastCommentId = Math.max(state.lastCommentId, id);
    });
    list.setAttribute('data-last-day', lastDay);
    if (full || nearBottom) body.scrollTop = body.scrollHeight;
    if (state.lastCommentId) {
      markSeen(state.ticketId, state.lastCommentId);
      updateBadge();
    }
    errorBox.hidden = true;
  }

  function send() {
    var text = input.value.trim();
    if (!text || state.sending) return;
    state.sending = true;
    sendBtn.disabled = true;
    var form = new FormData();
    form.append('ticket_id', String(state.ticketId));
    form.append('comment', text);
    api('/ticket-comments/add', { method: 'POST', body: form }).then(function (res) {
      if (!res.success) {
        showError(res.message || 'Message not sent.');
        return;
      }
      input.value = '';
      input.style.height = 'auto';
      if (res.comment) renderMessages([res.comment], false);
    }).catch(function () {
      showError('Message not sent. Check your connection.');
    }).then(function () {
      state.sending = false;
      sendBtn.disabled = false;
      input.focus();
    });
  }

  function showError(message) {
    errorBox.textContent = message;
    errorBox.hidden = false;
  }

  // ---------- threads + badge ----------
  function updateBadge() {
    var seen = seenMap();
    var total = 0;
    state.threads.forEach(function (t) { total += unreadFor(t, seen); });
    badge.hidden = total === 0;
    badge.textContent = total > 9 ? '9+' : String(total);
    head.setAttribute('aria-label', total ? 'Open ticket chat, ' + total + ' unread' : 'Open ticket chat');
  }

  function refreshThreads() {
    return api('/ticket-chat/threads').then(function (res) {
      if (!res.success) return;
      state.accountId = parseInt(res.account_id, 10) || 0;
      state.threads = res.threads || [];

      // A ticket this browser has not tracked yet: replies after your own last message are unread.
      var map = seenMap();
      var changed = false;
      state.threads.forEach(function (t) {
        if (map[t.ticket_id] === undefined) {
          map[t.ticket_id] = t.my_last_comment_id || 0;
          changed = true;
        }
      });
      if (changed) store(seenKey(), map);

      if (!state.threads.length) {
        // Ticket resolved / cancelled: the chat head goes away.
        if (head) { head.remove(); panel.remove(); head = null; }
        stopMessages();
        return;
      }
      if (!head) build();
      updateBadge();
      if (state.open && state.view === 'list') showList();
      if (state.open && state.view === 'chat' && !findThread(state.ticketId)) showList();
    }).catch(function () { /* offline: try again on the next tick */ });
  }

  function start() {
    refreshThreads();
    state.threadsTimer = setInterval(function () { if (!document.hidden) refreshThreads(); }, THREADS_EVERY);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) refreshThreads(); });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
