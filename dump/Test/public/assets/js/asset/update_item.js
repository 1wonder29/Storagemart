document.addEventListener("DOMContentLoaded", function() {
    function toggleReasonRow() {
        var status = document.getElementById("status").value;
        var reasonRow = document.getElementById("reasonRow");
        var reasonInput = document.getElementById("transferDetails");

        if (status === "DISPOSED" || status === "LOST" || status === "RETURNED") {
            reasonRow.style.display = "block";
            reasonInput.setAttribute("required", "required");
        } else {
            reasonRow.style.display = "none";
            reasonInput.removeAttribute("required");
            reasonInput.value = "";
        }
    }

    // Initial
    if (document.getElementById("status")) {
        toggleReasonRow();
        document.getElementById("status").addEventListener("change", toggleReasonRow);
    }
});
