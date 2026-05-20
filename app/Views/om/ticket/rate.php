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
<div class="mb-3">
    <button type="button" class="btn btn-info btn-sm" id="downloadTechRecordBtn" data-ticketid="<?= (int)$ticketId ?>">
        <i class="fas fa-download"></i> Download Technical Record
    </button>
</div>

<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px;">
    <div class="card" style="max-width: 500px; width: 100%; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); border: none; border-radius: 12px;">
        <div class="card-body" style="padding: 40px;">
            <h2 style="text-align: center; margin-bottom: 10px; color: #333; font-weight: 700;">Rate This Ticket</h2>
            <p style="text-align: center; color: #666; margin-bottom: 30px; font-size: 0.95rem;">Help us improve our support quality</p>

            <form method="POST" action="<?= htmlspecialchars($base) ?>/om/tickets/rate" id="rateTicketForm">
                <input type="hidden" name="ticket_id" value="<?= (int)$ticketId ?>">

                <!-- Star Rating -->
                <div class="form-group" style="margin-bottom: 30px;">
                    <label style="font-weight: 600; margin-bottom: 15px; display: block; color: #333;">How would you rate your experience?</label>
                    <div style="display: flex; gap: 12px; justify-content: center; margin-bottom: 10px;" id="starRating">
                        <span class="star" data-value="1" style="font-size: 2.5rem; cursor: pointer; color: #ddd; transition: all 0.2s; user-select: none;" title="Poor">★</span>
                        <span class="star" data-value="2" style="font-size: 2.5rem; cursor: pointer; color: #ddd; transition: all 0.2s; user-select: none;" title="Fair">★</span>
                        <span class="star" data-value="3" style="font-size: 2.5rem; cursor: pointer; color: #ddd; transition: all 0.2s; user-select: none;" title="Good">★</span>
                        <span class="star" data-value="4" style="font-size: 2.5rem; cursor: pointer; color: #ddd; transition: all 0.2s; user-select: none;" title="Very Good">★</span>
                        <span class="star" data-value="5" style="font-size: 2.5rem; cursor: pointer; color: #ddd; transition: all 0.2s; user-select: none;" title="Excellent">★</span>
                    </div>
                    <p style="text-align: center; color: #999; font-size: 0.9rem; margin: 10px 0 0 0;" id="ratingText">Click to select rating</p>
                    <select name="rating" id="ratingSelect" style="display: none;" required>
                        <option value="">Select rating</option>
                        <option value="5">★★★★★ Excellent</option>
                        <option value="4">★★★★ Very Good</option>
                        <option value="3">★★★ Good</option>
                        <option value="2">★★ Fair</option>
                        <option value="1">★ Poor</option>
                    </select>
                </div>

                <!-- Comment -->
                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="font-weight: 600; margin-bottom: 10px; display: block; color: #333;">Comments (optional)</label>
                    <textarea name="comment" class="form-control" placeholder="Share your feedback to help us improve..." style="border-radius: 8px; border: 1px solid #e0e0e0; padding: 12px; font-size: 0.95rem; min-height: 100px; resize: vertical;"></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-block" style="padding: 12px; font-size: 1rem; font-weight: 600; border-radius: 8px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; margin-top: 10px;">
                    <i class="fas fa-paper-plane" style="margin-right: 8px;"></i>
                    Submit Rating
                </button>
                <button type="button" class="btn btn-light btn-block" style="padding: 12px; margin-top: 10px; border-radius: 8px; border: 1px solid #e0e0e0; color: #333; font-weight: 600;" onclick="window.history.back();">
                    Cancel
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    #starRating .star:hover,
    #starRating .star.active {
        color: #ffc107;
        transform: scale(1.2);
    }
    
    #starRating .star {
        transition: all 0.15s ease-in-out;
    }
    
    .card-body {
        background: white;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stars = document.querySelectorAll('#starRating .star');
        const ratingSelect = document.getElementById('ratingSelect');
        const ratingText = document.getElementById('ratingText');
        const labels = ['Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                ratingSelect.value = value;

                // Update all stars
                stars.forEach(s => {
                    if (s.getAttribute('data-value') <= value) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });

                ratingText.textContent = labels[value - 1];
            });

            // Hover effect
            star.addEventListener('mouseenter', function() {
                const value = this.getAttribute('data-value');
                stars.forEach(s => {
                    if (s.getAttribute('data-value') <= value) {
                        s.style.color = '#ffc107';
                    } else {
                        s.style.color = '#ddd';
                    }
                });
            });
        });

        // Reset hover effect
        document.getElementById('starRating').addEventListener('mouseleave', function() {
            stars.forEach(star => {
                if (star.classList.contains('active')) {
                    star.style.color = '#ffc107';
                } else {
                    star.style.color = '#ddd';
                }
            });
        });

        // Form submission with AJAX
        document.getElementById('rateTicketForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent normal form submission
            
            const btn = e.target.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Submitting...';

            const formData = new FormData(this);
            const base = "<?= htmlspecialchars($base) ?>";

            fetch(base + '/om/tickets/rate', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Rating submitted successfully!');
                    window.history.back();
                } else {
                    alert('Error: ' + (data.message || 'Failed to submit rating'));
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting your rating');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });

        // Download button handler
        const downloadBtn = document.getElementById('downloadTechRecordBtn');
        if (downloadBtn) {
            downloadBtn.addEventListener('click', function() {
                const ticketId = this.getAttribute('data-ticketid');
                if (!ticketId) {
                    alert('Invalid ticket ID');
                    return;
                }
                const base = "<?= htmlspecialchars($base) ?>";
                window.location.href = base + '/om/tickets/download-record?id=' + ticketId;
            });
        }
    });
</script>
<?php endif; ?>
