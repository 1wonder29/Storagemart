$('#btnSearchEmployee').on('click', function() {
    var query = $('#employee_search').val().trim();

    if(query === '') {
        alert("Please enter employee name or ID.");
        return;
    }

    $.ajax({
        url: window.BASE_URL + '/admin/tickets/search-employee',  // create this new file
        type: 'GET',
        data: { q: query },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                $('#employee_id').val(response.employee_id);
                $('#employee_search').val(response.full_name);
                $('#fullname').val(response.full_name);
                $('#branch').val(response.branchName);
                $('#department').val(response.department);

                // Trigger asset fetch
                fetchAssets(response.employee_id);
            } else {
                alert(response.message);
                $('#employee_id').val('');
                $('#fullname').val('');
                $('#branch').val('');
                $('#department').val('');
                $('#assetsTable').html('');
            }
        },
        error: function() {
            alert('Error fetching employee data.');
        }
    });
});


function fetchAssets(employee_id) {
    if (!employee_id) {
        $('#assetsTable').html('');
        return;
    }

    $.ajax({
        type: 'GET',
        url: window.BASE_URL + '/admin/tickets/get-assets',
        data: { employee_id: employee_id },
        dataType: 'json',
        success: function(response) {
            // ✅ NEW CONDITION
            if (response.success && response.data && response.data.length > 0) {
                var assetsHTML = '';
                $.each(response.data, function(index, asset) {
                    assetsHTML += '<tr>';
                    assetsHTML += '<td>' + asset.assetNumber + '</td>';
                    assetsHTML += '<td>' + asset.groupName + '</td>';
                    assetsHTML += '<td>' + asset.ic_code + '</td>';
                    assetsHTML += '<td>' + asset.itemInfo + '</td>';
                    assetsHTML += '<td>' + asset.serialNumber + '</td>';
                    assetsHTML += '<td>' + asset.year_purchased + '</td>';
                    assetsHTML += '<td><a href="' + window.BASE_URL 
                                  + '/admin/tickets/file?inventory_id=' + asset.inventory_id 
                                  + '&employee_id=' + employee_id + '">'
                                  + '<button type="button" class="btn btn-outline-success">File Ticket</button>'
                                  + '</a></td>';
                    assetsHTML += '</tr>';
                });
                $('#assetsTable').html(assetsHTML);
            } else {
                $('#assetsTable').html('<tr><td colspan="7">No assets found for this employee.</td></tr>');
            }
        },
        error: function() {
            alert('Error fetching asset data.');
        }
    });
}
