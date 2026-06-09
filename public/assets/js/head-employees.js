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

    var branchFilter = "";
    var positionFilter = "";

    registerSearch(function (settings, searchData, dataIndex) {
      if (getTableId(settings) !== "employee-table") {
        return true;
      }

      var row = dt.row(dataIndex).node();
      if (!row) {
        return true;
      }

      var branch = (row.getAttribute("data-branch") || "").trim().toLowerCase();
      var position = (row.getAttribute("data-position") || "").trim().toLowerCase();

      if (branchFilter && branch !== branchFilter) {
        return false;
      }
      if (positionFilter && position !== positionFilter) {
        return false;
      }
      return true;
    });

    function redraw() {
      dt.draw();
    }

    $("#employeeBranchFilter").on("change", function () {
      branchFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#employeePositionFilter").on("change", function () {
      positionFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#employeeClearFilters").on("click", function () {
      branchFilter = positionFilter = "";
      $("#employeeBranchFilter, #employeePositionFilter").val("");
      dt.search("");
      redraw();
    });
  }

  $(document).ready(initEmployeesTable);
})(jQuery);
