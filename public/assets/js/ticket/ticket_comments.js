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
      $section.data('last-comment-id', 0);
      return;
    }

    comments.forEach(function (comment) {
      $list.append(renderComment(comment));
    });

    const lastId = Math.max.apply(null, comments.map(function (c) {
      return parseInt(c.comment_id, 10) || 0;
    }));
    $section.data('last-comment-id', lastId);
    $list.scrollTop($list[0].scrollHeight);
  }

  function appendComments($section, comments) {
    if (!Array.isArray(comments) || comments.length === 0) {
      return;
    }

    const $list = $section.find('.ticket-comments-list');
    $list.find('.ticket-comments-loading, .text-center.text-muted').remove();

    let lastId = parseInt($section.data('last-comment-id'), 10) || 0;
    comments.forEach(function (comment) {
      const cid = parseInt(comment.comment_id, 10) || 0;
      if (cid <= lastId) return;
      $list.append(renderComment(comment));
      lastId = cid;
    });

    $section.data('last-comment-id', lastId);
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
    $section.data('comment-loading', true);

    $.getJSON(baseUrl + '/ticket-comments/fetch', { ticket_id: ticketId })
      .done(function (res) {
        if (res && res.success) {
          renderComments($section, res.comments || []);
          $section.data('comments-ready', true);
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
      })
      .always(function () {
        $section.data('comment-loading', false);
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
          if (res.comment) {
            appendComments($section, [res.comment]);
          } else {
            loadComments($section);
          }
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

  function pollComments() {
    $('.ticket-comments-section').each(function () {
      const $section = $(this);
      const ticketId = parseInt($section.data('ticket-id'), 10);
      const sinceId = parseInt($section.data('last-comment-id'), 10) || 0;
      const baseUrl = getBaseUrl($section);

      if (!ticketId || ticketId <= 0) {
        return;
      }

      if ($section.data('comment-loading') || $section.data('comment-polling')) {
        return;
      }

      $section.data('comment-polling', true);

      const params = { ticket_id: ticketId };
      if (sinceId > 0) {
        params.since_id = sinceId;
      }

      $.getJSON(baseUrl + '/ticket-comments/fetch', params)
        .done(function (res) {
          if (!res || !res.success) {
            return;
          }

          const comments = res.comments || [];
          if (!comments.length) {
            return;
          }

          if (sinceId > 0) {
            appendComments($section, comments);
            return;
          }

          // Empty thread on this side — IT (or anyone) posted the first/new comments
          renderComments($section, comments);
        })
        .always(function () {
          $section.data('comment-polling', false);
        });
    });
  }

  var commentPollStarted = false;

  function startCommentPolling() {
    if (commentPollStarted || !document.querySelector('.ticket-comments-section')) {
      return;
    }
    commentPollStarted = true;
    pollComments();
    window.setInterval(pollComments, 2000);
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
      startCommentPolling();
    },
    poll: pollComments,
  };

  $(function () {
    TicketComments.init('.ticket-comments-section');
    startCommentPolling();
  });
})(window, jQuery);
