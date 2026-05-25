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
    
    <div id="alreadyRatedOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; z-index: 9999; pointer-events: auto;">
        <div class="popup-box" style="background: white; border-radius: 10px; padding: 50px 50px; text-align: center; box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15); max-width: 450px; border: 1px solid #e0e0e0; pointer-events: auto;">
            <h2 style="color: #333; margin-bottom: 20px; font-weight: 700; font-size: 1.8rem; margin-top: 0;">Thank You!</h2>
            <p style="font-size: 1rem; color: #666; margin-bottom: 35px; line-height: 1.6;">You already rated this ticket. 😊</p>
            <button type="button" id="closeOverlay" onclick="(function(){try{if(window.jQuery&&jQuery('#rateTicketModal').length){jQuery('#rateTicketModal').modal('hide');}else if(document.getElementById('rateTicketModal')){document.getElementById('rateTicketModal').style.display='none';}else{window.history.back();}}catch(e){window.history.back();}})();" style="padding: 12px 50px; font-size: 1rem; font-weight: 600; border: 2px solid #333; background: white; color: #333; border-radius: 5px; cursor: pointer; transition: all 0.3s;">Got it</button>
        </div>
    </div>
    
    <script>
        (function() {
            var btn = document.getElementById('closeOverlay');
            if (!btn) return;
            btn.addEventListener('click', function () {
                try {
                    if (window.jQuery && jQuery('#rateTicketModal').length) {
                        jQuery('#rateTicketModal').modal('hide');
                    } else if (document.getElementById('rateTicketModal')) {
                        document.getElementById('rateTicketModal').style.display = 'none';
                    } else {
                        window.history.back();
                    }
                } catch (e) {
                    window.history.back();
                }
            });
            btn.addEventListener('mouseover', function() { this.style.background = '#f5f5f5'; });
            btn.addEventListener('mouseout', function() { this.style.background = 'white'; });
        })();
    </script>
<?php else: ?>
<?php
$base = rtrim(BASE_URL, '/');
?>
<div class="mb-3">
    <button type="button" class="btn btn-info btn-sm" id="downloadTechRecordBtn" data-ticketid="<?= (int)$ticketId ?>">
        <i class="fas fa-download"></i> Download Technical Record
    </button>
</div>

<form id="rateTicketForm">
    <input type="hidden" name="ticket_id" value="<?= (int)$ticketId ?>">

    <div class="form-group">
        <label>How would you rate your experience?</label>
        <div style="display: flex; gap: 10px; justify-content: center; margin: 15px 0;">
            <span class="star" data-value="1" tabindex="0" style="font-size: 2rem; cursor: pointer; color: #ddd; transition: all 0.2s;" title="Poor">★</span>
            <span class="star" data-value="2" tabindex="0" style="font-size: 2rem; cursor: pointer; color: #ddd; transition: all 0.2s;" title="Fair">★</span>
            <span class="star" data-value="3" tabindex="0" style="font-size: 2rem; cursor: pointer; color: #ddd; transition: all 0.2s;" title="Good">★</span>
            <span class="star" data-value="4" tabindex="0" style="font-size: 2rem; cursor: pointer; color: #ddd; transition: all 0.2s;" title="Very Good">★</span>
            <span class="star" data-value="5" tabindex="0" style="font-size: 2rem; cursor: pointer; color: #ddd; transition: all 0.2s;" title="Excellent">★</span>
        </div>
        <p style="text-align: center; color: #999; font-size: 0.9rem;" id="ratingText">Click to select rating</p>
        <input type="hidden" name="rating" id="ratingSelect" value="">
    </div>

    <div class="form-group">
        <label>Comment (optional)</label>
        <textarea name="comment" class="form-control"></textarea>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Submit Rating</button>
</form>

<script>
$(document).on('click', '#downloadTechRecordBtn', function () {
    const ticketId = $(this).data('ticketid');
    if (!ticketId) {
        alert('Invalid ticket ID');
        return;
    }
    const base = "<?= htmlspecialchars($base) ?>";
    window.location.href = base + '/om/tickets/download-record?id=' + ticketId;
});
</script>
<script>
$(function() {
    $(".star").on("click keypress", function(e) {
        if (e.type === 'keypress' && e.which !== 13 && e.which !== 32) return;
        const rating = $(this).data('value');
        $("#ratingSelect").val(rating);
        $(".star").css("color", "#ddd");
        if ($.fn.addBack) {
            $(this).prevAll(".star").addBack().css("color", "#ffc107");
        } else {
            $(this).prevAll(".star").andSelf().css("color", "#ffc107");
        }
        $("#ratingText").text(rating + " star" + (rating > 1 ? "s" : "")).css("color", "#666");
    });

    $("#rateTicketForm").on("submit", function(e) {
        e.preventDefault();
        
        if (!$("#ratingSelect").val()) {
            $("#ratingText").text("Please select a rating").css("color", "#d9534f");
            $(".star").first().focus();
            return false;
        }

        const base = "<?= htmlspecialchars($base) ?>";
        const form = $(this);
        
        $.ajax({
            url: base + '/om/tickets/store-rating',
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(result) {
                if (result.success) {
                    alert(result.message);
                    $('#rateTicketModal').modal('hide');
                    setTimeout(function() { location.reload(); }, 500);
                } else {
                    alert('Error: ' + result.message);
                }
            },
            error: function() {
                alert('Failed to submit rating. Please try again.');
            }
        });
    });
});
</script>
<?php endif; ?>
