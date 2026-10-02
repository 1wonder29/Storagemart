(function ($) {
  'use strict';

  var $modal = $('#employeeListModal');
  if (!$modal.length) return;

  var $branch = $('#employeeListBranch');
  var $filter = $('#employeeListFilter');
  var $body = $('#employeeListBody');
  var $count = $('#employeeListCount');
  var branchesLoaded = false;
  var filterTimer = null;
  var requestId = 0;

  function escapeHtml(value) {
    return $('<div>').text(value == null ? '' : String(value)).html();
  }

  function fillBranches(branches) {
    if (branchesLoaded) return;
    branchesLoaded = true;
    $.each(branches || [], function (i, branch) {
      $branch.append($('<option>').val(branch.branch_id).text(branch.branchName));
    });
  }

  function render(employees) {
    if (!employees || !employees.length) {
      $body.html('<tr><td colspan="5" class="text-center text-muted py-4">No employees found.</td></tr>');
      $count.text('');
      return;
    }

    var rows = '';
    $.each(employees, function (i, emp) {
      rows += '<tr class="employee-list-row" style="cursor:pointer">' +
        '<td>' + escapeHtml(emp.full_name) + '<div class="small text-muted">ID ' + escapeHtml(emp.employee_id) + '</div></td>' +
        '<td>' + escapeHtml(emp.position) + '</td>' +
        '<td>' + escapeHtml(emp.department) + '</td>' +
        '<td>' + escapeHtml(emp.branchName) + '</td>' +
        '<td class="text-right"><button type="button" class="btn btn-sm btn-primary js-select-employee"' +
        ' data-id="' + escapeHtml(emp.employee_id) + '"' +
        ' data-name="' + escapeHtml(emp.full_name) + '"' +
        ' data-branch="' + escapeHtml(emp.branchName) + '"' +
        ' data-department="' + escapeHtml(emp.department) + '">Select</button></td>' +
        '</tr>';
    });
    $body.html(rows);
    $count.text(employees.length + ' employee' + (employees.length === 1 ? '' : 's') + ' shown');
  }

  function load() {
    var current = ++requestId;
    $body.html('<tr><td colspan="5" class="text-center text-muted py-4">Loading…</td></tr>');

    $.ajax({
      url: window.BASE_URL + '/admin/tickets/employee-list',
      type: 'GET',
      dataType: 'json',
      data: { branch_id: $branch.val(), q: $filter.val() }
    }).done(function (res) {
      if (current !== requestId) return;
      if (!res || !res.success) {
        $body.html('<tr><td colspan="5" class="text-center text-danger py-4">Unable to load employees.</td></tr>');
        return;
      }
      fillBranches(res.branches);
      render(res.employees);
    }).fail(function () {
      if (current !== requestId) return;
      $body.html('<tr><td colspan="5" class="text-center text-danger py-4">Unable to load employees.</td></tr>');
    });
  }

  function selectEmployee($button) {
    var name = $button.attr('data-name');
    var employeeId = $button.attr('data-id');

    $('#employee_id').val(employeeId);
    $('#fullname').val(name);
    $('#branch').val($button.attr('data-branch'));
    $('#department').val($button.attr('data-department'));
    $('#employee_search').val(name);

    if (typeof fetchAssets === 'function') {
      fetchAssets(employeeId);
    }
    $modal.modal('hide');
  }

  $('#btnEmployeeList').on('click', function () {
    $modal.modal('show');
  });

  $modal.on('shown.bs.modal', function () {
    load();
    $filter.trigger('focus');
  });

  $branch.on('change', load);

  $filter.on('input', function () {
    window.clearTimeout(filterTimer);
    filterTimer = window.setTimeout(load, 250);
  });

  $body.on('click', '.js-select-employee', function (event) {
    event.stopPropagation();
    selectEmployee($(this));
  });

  $body.on('click', '.employee-list-row', function () {
    selectEmployee($(this).find('.js-select-employee'));
  });
})(jQuery);
