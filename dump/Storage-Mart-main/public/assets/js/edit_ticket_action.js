$(document).on('click', '.openUpdateAssignBtn', function (e) {
  e.preventDefault();

  const ticketId   = $(this).data('ticket-id');
  const assignedId = $(this).data('assignedid') || '';
  const status     = ($(this).data('status') || '').toLowerCase();

  if (!ticketId) {
    alert('Invalid ticket id.');
    return;
  }

  // 🔒 Block resolved tickets (UI safety)
  if (status === 'resolved') {
    alert('This ticket is already resolved and cannot be reassigned.');
    return;
  }

  $('#update_ticket_id').val(ticketId);
  $('#assigned_to_select').val(assignedId || '');

  $('#updateAssignModal').modal('show');
});