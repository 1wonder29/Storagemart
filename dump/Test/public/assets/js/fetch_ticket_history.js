  // --- Main list table (page) ---
  document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('#logsTicket')) {
      new DataTable('#logsTicket', {
        fixedHeader: { header: true },
        order: [],
        columnDefs: [
          {
            targets: [2,3, 4, 5], // Category, Priority, Date Filed
            columnControl: ["order", ["searchList","spacer","orderAsc","orderDesc","orderClear"]],
          },
          {
            targets: [0, 1, 6], // Employee, Branch, Action
            columnControl: ["order", ["search"]],
          },
        ],
        ordering: { indicators: false, handler: false },
      });
    }
  });

  // --- Modal history table (inside modal) ---
  // Replace your current viewBtn handler with this ready-to-paste block
let historyDT = null;

$(document).on('click', '.viewBtn', function () {
  const id = $(this).data('ticketid');
  console.log('View clicked — ticket id =', id);

  if (!id || parseInt(id, 10) <= 0) {
    alert('Invalid ticket id. Please refresh and try again.');
    return;
  }

  // Fill header fields (optional)
  $('#ticket_number').val($(this).data('ticketnum') || '');
  $('#employee').val($(this).data('employee') || '');
  $('#priority').val($(this).data('priority') || '');
  $('#status').val($(this).data('status') || '');

  // 1) Clear previous rows and safely destroy previous DataTable
  try {
    if (historyDT && typeof historyDT.destroy === 'function') {
      historyDT.destroy();
    }
  } catch (err) {
    console.warn('Error destroying previous DataTable instance:', err);
  }
  historyDT = null;
  $('#ticketHistoryTable tbody').empty();

  // 2) Show loading row and open modal (so user sees immediate feedback)
  $('#ticketHistoryTable tbody').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
  $('#viewTicketModal').modal('show');

  // 3) Fetch history
  $.ajax({
    url: window.BASE_URL + '/admin/tickets/history',
    method: 'GET',
    data: { ticket_id: id },
    dataType: 'json',
    cache: false,
    timeout: 10000,
    success: function (history) {
      console.log('fetch_ticket_history response:', history);

      // Ensure the modal is still open (optional)
      if (!$('#viewTicketModal').hasClass('show')) {
        // modal closed by user; do nothing
      }

      // Validate response
      if (!Array.isArray(history) || history.length === 0) {
        $('#ticketHistoryTable tbody').html('<tr><td colspan="5" class="text-center">No history found.</td></tr>');
      } else {
        const rowsHtml = history.map(row => {
          const action = row.action_details || '';
          const tech = row.assigned_to || '';
          const oldS = row.old_status || '';
          const newS = row.new_status || '';
          const date = row.date_logged || '';
          return `<tr>
                    <td>${action}</td>
                    <td>${tech}</td>
                    <td>${oldS}</td>
                    <td>${newS}</td>
                    <td>${date}</td>
                  </tr>`;
        }).join('');
        $('#ticketHistoryTable tbody').html(rowsHtml);
      }

      // 4) Initialize DataTable AFTER the rows are in the DOM
      // Use a tiny timeout to ensure layout settled (helps with some bootstrap modal timing)
      setTimeout(function () {
        try {
          historyDT = new DataTable('#ticketHistoryTable', {
            fixedHeader: { header: true },
            order: [],
            destroy: true // allow re-init safely (redundant with manual destroy above)
          });
        } catch (err) {
          console.warn('Failed to init DataTable:', err);
        }
      }, 50);
    },
    error: function (xhr, status, err) {
      console.error('fetch_ticket_history error:', status, err, xhr);
      $('#ticketHistoryTable tbody').html('<tr><td colspan="5" class="text-center text-danger">Failed to load history.</td></tr>');
    }
  });
});
