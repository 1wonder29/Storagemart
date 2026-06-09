(function ($) {
  "use strict";

  function isTableInitialized(table) {
    if ($.fn.DataTable && $.fn.DataTable.isDataTable(table)) {
      return true;
    }
    if (typeof DataTable !== "undefined" && DataTable.isDataTable) {
      return DataTable.isDataTable(table);
    }
    return false;
  }

  function getTableId(settings) {
    return (
      settings.sTableId ||
      (settings.nTable && settings.nTable.id) ||
      (settings.nTable && settings.nTable.getAttribute && settings.nTable.getAttribute("id")) ||
      ""
    );
  }

  function registerSearch(fn) {
    if (typeof DataTable !== "undefined" && DataTable.ext && DataTable.ext.search) {
      DataTable.ext.search.push(fn);
    } else if ($.fn.dataTable && $.fn.dataTable.ext && $.fn.dataTable.ext.search) {
      $.fn.dataTable.ext.search.push(fn);
    }
  }

  function initEmployeesTable() {
    var $table = $("#employee-table");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }
    if ($table.find("tbody td[colspan]").length) {
      return;
    }
    if (isTableInitialized($table[0])) {
      return;
    }

    var dt = new DataTable("#employee-table", {
      fixedHeader: { header: true },
      order: [[4, "desc"]],
      pageLength: 10,
      columnDefs: [{ targets: [5], orderable: false, searchable: false }],
    });

    var deptFilter = "";
    var branchFilter = "";

    registerSearch(function (settings, searchData, dataIndex) {
      if (getTableId(settings) !== "employee-table") {
        return true;
      }

      var row = dt.row(dataIndex).node();
      if (!row) {
        return true;
      }

      var dept = (row.getAttribute("data-department") || "").trim().toLowerCase();
      var branch = (row.getAttribute("data-branch") || "").trim().toLowerCase();

      if (deptFilter && dept !== deptFilter) {
        return false;
      }
      if (branchFilter && branch !== branchFilter) {
        return false;
      }
      return true;
    });

    function redraw() {
      dt.draw();
    }

    $("#employeeDeptFilter").on("change", function () {
      deptFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#employeeBranchFilter").on("change", function () {
      branchFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#employeeClearFilters").on("click", function () {
      deptFilter = branchFilter = "";
      $("#employeeDeptFilter, #employeeBranchFilter").val("");
      dt.search("");
      redraw();
    });
  }

  window.confirmDeleteEmployee = function (event, employeeId, employeeName) {
    event.preventDefault();
    if (
      confirm(
        'Are you sure you want to delete employee "' +
          employeeName +
          '"?\n\nThis action cannot be undone and will permanently remove all associated data.'
      )
    ) {
      document.getElementById("deleteEmployeeId").value = employeeId;
      document.getElementById("deleteEmployeeForm").submit();
    }
  };

  $(document).ready(initEmployeesTable);
})(jQuery);
