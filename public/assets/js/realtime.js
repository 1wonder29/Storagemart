(function (window) {
  'use strict';

  var POLL = {
    notifications: 3000,
    comments: 2000,
    tickets: 4000,
  };

  function baseUrl() {
    if (window.BASE_URL) return String(window.BASE_URL).replace(/\/$/, '');
    if (typeof base !== 'undefined') return String(base).replace(/\/$/, '');
    var meta = document.querySelector('meta[name="base-url"]');
    if (meta && meta.content) return String(meta.content).replace(/\/$/, '');
    return '';
  }

  function getJson(url, params) {
    var qs = '';
    if (params && typeof params === 'object') {
      var parts = [];
      Object.keys(params).forEach(function (key) {
        if (params[key] !== undefined && params[key] !== null && params[key] !== '') {
          parts.push(encodeURIComponent(key) + '=' + encodeURIComponent(params[key]));
        }
      });
      if (parts.length) qs = '?' + parts.join('&');
    }

    return fetch(url + qs, {
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
    }).then(function (res) {
      if (!res.ok) throw new Error('HTTP ' + res.status);
      return res.json();
    });
  }

  function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatNotifDate(dateStr) {
    if (!dateStr) return '';
    var d = new Date(String(dateStr).replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) return escapeHtml(dateStr);
    return d.toLocaleString(undefined, {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
    });
  }

  function updateBadge(count) {
    var bell = document.querySelector('#alertsDropdown');
    if (!bell) return;

    var badge = bell.querySelector('.badge-counter');
    var n = parseInt(count, 10) || 0;

    if (n <= 0) {
      if (badge) badge.remove();
      return;
    }

    var label = n > 9 ? '9+' : String(n);
    if (!badge) {
      badge = document.createElement('span');
      badge.className = 'badge badge-danger badge-counter';
      bell.appendChild(badge);
    }
    badge.textContent = label;
  }

  function renderNotificationItem(n) {
    var isRead = parseInt(n.is_read, 10) === 1;
    var readClass = isRead ? 'notification-read' : 'notification-unread';
    var bg = escapeHtml(n.bg_color || 'primary');
    var icon = escapeHtml(n.icon || 'fa-bell');
    var url = escapeHtml(n.action_url || '#');
    var id = parseInt(n.id, 10) || 0;
    var message = escapeHtml(n.message || '');
    var dateLabel = formatNotifDate(n.created_at);

    return (
      '<a class="dropdown-item d-flex align-items-center notification-item ' + readClass + '" ' +
        'href="' + url + '" data-id="' + id + '">' +
        '<div class="mr-3">' +
          '<div class="icon-circle bg-' + bg + '">' +
            '<i class="fas ' + icon + ' text-white"></i>' +
          '</div>' +
        '</div>' +
        '<div>' +
          '<div class="small text-gray-500">' + dateLabel + '</div>' +
          '<div class="font-weight-bold">' + message + '</div>' +
        '</div>' +
      '</a>'
    );
  }

  function renderNotificationList(notifications) {
    if (!Array.isArray(notifications) || notifications.length === 0) {
      return '<div class="dropdown-item text-center small text-gray-500 py-2">No notifications</div>';
    }
    return '<div class="notification-scroll">' +
      notifications.map(renderNotificationItem).join('') +
    '</div>';
  }

  function handleNotificationClick(e) {
    var item = e.target.closest('.notification-item');
    if (!item) return;

    e.preventDefault();
    var url = item.getAttribute('href') || '#';
    var notifId = item.dataset.id;
    var root = baseUrl();

    if (!notifId || notifId === '0') {
      navigateAfterNotificationRead(url);
      return;
    }

    fetch(root + '/notifications/read', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id=' + encodeURIComponent(notifId),
    }).then(function () {
      item.classList.remove('notification-unread');
      item.classList.add('notification-read');
      var badge = document.querySelector('#alertsDropdown .badge-counter');
      if (badge) {
        var n = parseInt(badge.textContent, 10);
        if (Number.isFinite(n)) {
          var next = Math.max(0, n - 1);
          if (next <= 0) badge.remove();
          else badge.textContent = String(next);
        }
      }
      navigateAfterNotificationRead(url);
    }).catch(function () {
      navigateAfterNotificationRead(url);
    });
  }

  function navigateAfterNotificationRead(url) {
    if (!url || url === '#') return;

    if (url.indexOf('/tickets/rate') !== -1) {
      var body = document.getElementById('rateTicketModalBody');
      if (body && window.jQuery) {
        body.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
        window.jQuery('#rateTicketModal').modal('show');
        fetch(url, { credentials: 'same-origin' })
          .then(function (res) { return res.text(); })
          .then(function (html) { body.innerHTML = html; })
          .catch(function () {
            body.innerHTML = '<div class="text-danger p-3">Unable to load rating form.</div>';
          });
        return;
      }
    }

    window.location.href = url;
  }

  function pollNotifications() {
    var dropdown = document.querySelector('[aria-labelledby="alertsDropdown"]');
    if (!dropdown) return;

    getJson(baseUrl() + '/realtime/notifications')
      .then(function (res) {
        if (!res || !res.success) return;
        updateBadge(res.count);

        var header = dropdown.querySelector('.dropdown-header');
        var showAll = dropdown.querySelector('a[href*="/notifications"]');
        var html = renderNotificationList(res.notifications || []);

        Array.from(dropdown.children).forEach(function (child) {
          if (child === header || child === showAll) return;
          child.remove();
        });

        var temp = document.createElement('div');
        temp.innerHTML = html;
        while (temp.firstChild) {
          if (showAll) dropdown.insertBefore(temp.firstChild, showAll);
          else dropdown.appendChild(temp.firstChild);
        }
      })
      .catch(function () { /* silent */ });
  }

  function statusClass(status) {
    var s = String(status || '').toLowerCase();
    if (s === 'pending') return 'status-pending';
    if (s === 'in progress') return 'status-in-progress';
    if (s === 'on hold') return 'status-on-hold';
    if (s === 'resolved') return 'status-resolved';
    if (s === 'closed') return 'status-closed';
    if (s === 'reopened') return 'status-reopened';
    return 'status-default';
  }

  function priorityClass(priority) {
    var p = String(priority || '').toLowerCase();
    if (p === 'high') return 'priority-high';
    if (p === 'medium') return 'priority-medium';
    if (p === 'low') return 'priority-low';
    return '';
  }

  function updateTicketRow(ticket) {
    var id = parseInt(ticket.ticket_id, 10);
    if (!id) return false;

    var row = document.querySelector('tr[data-ticket-id="' + id + '"]');
    if (!row) return false;

    if (ticket.status) {
      row.setAttribute('data-status', String(ticket.status).toLowerCase());
      row.querySelectorAll('[data-ticket-status], .status-badge').forEach(function (statusEl) {
        statusEl.textContent = ticket.status;
        if (statusEl.classList.contains('status-badge')) {
          statusEl.className = 'status-badge ' + statusClass(ticket.status) + (statusEl.classList.contains('mt-1') ? ' mt-1' : '');
        }
      });
    }

    if (ticket.priority) {
      row.setAttribute('data-priority', String(ticket.priority).toLowerCase());
      row.querySelectorAll('[data-ticket-priority], .priority-pill').forEach(function (priorityEl) {
        if (priorityEl.classList.contains('priority-pill')) {
          priorityEl.className = 'priority-pill ' + priorityClass(ticket.priority);
          var icon = priorityEl.querySelector('i');
          priorityEl.textContent = '';
          if (icon) priorityEl.appendChild(icon);
          priorityEl.appendChild(document.createTextNode(' ' + ticket.priority));
        } else {
          priorityEl.textContent = ticket.priority;
        }
      });
    }

    if (typeof ticket.assigned_to_name !== 'undefined') {
      var assigneeCell = row.querySelector('.assignee-hint, .not-assigned-label');
      if (assigneeCell) {
        var name = String(ticket.assigned_to_name || '').trim();
        if (name) {
          assigneeCell.className = 'assignee-hint';
          assigneeCell.innerHTML = '<i class="fas fa-user-cog mr-1"></i>' + escapeHtml(name);
        } else {
          assigneeCell.className = 'not-assigned-label';
          assigneeCell.textContent = 'Unassigned';
        }
      }
    }

    row.classList.add('ticket-row-updated');
    setTimeout(function () { row.classList.remove('ticket-row-updated'); }, 2000);
    return true;
  }

  function updateTicketDetail(ticket) {
    var wrap = document.querySelector('[data-realtime-ticket-detail]');
    if (!wrap || !ticket) return;

    var detailId = parseInt(wrap.getAttribute('data-ticket-id'), 10);
    if (detailId !== parseInt(ticket.ticket_id, 10)) return;

    if (ticket.status) {
      wrap.querySelectorAll('[data-ticket-status]').forEach(function (el) {
        el.textContent = ticket.status;
      });
    }

    if (ticket.priority) {
      wrap.querySelectorAll('[data-ticket-priority]').forEach(function (el) {
        el.textContent = ticket.priority;
      });
    }
  }

  function showTicketRefreshBanner() {
    if (document.getElementById('ticketRealtimeBanner')) return;

    var banner = document.createElement('div');
    banner.id = 'ticketRealtimeBanner';
    banner.className = 'alert alert-info alert-dismissible fade show shadow-sm';
    banner.style.cssText = 'position:fixed;bottom:1.25rem;right:1.25rem;z-index:1050;max-width:320px;margin:0;';
    banner.innerHTML =
      '<i class="fas fa-sync-alt mr-1"></i> Ticket list updated. ' +
      '<button type="button" class="btn btn-sm btn-primary ml-2" id="ticketRealtimeRefreshBtn">Refresh</button>' +
      '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';

    document.body.appendChild(banner);
    banner.querySelector('#ticketRealtimeRefreshBtn').addEventListener('click', function () {
      window.location.reload();
    });
    banner.querySelector('.close').addEventListener('click', function () {
      banner.remove();
    });
  }

  var ticketPollSince = new Date().toISOString().slice(0, 19).replace('T', ' ');

  function pollTickets() {
    var hasTable = document.querySelector('.ticket-realtime-table, tr[data-ticket-id]');
    var detail = document.querySelector('[data-realtime-ticket-detail]');

    if (detail) {
      var ticketId = parseInt(detail.getAttribute('data-ticket-id'), 10);
      if (ticketId > 0) {
        getJson(baseUrl() + '/realtime/tickets', { ticket_id: ticketId })
          .then(function (res) {
            if (res && res.success && res.ticket) {
              updateTicketDetail(res.ticket);
            }
          })
          .catch(function () { /* silent */ });
      }
      return;
    }

    if (!hasTable) return;

    getJson(baseUrl() + '/realtime/tickets', { since: ticketPollSince })
      .then(function (res) {
        if (!res || !res.success) return;
        if (res.server_time) ticketPollSince = res.server_time;

        var tickets = res.tickets || [];
        if (!tickets.length) return;

        var anyRowUpdated = false;
        tickets.forEach(function (ticket) {
          if (updateTicketRow(ticket)) anyRowUpdated = true;
        });

        if (!anyRowUpdated && tickets.length > 0) {
          showTicketRefreshBanner();
        }
      })
      .catch(function () { /* silent */ });
  }

  function pollComments() {
    if (window.TicketComments && typeof window.TicketComments.poll === 'function') {
      window.TicketComments.poll();
    }
  }

  function startPolling() {
    if (!baseUrl()) return;

    document.addEventListener('click', handleNotificationClick);

    pollNotifications();
    window.setInterval(pollNotifications, POLL.notifications);

    function startCommentPolling() {
      if (!document.querySelector('.ticket-comments-section')) return;
      pollComments();
      window.setInterval(pollComments, POLL.comments);
    }

    if (document.querySelector('.ticket-comments-section')) {
      startCommentPolling();
    } else {
      document.addEventListener('DOMContentLoaded', startCommentPolling);
    }

    if (document.querySelector('.ticket-realtime-table, tr[data-ticket-id], [data-realtime-ticket-detail]')) {
      window.setTimeout(pollTickets, 3000);
      window.setInterval(pollTickets, POLL.tickets);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startPolling);
  } else {
    startPolling();
  }
})(window);
