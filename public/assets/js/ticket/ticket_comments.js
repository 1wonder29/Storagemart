(function (window, $) {
  'use strict';

  function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(String(dateStr).replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) return escapeHtml(dateStr);
    return d.toLocaleString(undefined, {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
    });
  }

  function roleBadgeClass(role) {
    const r = String(role || '').toUpperCase();
    if (r === 'ADMIN') return 'badge-danger';
    if (r === 'IT') return 'badge-primary';
    if (r === 'EMPLOYEE') return 'badge-success';
    if (r === 'HEAD') return 'badge-dark';
    if (r === 'HR') return 'badge-warning';
    if (r === 'OM' || r === 'HOM') return 'badge-secondary';
    if (r === 'AOM') return 'badge-info';
    return 'badge-secondary';
  }

  function renderComment(comment) {
    const role = escapeHtml(comment.author_role || 'User');
    const name = escapeHtml(comment.author_name || 'Unknown');
    const text = escapeHtml(comment.comment_text || '').replace(/\n/g, '<br>');
    const date = formatDate(comment.created_at);

    return (
      '<div class="ticket-comment-item mb-3 pb-3 border-bottom">' +
        '<div class="d-flex justify-content-between align-items-start mb-1">' +
          '<div>' +
            '<strong class="text-gray-800">' + name + '</strong> ' +
            '<span class="badge ' + roleBadgeClass(comment.author_role) + ' badge-pill ml-1">' + role + '</span>' +
          '</div>' +
          '<small class="text-muted text-nowrap ml-2">' + date + '</small>' +
        '</div>' +
        '<div class="text-gray-700 small">' + text + '</div>' +
      '</div>'
    );
  }

  function getBaseUrl($section) {
    const fromData = $section.data('base-url');
    if (fromData) return String(fromData).replace(/\/$/, '');
    if (window.BASE_URL) return String(window.BASE_URL).replace(/\/$/, '');
    if (typeof base !== 'undefined') return String(base).replace(/\/$/, '');
    return '';
  }

  function showAlert($section, type, message) {
    const $alert = $section.find('.ticket-comments-alert');
    if (!$alert.length) return;
    $alert.removeClass('d-none alert-success alert-danger')
      .addClass('alert-' + type)
      .text(message);
  }

  function hideAlert($section) {
    $section.find('.ticket-comments-alert').addClass('d-none').removeClass('alert-success alert-danger').text('');
  }

  function renderComments($section, comments) {
    const $list = $section.find('.ticket-comments-list');
    $list.empty();

    if (!Array.isArray(comments) || comments.length === 0) {
      $list.html('<div class="text-center text-muted py-3"><i class="fas fa-comment-slash"></i> No comments yet. Start the conversation.</div>');
      return;
    }

    comments.forEach(function (comment) {
      $list.append(renderComment(comment));
    });

    $list.scrollTop($list[0].scrollHeight);
  }

  function loadComments($section) {
    const ticketId = parseInt($section.data('ticket-id'), 10);
    const baseUrl = getBaseUrl($section);

    if (!ticketId || ticketId <= 0) {
      renderComments($section, []);
      return;
    }

    const $list = $section.find('.ticket-comments-list');
    $list.html('<div class="text-center text-muted py-3 ticket-comments-loading"><i class="fas fa-spinner fa-spin"></i> Loading comments...</div>');

    $.getJSON(baseUrl + '/ticket-comments/fetch', { ticket_id: ticketId })
      .done(function (res) {
        if (res && res.success) {
          renderComments($section, res.comments || []);
          if (typeof res.canPost !== 'undefined') {
            $section.data('can-post', res.canPost ? '1' : '0');
            if (!res.canPost) {
              $section.find('.ticket-comments-form-wrap').hide();
            } else {
              $section.find('.ticket-comments-form-wrap').show();
            }
          }
        } else {
          $list.html('<div class="text-center text-danger py-3">Unable to load comments.</div>');
        }
      })
      .fail(function () {
        $list.html('<div class="text-center text-danger py-3">Failed to load comments.</div>');
      });
  }

  function submitComment($section) {
    const ticketId = parseInt($section.data('ticket-id'), 10);
    const canPost = String($section.data('can-post')) === '1';
    const baseUrl = getBaseUrl($section);
    const $input = $section.find('.ticket-comment-input');
    const $btn = $section.find('.ticket-comment-submit');
    const comment = ($input.val() || '').trim();

    hideAlert($section);

    if ($section.data('comment-submitting')) {
      return;
    }

    if (!canPost) {
      showAlert($section, 'danger', 'You cannot post comments on this ticket.');
      return;
    }

    if (!comment) {
      showAlert($section, 'danger', 'Please enter a comment.');
      return;
    }

    $section.data('comment-submitting', true);
    $btn.prop('disabled', true);

    $.ajax({
      url: baseUrl + '/ticket-comments/add',
      method: 'POST',
      dataType: 'json',
      data: { ticket_id: ticketId, comment: comment },
    })
      .done(function (res) {
        if (res && res.success) {
          $input.val('');
          showAlert($section, 'success', res.message || 'Comment posted.');
          loadComments($section);
          setTimeout(function () { hideAlert($section); }, 2500);
        } else {
          showAlert($section, 'danger', (res && res.message) ? res.message : 'Failed to post comment.');
        }
      })
      .fail(function (xhr) {
        let msg = 'Failed to post comment.';
        try {
          const res = xhr.responseJSON;
          if (res && res.message) msg = res.message;
        } catch (e) { /* ignore */ }
        showAlert($section, 'danger', msg);
      })
      .always(function () {
        $btn.prop('disabled', false);
        $section.data('comment-submitting', false);
      });
  }

  function bindSection($section) {
    if (!$section.length || $section.data('comments-init')) {
      return;
    }

    $section.data('comments-init', true);
    $section.on('submit.ticketComments', '.ticket-comments-form', function (e) {
      e.preventDefault();
      submitComment($section);
    });
  }

  function initSection($section) {
    bindSection($section);
    loadComments($section);
  }

  window.TicketComments = {
    init: function (selector) {
      $(selector).each(function () {
        initSection($(this));
      });
    },
    load: function (selector, ticketId) {
      const $section = $(selector);
      if (!$section.length) {
        return;
      }

      if (ticketId) {
        $section.data('ticket-id', ticketId);
      }

      bindSection($section);
      loadComments($section);
    },
  };

  $(function () {
    TicketComments.init('.ticket-comments-section');
  });
})(window, jQuery);
