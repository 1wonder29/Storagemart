document.getElementById('btnSearchEmployee').addEventListener('click', function () {
    var q = document.getElementById('employee_search').value.trim();
    if (!q) { alert('Enter employee id or name'); return; }

    var url = "<?= htmlspecialchars($base) ?>/admin/assets/search-employee?q=" + encodeURIComponent(q);

    fetch(url, { credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById("employee_id").value = data.employee_id;
                document.getElementById("employee_search").value = data.full_name;
                document.getElementById("branchName").value = data.branchName;
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert("Error contacting server.");
        });
});
