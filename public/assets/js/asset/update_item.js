document.addEventListener("DOMContentLoaded", function() {
    var statusSelect = document.getElementById("status");
    var reasonRow = document.getElementById("reasonRow");
    var reasonInput = document.getElementById("transferDetails");
    if (!statusSelect || !reasonRow || !reasonInput) {
        return;
    }

    // The saved reason belongs to the item's current status: show it again when that
    // status is selected, and start blank for a different one.
    var originalStatus = statusSelect.getAttribute("data-original-status") || statusSelect.value;
    var originalReason = reasonInput.value;

    function needsReason(status) {
        return status === "DEFECTIVE" || status === "DISPOSED" || status === "LOST" || status === "RETURNED";
    }

    function toggleReasonRow(statusChanged) {
        var status = statusSelect.value;

        if (needsReason(status)) {
            reasonRow.style.display = "block";
            reasonInput.setAttribute("required", "required");
            if (statusChanged) {
                reasonInput.value = status === originalStatus ? originalReason : "";
            }
        } else {
            reasonRow.style.display = "none";
            reasonInput.removeAttribute("required");
            reasonInput.value = "";
        }
    }

    // Initial
    toggleReasonRow(false);
    statusSelect.addEventListener("change", function() {
        toggleReasonRow(true);
    });
});
