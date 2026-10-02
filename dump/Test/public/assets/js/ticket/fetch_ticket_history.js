// public/assets/js/ticket/fetch_ticket_history.js
// Requires: jQuery, DataTables
// Must be loaded AFTER jQuery and DataTables JS and AFTER `const base = "...";` is defined.

(function () {
    // small helper to avoid inserting raw HTML
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    $(document).ready(function () {
        // Initialize main tickets DataTable if not already initialized
        try {
            if (!$.fn.DataTable.isDataTable('#ticketsTable')) {
                $('#ticketsTable').DataTable({
                    // keep defaults from your demo; add options if needed
                    responsive: true,
                    // you can add pageLength, order, columnDefs, etc.
                    pageLength: 10
                });
            }
        } catch (err) {
            console.error('DataTable init error:', err);
        }

        // Attach click handler for "View" buttons (delegated in case rows are replaced)
        $(document).on('click', '.viewBtn', function () {
            const id = $(this).data('ticketid');

            $('#ticket_number').val($(this).data('ticketnum') || '');
            $('#employee').val($(this).data('employee') || '');
            $('#priority').val($(this).data('priority') || '');
            $('#status').val($(this).data('status') || '');

            // CLEAR history table (not the main tickets table)
            $('#ticketHistoryTable tbody').empty();

            // fetch history JSON (expects JSON array)
            $.getJSON(base + '/employee/tickets/history/fetch', { ticket_id: id })
                .done(function (data) {
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(row => {
                            $('#ticketHistoryTable tbody').append(`
                                <tr>
                                    <td>${escapeHtml(row.action_details)}</td>
                                    <td>${escapeHtml(row.performed_by)}</td>
                                    <td>${escapeHtml(row.old_status || '')}</td>
                                    <td>${escapeHtml(row.new_status || '')}</td>
                                    <td>${escapeHtml(row.date_logged || '')}</td>
                                </tr>
                            `);
                        });
                    } else {
                        $('#ticketHistoryTable tbody').append(
                            `<tr><td colspan="5" class="text-center">No history found.</td></tr>`
                        );
                    }
                })
                .fail(function (jqxhr, textStatus, error) {
                    console.error('Failed to fetch ticket history:', textStatus, error);
                    $('#ticketHistoryTable tbody').append(
                        `<tr><td colspan="5" class="text-center text-danger">Failed to load history.</td></tr>`
                    );
                });

            $('#viewTicketModal').modal('show');
        });
    });
})();
