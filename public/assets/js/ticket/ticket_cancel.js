(function (window, $) {
  'use strict';

  function getBaseUrl() {
    if (window.TICKET_CANCEL_BASE_URL) {
      return String(window.TICKET_CANCEL_BASE_URL).replace(/\/$/, '');
    }
    if (window.BASE_URL) {
      return String(window.BASE_URL).replace(/\/$/, '');
    }
    if (typeof base !== 'undefined') {
      return String(base).replace(/\/$/, '');
    }
    return '';
  }

  function showAlert(type, message) {
    const $alert = $('#cancelTicketAlert');
    if (!$alert.length) return;
    $alert.removeClass('d-none alert-success alert-danger')
      .addClass('alert-' + type)
      .text(message);
  }

  function hideAlert() {
    $('#cancelTicketAlert').addClass('d-none').removeClass('alert-success alert-danger').text('');
  }

  function openCancelModal(ticketId, ticketNumber) {
    const $modal = $('#cancelTicketModal');
    if (!$modal.length) {
      alert('Cancel ticket is not available on this page.');
      return;
    }

    hideAlert();
    $('#cancel_ticket_id').val(ticketId);
    $('#cancel_reason').val('');
    $('#cancelTicketNumberLabel').text(ticketNumber ? ticketNumber : ('Ticket #' + ticketId));
    $modal.modal('show');
  }

  function submitCancel(e) {
    e.preventDefault();

    const baseUrl = getBaseUrl();
    const ticketId = parseInt($('#cancel_ticket_id').val(), 10);
    const reason = ($('#cancel_reason').val() || '').trim();
    const $btn = $('#cancelTicketSubmitBtn');

    hideAlert();

    if (!ticketId) {
      showAlert('danger', 'Invalid ticket.');
      return;
    }

    if (!reason) {
      showAlert('danger', 'Please provide a reason for cancellation.');
      return;
    }

    $btn.prop('disabled', true);

    $.ajax({
      url: baseUrl + '/tickets/cancel',
      method: 'POST',
      dataType: 'json',
      data: {
        ticket_id: ticketId,
        cancel_reason: reason,
      },
    })
      .done(function (res) {
        if (res && res.success) {
          showAlert('success', res.message || 'Ticket cancelled.');
          setTimeout(function () {
            window.location.reload();
          }, 800);
        } else {
          showAlert('danger', (res && res.message) ? res.message : 'Failed to cancel ticket.');
          $btn.prop('disabled', false);
        }
      })
      .fail(function (xhr) {
        let msg = 'Failed to cancel ticket.';
        try {
          const res = xhr.responseJSON;
          if (res && res.message) msg = res.message;
        } catch (err) { /* ignore */ }
        showAlert('danger', msg);
        $btn.prop('disabled', false);
      });
  }

  $(function () {
    $(document).on('click', '.cancelTicketBtn', function () {
      const ticketId = parseInt($(this).data('ticket-id'), 10);
      const ticketNumber = $(this).data('ticket-num') || '';
      openCancelModal(ticketId, ticketNumber);
    });

    $('#cancelTicketForm').on('submit', submitCancel);

    $('#cancelTicketModal').on('hidden.bs.modal', function () {
      hideAlert();
      $('#cancel_reason').val('');
      $('#cancelTicketSubmitBtn').prop('disabled', false);
    });
  });

  window.TicketCancel = {
    open: openCancelModal,
  };
})(window, jQuery);
