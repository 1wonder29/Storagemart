<?php if ($alreadyRated): ?>
    <!-- Popup Modal for Already Rated -->
    <style>
        @keyframes popIn {
            0% {
                opacity: 0;
                transform: scale(0.3);
            }
            50% {
                opacity: 0.8;
                transform: scale(1.05);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
        #alreadyRatedOverlay .popup-box {
            animation: popIn 0.4s ease-out;
        }
    </style>
    
    <div id="alreadyRatedOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; z-index: 9999; pointer-events: none;">
        <div class="popup-box" style="background: white; border-radius: 10px; padding: 50px 50px; text-align: center; box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15); max-width: 450px; border: 1px solid #e0e0e0; pointer-events: auto;">
            <h2 style="color: #333; margin-bottom: 20px; font-weight: 700; font-size: 1.8rem; margin-top: 0;">Thank You!</h2>
            <p style="font-size: 1rem; color: #666; margin-bottom: 35px; line-height: 1.6;">You already rated this ticket. 😊</p>
            <button type="button" id="closeOverlay" style="padding: 12px 50px; font-size: 1rem; font-weight: 600; border: 2px solid #333; background: white; color: #333; border-radius: 5px; cursor: pointer; transition: all 0.3s;">Got it</button>
        </div>
    </div>
    
    <script>
        document.getElementById('closeOverlay').onclick = function() {
            window.history.back();
        }
        document.getElementById('closeOverlay').onmouseover = function() {
            this.style.background = '#f5f5f5';
        }
        document.getElementById('closeOverlay').onmouseout = function() {
            this.style.background = 'white';
        }
    </script>
<?php else: ?>
<?php
$base = rtrim(BASE_URL, '/');
?>
<form method="POST" action="<?= htmlspecialchars($base) ?>/head/tickets/rate" id="rateTicketForm">
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

    <button type="submit" class="btn btn-primary btn-block">Submit Rating</button>
</form>
<?php endif; ?>