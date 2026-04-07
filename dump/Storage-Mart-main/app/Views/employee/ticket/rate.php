<?php if ($alreadyRated): ?>
    <div class="alert alert-success">
        You already rated this ticket. Thank you! 😊
    </div>
    
<?php else: ?>
<?php
$base = rtrim(BASE_URL, '/');
?>
<form method="POST" action="<?= htmlspecialchars($base) ?>/employee/tickets/rate" id="rateTicketForm">
    <input type="hidden" name="ticket_id" value="<?= (int)$ticketId ?>">

    <div class="form-group">
        <label>Rating</label>
        <select name="rating" class="form-control" required>
            <option value="">Select rating</option>
            <option value="5">★★★★★</option>
            <option value="4">★★★★</option>
            <option value="3">★★★</option>
            <option value="2">★★</option>
            <option value="1">★</option>
        </select>
    </div>

    <div class="form-group">
        <label>Comment (optional)</label>
        <textarea name="comment" class="form-control"></textarea>
    </div>

    <button class="btn btn-primary btn-block">Submit Rating</button>
</form>
<?php endif; ?>