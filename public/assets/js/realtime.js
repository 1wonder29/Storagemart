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

  function syncNotificationHeaderState(dropdown, count) {
    if (!dropdown) return;

    var header = dropdown.querySelector('.notification-dropdown-header');
    if (!header) return;

    var n = parseInt(count, 10) || 0;
    var actions = header.querySelector('.notification-dropdown-actions');

    if (n <= 0) {
      if (actions) actions.remove();
      return;
    }

    if (!actions) {
      actions = document.createElement('div');
      actions.className = 'notification-dropdown-actions';
      header.appendChild(actions);
    }

    var btn = actions.querySelector('.notification-mark-all-read');
    if (!btn) {
      btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'notification-mark-all-read';
      btn.setAttribute('aria-label', 'Mark all notifications as read');
      btn.textContent = 'Mark all read';
      actions.insertBefore(btn, actions.firstChild);
    }
    btn.disabled = false;

    var pill = actions.querySelector('.notification-unread-pill');
    if (!pill) {
      pill = document.createElement('span');
      pill.className = 'notification-unread-pill';
      actions.appendChild(pill);
    }
    pill.textContent = (n > 9 ? '9+' : String(n)) + ' new';
    pill.style.display = '';
  }

  function applyAllNotificationsRead() {
    document.querySelectorAll('.notification-item.notification-unread').forEach(function (item) {
      item.classList.remove('notification-unread');
      item.classList.add('notification-read');
    });

    updateBadge(0);

    document.querySelectorAll('.notification-unread-pill').forEach(function (pill) {
      pill.style.display = 'none';
    });

    document.querySelectorAll('.notification-mark-all-read').forEach(function (btn) {
      btn.remove();
    });

    var pageUnread = document.getElementById('alertsUnreadStat');
    var pageRead = document.getElementById('alertsReadStat');
    if (pageUnread && pageRead) {
      var unreadN = parseInt(pageUnread.textContent, 10) || 0;
      var readN = parseInt(pageRead.textContent, 10) || 0;
      if (unreadN > 0) {
        pageRead.textContent = String(readN + unreadN);
        pageUnread.textContent = '0';
      }
    }

    var pagePill = document.getElementById('alertsUnreadPill');
    if (pagePill) {
      pagePill.classList.add('d-none');
    }

    lastNotificationPayload = '';
  }

  function handleMarkAllReadClick(e) {
    var btn = e.target.closest('.notification-mark-all-read');
    if (!btn || btn.disabled) return;

    e.preventDefault();
    e.stopPropagation();

    btn.disabled = true;

    fetch(baseUrl() + '/notifications/read-all', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    })
      .then(function (res) {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      })
      .then(function (res) {
        if (res && res.success) {
          applyAllNotificationsRead();
        } else {
          btn.disabled = false;
        }
      })
      .catch(function () {
        btn.disabled = false;
      });
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
      '<a class="notification-item ' + readClass + '" href="' + url + '" data-id="' + id + '">' +
        '<span class="notification-indicator" aria-hidden="true"></span>' +
        '<div class="notification-icon bg-' + bg + '">' +
          '<i class="fas ' + icon + '"></i>' +
        '</div>' +
        '<div class="notification-body">' +
          '<div class="notification-message">' + message + '</div>' +
          '<div class="notification-time">' + dateLabel + '</div>' +
        '</div>' +
      '</a>'
    );
  }

  function renderNotificationList(notifications) {
    if (!Array.isArray(notifications) || notifications.length === 0) {
      return '<div class="notification-empty"><i class="fas fa-check-circle d-block mb-2"></i>You\'re all caught up</div>';
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
      var wasUnread = item.classList.contains('notification-unread');
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
      if (wasUnread) {
        var pageUnread = document.getElementById('alertsUnreadStat');
        if (pageUnread) {
          var unreadN = parseInt(pageUnread.textContent, 10);
          if (Number.isFinite(unreadN)) {
            var unreadNext = Math.max(0, unreadN - 1);
            pageUnread.textContent = String(unreadNext);
            var pageRead = document.getElementById('alertsReadStat');
            if (pageRead) {
              var readN = parseInt(pageRead.textContent, 10);
              if (Number.isFinite(readN)) pageRead.textContent = String(readN + 1);
            }
            var pagePill = document.getElementById('alertsUnreadPill');
            if (pagePill) {
              if (unreadNext <= 0) pagePill.classList.add('d-none');
              else pagePill.textContent = (unreadNext > 9 ? '9+' : String(unreadNext)) + ' unread';
            }
          }
        }
      }
      navigateAfterNotificationRead(url);
    }).catch(function () {
      navigateAfterNotificationRead(url);
    });
  }

  function resolveNotificationUrl(url) {
    if (!url || url === '#') return url;
    if (url.indexOf('/tickets/rate') !== -1) {
      return url.replace('/tickets/rate', '/tickets/view');
    }
    return url;
  }

  function navigateAfterNotificationRead(url) {
    url = resolveNotificationUrl(url);
    if (!url || url === '#') return;
    window.location.href = url;
  }

  var lastNotificationPayload = '';

  function pollNotifications() {
    var dropdown = document.querySelector('[aria-labelledby="alertsDropdown"]');
    if (!dropdown) return;

    getJson(baseUrl() + '/realtime/notifications')
      .then(function (res) {
        if (!res || !res.success) return;
        updateBadge(res.count);
        syncNotificationHeaderState(dropdown, res.count);

        var payload = JSON.stringify(res.notifications || []);
        if (payload === lastNotificationPayload) return;
        lastNotificationPayload = payload;

        var scrollEl = dropdown.querySelector('.notification-scroll');
        var savedScrollTop = scrollEl ? scrollEl.scrollTop : 0;

        var header = dropdown.querySelector('.notification-dropdown-header, .dropdown-header');
        var footer = dropdown.querySelector('.notification-dropdown-footer');
        var showAll = footer ? footer.querySelector('a[href*="/notifications"]') : dropdown.querySelector('a[href*="/notifications"]');
        var html = renderNotificationList(res.notifications || []);

        Array.from(dropdown.children).forEach(function (child) {
          if (child === header || child === footer || child === showAll) return;
          child.remove();
        });

        var temp = document.createElement('div');
        temp.innerHTML = html;
        while (temp.firstChild) {
          if (footer) dropdown.insertBefore(temp.firstChild, footer);
          else if (showAll) dropdown.insertBefore(temp.firstChild, showAll);
          else dropdown.appendChild(temp.firstChild);
        }

        var newScrollEl = dropdown.querySelector('.notification-scroll');
        if (newScrollEl && savedScrollTop > 0) {
          newScrollEl.scrollTop = savedScrollTop;
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

  function removeTicketRow(row, table) {
    if (!row) return;
    if (window.jQuery && jQuery.fn.DataTable && table && jQuery.fn.DataTable.isDataTable(table)) {
      jQuery(table).DataTable().row(row).remove().draw(false);
    } else {
      row.remove();
    }
  }

  function updateTicketRow(ticket) {
    var id = parseInt(ticket.ticket_id, 10);
    if (!id) return false;

    var row = document.querySelector('tr[data-ticket-id="' + id + '"]');
    if (!row) return false;

    var table = row.closest('.ticket-realtime-table');
    var keepStatus = table ? table.getAttribute('data-realtime-keep-status') : '';

    if (ticket.status && keepStatus) {
      var allowed = keepStatus.split(',').map(function (s) {
        return s.trim().toLowerCase();
      }).filter(Boolean);
      var statusLower = String(ticket.status).toLowerCase();
      if (allowed.length && allowed.indexOf(statusLower) === -1) {
        removeTicketRow(row, table);
        return true;
      }
    }

    if (ticket.status) {
      row.setAttribute('data-status', String(ticket.status).toLowerCase());
      row.querySelectorAll('[data-ticket-status], .status-badge').forEach(function (statusEl) {
        if (statusEl.closest('.cancelTicketBtn') || statusEl.classList.contains('cancelTicketBtn')) {
          return;
        }
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

  var ticketTableRefreshTimer = null;

  function refreshTicketTableBody(table) {
    if (!table) return;

    var url = table.getAttribute('data-realtime-refresh-url');
    if (!url || table.getAttribute('data-realtime-refreshing') === '1') return;

    table.setAttribute('data-realtime-refreshing', '1');

    fetch(url, {
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then(function (res) {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.text();
      })
      .then(function (html) {
        var tbody = table.querySelector('tbody');
        if (!tbody) return;

        if (window.jQuery && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable(table)) {
          jQuery(table).DataTable().destroy();
        }

        tbody.innerHTML = html;

        document.dispatchEvent(new CustomEvent('tms:ticket-table-refreshed', {
          detail: { table: table, tableId: table.id || '' },
        }));
      })
      .catch(function () { /* silent */ })
      .finally(function () {
        table.removeAttribute('data-realtime-refreshing');
      });
  }

  function scheduleTicketTableRefresh(table) {
    if (!table) return;
    if (ticketTableRefreshTimer) {
      window.clearTimeout(ticketTableRefreshTimer);
    }
    ticketTableRefreshTimer = window.setTimeout(function () {
      ticketTableRefreshTimer = null;
      refreshTicketTableBody(table);
    }, 300);
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

        var table = document.querySelector('.ticket-realtime-table[data-realtime-refresh-url]');
        var needsBodyRefresh = false;

        tickets.forEach(function (ticket) {
          if (!updateTicketRow(ticket)) {
            needsBodyRefresh = true;
          }
        });

        if (needsBodyRefresh && table) {
          scheduleTicketTableRefresh(table);
        }
      })
      .catch(function () { /* silent */ });
  }

  function startPolling() {
    if (!baseUrl()) return;

    document.addEventListener('click', handleNotificationClick);
    document.addEventListener('click', handleMarkAllReadClick);

    pollNotifications();
    window.setInterval(pollNotifications, POLL.notifications);

    // Comment polling is handled by ticket_comments.js (needs jQuery + initial load)

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
