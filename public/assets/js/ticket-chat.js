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
  var HELP_WAIT_MS = 60000;          // "Need help from IT personnel" unlocks 1 minute after the guide was sent
  var PING_AFTER_MS = 120000;        // "Are you there?" when IT's message is unanswered for 2 minutes
  var PING_WINDOW_MS = 3600000;      // ... but not for messages older than an hour

  var state = {
    accountId: 0,
    usertype: '',
    serverOffset: 0,       // server clock minus browser clock, in ms
    helpTimer: null,
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

  function serverNow() { return Date.now() + state.serverOffset; }

  // Message text with clickable links (built from text nodes, never innerHTML).
  function bubble(text) {
    var node = el('div', 'tc-bubble');
    var parts = String(text || '').split(/(https?:\/\/[^\s<>"']+)/);
    parts.forEach(function (part, i) {
      if (i % 2 === 1) {
        var a = el('a', null, part);
        a.href = part;
        a.target = '_blank';
        a.rel = 'noopener noreferrer';
        node.appendChild(a);
      } else if (part) {
        node.appendChild(document.createTextNode(part));
      }
    });
    return node;
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
  var head, badge, panel, titleName, titleSub, avatar, backBtn, openLink, body, composer, input, sendBtn, errorBox, helpBar;
  var fileInput, cameraInput, attachChip, pendingFile = null;
  var ACCEPT = '.jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt';
  var MAX_FILE = 10 * 1024 * 1024;

  function sizeLabel(bytes) {
    bytes = parseInt(bytes, 10) || 0;
    return bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
  }

  // Photo thumbnail or file chip for a message (DOM only, no innerHTML with data).
  function attachmentNode(c) {
    if (!c.attachment_url) return null;
    var link = el('a', c.attachment_is_image ? 'tc-attach-img' : 'tc-attach-file');
    link.href = c.attachment_url;
    link.target = '_blank';
    link.rel = 'noopener';
    if (c.attachment_is_image) {
      var img = el('img');
      img.src = c.attachment_url;
      img.alt = c.attachment_name || 'Photo';
      img.loading = 'lazy';
      img.addEventListener('load', function () { if (body) body.scrollTop = body.scrollHeight; });
      img.addEventListener('error', function () {
        // Could not load the preview: keep a plain link to open the photo.
        link.className = 'tc-attach-file';
        link.textContent = '';
        link.appendChild(el('i', 'fas fa-image'));
        var meta = el('span');
        meta.appendChild(el('strong', null, c.attachment_name || 'Photo'));
        meta.appendChild(el('small', null, 'Tap to open'));
        link.appendChild(meta);
      });
      link.appendChild(img);
    } else {
      link.appendChild(el('i', 'fas fa-file-alt'));
      var meta = el('span');
      meta.appendChild(el('strong', null, c.attachment_name || 'File'));
      meta.appendChild(el('small', null, sizeLabel(c.attachment_size)));
      link.appendChild(meta);
    }
    return link;
  }

  function setPendingFile(file) {
    pendingFile = null;
    attachChip.hidden = true;
    attachChip.textContent = '';
    if (!file) return;
    if (file.size > MAX_FILE) { showError('The file is too large (max 10 MB).'); return; }
    pendingFile = file;
    var isImage = /^image\//.test(file.type);
    if (isImage && window.URL && URL.createObjectURL) {
      var thumb = el('img');
      thumb.src = URL.createObjectURL(file);
      attachChip.appendChild(thumb);
    } else {
      attachChip.appendChild(el('i', 'fas fa-file-alt'));
    }
    attachChip.appendChild(el('span', null, file.name + ' (' + sizeLabel(file.size) + ')'));
    var remove = el('button', 'tc-icon-btn');
    remove.type = 'button';
    remove.setAttribute('aria-label', 'Remove attachment');
    remove.appendChild(el('i', 'fas fa-times'));
    remove.addEventListener('click', function () { setPendingFile(null); fileInput.value = ''; cameraInput.value = ''; });
    attachChip.appendChild(remove);
    attachChip.hidden = false;
    errorBox.hidden = true;
    input.focus();
  }

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
    helpBar = el('div', 'tc-help');
    helpBar.hidden = true;

    composer = el('form', 'tc-composer');
    attachChip = el('div', 'tc-attach-chip');
    attachChip.hidden = true;
    fileInput = el('input');
    fileInput.type = 'file';
    fileInput.accept = ACCEPT;
    fileInput.hidden = true;
    cameraInput = el('input');
    cameraInput.type = 'file';
    cameraInput.accept = 'image/*';
    cameraInput.setAttribute('capture', 'environment');
    cameraInput.hidden = true;
    var attachBtn = el('button', 'tc-tool');
    attachBtn.type = 'button';
    attachBtn.title = 'Attach a file or photo';
    attachBtn.setAttribute('aria-label', 'Attach a file or photo');
    attachBtn.appendChild(el('i', 'fas fa-paperclip'));
    var cameraBtn = el('button', 'tc-tool');
    cameraBtn.type = 'button';
    cameraBtn.title = 'Take a photo';
    cameraBtn.setAttribute('aria-label', 'Take a photo');
    cameraBtn.appendChild(el('i', 'fas fa-camera'));
    attachBtn.addEventListener('click', function () { fileInput.click(); });
    cameraBtn.addEventListener('click', function () { cameraInput.click(); });
    function pick(file) {
      if (file && window.TMSShrinkImage) {
        window.TMSShrinkImage(file).then(setPendingFile);
      } else {
        setPendingFile(file || null);
      }
    }
    fileInput.addEventListener('change', function () { pick(fileInput.files[0]); });
    cameraInput.addEventListener('change', function () { pick(cameraInput.files[0]); });
    input = el('textarea');
    input.rows = 1;
    input.maxLength = 2000;
    input.placeholder = 'Type a message… (Ctrl+V pastes a screenshot)';
    input.setAttribute('aria-label', 'Message');
    sendBtn = el('button', 'tc-send');
    sendBtn.type = 'submit';
    sendBtn.setAttribute('aria-label', 'Send');
    sendBtn.appendChild(el('i', 'fas fa-paper-plane'));
    composer.appendChild(fileInput);
    composer.appendChild(cameraInput);
    composer.appendChild(attachBtn);
    composer.appendChild(cameraBtn);
    composer.appendChild(input);
    composer.appendChild(sendBtn);

    panel.appendChild(header);
    panel.appendChild(body);
    panel.appendChild(helpBar);
    panel.appendChild(errorBox);
    panel.appendChild(attachChip);
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
    // Screenshot in the clipboard (Print Screen / Snipping Tool): Ctrl+V or right-click > Paste attaches it.
    input.addEventListener('paste', function (e) {
      var items = (e.clipboardData && e.clipboardData.items) || [];
      for (var i = 0; i < items.length; i++) {
        if (items[i].kind === 'file' && /^image\//.test(items[i].type)) {
          var blob = items[i].getAsFile();
          if (!blob) continue;
          e.preventDefault();
          var d = new Date(), pad2 = function (n) { return (n < 10 ? '0' : '') + n; };
          var stamp = d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate()) + '-' + pad2(d.getHours()) + pad2(d.getMinutes()) + pad2(d.getSeconds());
          var file;
          try { file = new File([blob], 'screenshot-' + stamp + '.png', { type: blob.type || 'image/png' }); } catch (err) { file = blob; }
          pick(file);
          return;
        }
      }
    });
    // Drag a photo or file onto the chat.
    panel.addEventListener('dragover', function (e) { if (state.view === 'chat') e.preventDefault(); });
    panel.addEventListener('drop', function (e) {
      if (state.view !== 'chat' || !e.dataTransfer || !e.dataTransfer.files.length) return;
      e.preventDefault();
      pick(e.dataTransfer.files[0]);
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
    hideHelpBar();
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
    renderHelpBar(t);
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
    if (full) {
      // A full reload redraws everything (two loads can overlap when the chat opens).
      list.textContent = '';
      list.removeAttribute('data-last-day');
      state.lastCommentId = 0;
    }
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
      var system = String(c.author_role || '').toUpperCase() === 'SYSTEM';
      var msg = el('div', 'tc-msg ' + (mine ? 'tc-mine' : 'tc-theirs') + (system ? ' tc-system' : ''));
      if (system) {
        msg.appendChild(el('div', 'tc-author', c.author_name || 'IT Helpdesk'));
      } else if (!mine) {
        msg.appendChild(el('div', 'tc-author', c.author_name + (c.author_role ? ' · ' + c.author_role : '')));
      }
      var file = attachmentNode(c);
      if (file) msg.appendChild(file);
      if (c.comment_text) msg.appendChild(bubble(c.comment_text));
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

  function send(preset) {
    var text = typeof preset === 'string' ? preset : input.value.trim();
    var file = typeof preset === 'string' ? null : pendingFile;
    if ((!text && !file) || state.sending) return;
    state.sending = true;
    sendBtn.disabled = true;
    var form = new FormData();
    form.append('ticket_id', String(state.ticketId));
    form.append('comment', text);
    if (file) form.append('attachment', file, file.name);
    api('/ticket-comments/add', { method: 'POST', body: form }).then(function (res) {
      if (!res.success) {
        showError(res.message || 'Message not sent.');
        return;
      }
      if (typeof preset !== 'string') {
        input.value = '';
        input.style.height = 'auto';
        setPendingFile(null);
        fileInput.value = '';
        cameraInput.value = '';
      }
      if (res.comment) renderMessages([res.comment], false);
    }).catch(function () {
      showError('Message not sent. Check your connection.');
    }).then(function () {
      state.sending = false;
      sendBtn.disabled = false;
      input.focus();
    });
  }

  // ---------- "Need help from IT personnel" (requester) / "Are you there?" (IT) ----------
  function hideHelpBar() {
    if (state.helpTimer) clearInterval(state.helpTimer);
    state.helpTimer = null;
    if (helpBar) { helpBar.hidden = true; helpBar.textContent = ''; }
  }

  function renderHelpBar(t) {
    hideHelpBar();
    if (!t) return;

    if (!t.i_am_requester) {
      // IT side: one tap to check whether the requester is still there.
      if (state.usertype !== 'IT' && state.usertype !== 'ADMIN') return;
      var ping = el('button', 'tc-help-btn tc-help-ghost');
      ping.type = 'button';
      ping.appendChild(el('i', 'fas fa-hand-paper'));
      ping.appendChild(document.createTextNode(' Ask "Are you there?"'));
      ping.addEventListener('click', function () { send('Are you there? Please reply so we can continue with your ticket.'); });
      helpBar.appendChild(ping);
      helpBar.hidden = false;
      return;
    }

    if (!t.manual_sent_at) return;

    if (t.help_requested_at) {
      var done = el('div', 'tc-help-done');
      done.appendChild(el('i', 'fas fa-check-circle'));
      done.appendChild(document.createTextNode(' IT personnel was alerted ' + timeLabel(t.help_requested_at) + '. They will reply here.'));
      helpBar.appendChild(done);
      helpBar.hidden = false;
      return;
    }

    var sent = parseDate(t.manual_sent_at);
    var deadline = sent ? sent.getTime() + HELP_WAIT_MS : 0;
    var info = el('div', 'tc-help-info');
    var bar = el('div', 'tc-help-progress');
    var fill = el('span');
    bar.appendChild(fill);
    var btn = el('button', 'tc-help-btn');
    btn.type = 'button';
    btn.appendChild(el('i', 'fas fa-life-ring'));
    btn.appendChild(document.createTextNode(' Need help from IT personnel'));
    helpBar.appendChild(info);
    helpBar.appendChild(bar);
    helpBar.appendChild(btn);
    helpBar.hidden = false;

    function tick() {
      var left = Math.max(0, deadline - serverNow());
      if (left > 0) {
        var secs = Math.ceil(left / 1000);
        info.textContent = 'Please try the guide above first. The help button unlocks in 0:' + (secs < 10 ? '0' : '') + secs + '.';
        fill.style.width = Math.round(100 - (left / HELP_WAIT_MS) * 100) + '%';
        btn.disabled = true;
        bar.hidden = false;
        return;
      }
      info.textContent = 'Still not working after trying the guide?';
      bar.hidden = true;
      btn.disabled = false;
      if (state.helpTimer) { clearInterval(state.helpTimer); state.helpTimer = null; }
    }
    tick();
    if (deadline - serverNow() > 0) state.helpTimer = setInterval(tick, 1000);

    btn.addEventListener('click', function () {
      btn.disabled = true;
      var form = new FormData();
      form.append('ticket_id', String(t.ticket_id));
      api('/ticket-chat/help', { method: 'POST', body: form }).then(function (res) {
        if (!res.success) {
          showError(res.message || 'Could not alert IT. Please try again.');
          btn.disabled = false;
          return;
        }
        t.help_requested_at = res.help_requested_at || '';
        renderHelpBar(t);
        loadMessages(false);
      }).catch(function () {
        showError('Connection problem. Please try again.');
        btn.disabled = false;
      });
    });
  }

  // Requester popup when IT's last message has gone unanswered for a while.
  function pingKey() { return 'tc_ping_' + state.accountId; }
  function checkPing() {
    if (document.querySelector('.tc-ping')) return;
    var acked = store(pingKey()) || {};
    var now = serverNow();
    var target = null;
    state.threads.forEach(function (t) {
      if (target || !t.i_am_requester || t.last_is_mine || !t.last_comment_id) return;
      if (t.last_role !== 'IT' && t.last_role !== 'ADMIN') return;
      if (acked[t.ticket_id] && acked[t.ticket_id] >= t.last_comment_id) return;
      if (state.open && state.view === 'chat' && state.ticketId === t.ticket_id && !document.hidden) return;
      var at = parseDate(t.last_created_at);
      if (!at) return;
      var age = now - at.getTime();
      var asked = /are you there/i.test(t.last_text || '');
      if (age < PING_WINDOW_MS && (asked || age >= PING_AFTER_MS)) target = t;
    });
    if (target) showPing(target);
  }

  function showPing(t) {
    var acked = store(pingKey()) || {};
    function ack() {
      acked[t.ticket_id] = t.last_comment_id;
      store(pingKey(), acked);
      overlay.remove();
    }
    var overlay = el('div', 'tc-ping');
    overlay.setAttribute('role', 'alertdialog');
    overlay.setAttribute('aria-label', 'Are you there?');
    var card = el('div', 'tc-ping-card');
    var icon = el('div', 'tc-ping-icon');
    icon.appendChild(el('i', 'fas fa-user-clock'));
    card.appendChild(icon);
    card.appendChild(el('h5', null, 'Are you there?'));
    card.appendChild(el('p', null, t.other_name + ' is waiting for your reply on ticket ' + t.ticket_number + '.'));
    var yes = el('button', 'tc-help-btn', 'Yes, I\'m here');
    yes.type = 'button';
    var later = el('button', 'tc-help-btn tc-help-ghost', 'Later');
    later.type = 'button';
    var actions = el('div', 'tc-ping-actions');
    actions.appendChild(later);
    actions.appendChild(yes);
    card.appendChild(actions);
    overlay.appendChild(card);
    document.body.appendChild(overlay);
    yes.focus();

    later.addEventListener('click', ack);
    yes.addEventListener('click', function () {
      ack();
      if (!head) return;
      setOpen(true);
      openChat(t.ticket_id);
      send('Yes, I\'m here.');
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
      state.usertype = String(res.usertype || '').toUpperCase();
      var srv = parseDate(res.server_now);
      if (srv) state.serverOffset = srv.getTime() - Date.now();
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

      var openId = parseInt(res.open_ticket_id, 10) || 0;
      if (openId && findThread(openId)) {
        // Just filed a ticket: open its chat with the troubleshooting guide.
        if (!state.open) setOpen(true);
        if (state.view !== 'chat' || state.ticketId !== openId) openChat(openId);
      }
      checkPing();
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
