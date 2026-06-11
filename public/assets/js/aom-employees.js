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

  function initAomEmployeesTable() {
    var $table = $("#aom-employee-table");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }
    if ($table.find("tbody td[colspan]").length) {
      return;
    }
    if (isTableInitialized($table[0])) {
      return;
    }

    var dt = new DataTable("#aom-employee-table", {
      fixedHeader: { header: true },
      order: [[0, "asc"]],
      pageLength: 10,
      columnDefs: [{ targets: [5], orderable: false, searchable: false }],
    });

    var branchFilter = "";

    registerSearch(function (settings, searchData, dataIndex) {
      if (getTableId(settings) !== "aom-employee-table") {
        return true;
      }

      var row = dt.row(dataIndex).node();
      if (!row) {
        return true;
      }

      var branchId = (row.getAttribute("data-branch-id") || "").trim();

      if (branchFilter && branchId !== branchFilter) {
        return false;
      }
      return true;
    });

    function redraw() {
      dt.draw();
    }

    $("#aomBranchFilter").on("change", function () {
      branchFilter = ($(this).val() || "").trim();
      redraw();
    });
    $("#aomClearFilters").on("click", function () {
      branchFilter = "";
      $("#aomBranchFilter").val("");
      dt.search("");
      redraw();
    });
  }

  $(document).ready(initAomEmployeesTable);
})(jQuery);
