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

  function initAssetInventoryTable() {
    var $table = $("#asset_inventory");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }
    if ($table.find("tbody td[colspan]").length) {
      return;
    }
    if (isTableInitialized($table[0])) {
      return;
    }

    var dt = new DataTable("#asset_inventory", {
      fixedHeader: { header: true },
      order: [[1, "asc"]],
      pageLength: 10,
      columnDefs: [{ targets: [7], orderable: false, searchable: false }],
    });

    var branchFilter = "";
    var statusFilter = "";

    registerSearch(function (settings, searchData, dataIndex) {
      if (getTableId(settings) !== "asset_inventory") {
        return true;
      }
      var row = dt.row(dataIndex).node();
      if (!row) {
        return true;
      }
      var branch = (row.getAttribute("data-branch") || "").trim().toLowerCase();
      var status = (row.getAttribute("data-status") || "").trim().toLowerCase();
      if (branchFilter && branch !== branchFilter) {
        return false;
      }
      if (statusFilter && status !== statusFilter) {
        return false;
      }
      return true;
    });

    function redraw() {
      dt.draw();
    }

    $("#invBranchFilter").on("change", function () {
      branchFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#invStatusFilter").on("change", function () {
      statusFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#invClearFilters").on("click", function () {
      branchFilter = statusFilter = "";
      $("#invBranchFilter, #invStatusFilter").val("");
      dt.search("");
      redraw();
    });
  }

  $(document).ready(initAssetInventoryTable);
})(jQuery);
