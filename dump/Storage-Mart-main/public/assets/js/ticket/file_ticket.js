    function togglePasswordVisibility() {
        var passwordField = document.getElementById("txtPassword");
        if (passwordField.type === "password") {
            passwordField.type = "text";
            document.getElementById("showPassword").textContent = "Hide";
        }
        else {
            passwordField.type = "password";
            document.getElementById("showPassword").textContent = "Show";
        }
    }

    document.getElementById("employee_id").addEventListener("change", function() {
    var selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value !== "") {
        document.getElementById("lastname").value = selectedOption.getAttribute("data-lastname");
        document.getElementById("firstname").value = selectedOption.getAttribute("data-firstname");
        document.getElementById("middlename").value = selectedOption.getAttribute("data-middlename");
        document.getElementById("branch").value = selectedOption.getAttribute("data-branch");
        document.getElementById("department").value = selectedOption.getAttribute("data-department");
    } else {
        document.getElementById("lastname").value = "";
        document.getElementById("firstname").value = "";
        document.getElementById("middlename").value = "";
        document.getElementById("branch").value = "";
        document.getElementById("department").value = "";
    }
});