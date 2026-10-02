<?php
// flash_modal.php
// NOTE: session_start() should already have been called by the layout or front controller.

// read & clear flash messages
$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<!-- Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" role="alert" aria-live="polite" aria-atomic="true">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="notificationTitle">Notification</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="notificationBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  // encode PHP values safely into JS
  var flashSuccess = <?= json_encode($flashSuccess) ?>;
  var flashError   = <?= json_encode($flashError) ?>;

  // helper: show modal using jQuery/Bootstrap if available, otherwise fallback to alert()
  function showNotification(title, message, autoCloseMs) {
      if (typeof $ !== 'undefined' && typeof $.fn !== 'undefined' && typeof $.fn.modal === 'function') {
          $('#notificationTitle').text(title);
          $('#notificationBody').text(message);
          // show modal
          $('#notificationModal').modal('show');

          // move focus into modal for accessibility
          $('#notificationModal').on('shown.bs.modal', function () {
              $(this).find('.modal-content').focus();
          });

          // optionally auto-close after X ms
          if (autoCloseMs && Number.isFinite(autoCloseMs)) {
              setTimeout(function() {
                  $('#notificationModal').modal('hide');
              }, autoCloseMs);
          }
      } else {
          // jQuery/Bootstrap not available — fall back to built-in alert
          alert(title + "\n\n" + message);
      }
  }

  // On DOM ready (try jQuery if available, otherwise DOMContentLoaded)
  function onReady(fn) {
      if (typeof $ !== 'undefined') {
          $(fn);
      } else {
          if (document.readyState === 'complete' || document.readyState === 'interactive') {
              setTimeout(fn, 0);
          } else {
              document.addEventListener('DOMContentLoaded', fn);
          }
      }
  }

  onReady(function() {
      var message = '';
      var title = 'Notification';
      if (flashSuccess) {
          message = flashSuccess;
          title = 'Success';
      } else if (flashError) {
          message = flashError;
          title = 'Error';
      }

      if (message) {
          // show for 6 seconds (6000 ms) then auto-hide — change to null to disable
          showNotification(title, message, 6000);
      }
  });
})();
</script>
