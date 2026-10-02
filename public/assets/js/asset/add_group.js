document.getElementById("category_id").addEventListener("change", function() {
    var selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value !== "") {
        document.getElementById("ic_code").value = selectedOption.getAttribute("data-ic_code");
    } else {
        document.getElementById("ic_code").value = "";
    }
});
