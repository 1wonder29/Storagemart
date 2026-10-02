    function togglePasswordVisibility() {
    var passwordField = document.getElementById("password");
    var icon = document.querySelector("#showPassword i");

    if (passwordField.type === "password") {
        passwordField.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        passwordField.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

document.getElementById("showPassword").addEventListener("click", togglePasswordVisibility);

    var notificationMessage = "<?php echo isset($notificationMessage) ? $notificationMessage : ''; ?>";
    if (notificationMessage !== "") {
        alert(notificationMessage);
        window.location.href = "Accounts.php";
    }
document.getElementById("branchName").addEventListener("change", function() {
    var selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value !== "") {
        document.getElementById("branchCode").value = selectedOption.getAttribute("data-branchCode");
    } else {
        document.getElementById("branchCode").value = "";
    }
});