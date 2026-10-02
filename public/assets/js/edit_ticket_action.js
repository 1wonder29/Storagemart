$(document).on('click', '.openUpdateAssignBtn', function (e) {
  e.preventDefault();

  const ticketId   = $(this).data('ticket-id');
  const assignedId = $(this).data('assignedid') || '';
  const status     = ($(this).data('status') || '').toLowerCase();

  if (!ticketId) {
    alert('Invalid ticket id.');
    return;
  }

  if (status === 'resolved' || status === 'closed') {
    alert('This ticket is already ' + (status === 'closed' ? 'closed' : 'resolved') + ' and cannot be reassigned.');
    return;
  }

  if (status === 'cancelled') {
    alert('This ticket is cancelled and cannot be reassigned.');
    return;
  }

  $('#update_ticket_id').val(ticketId);
  $('#assigned_to_select').val(assignedId || '');

  $('#updateAssignModal').modal('show');
});