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

  function initSimpleTable(tableId, orderColumns) {
    var $table = $("#" + tableId);
    if (!$table.length || !$table.find("tbody tr").length) {
      return null;
    }
    if ($table.find("tbody td[colspan]").length) {
      return null;
    }
    if (isTableInitialized($table[0])) {
      return null;
    }

    return new DataTable("#" + tableId, {
      fixedHeader: { header: true },
      order: orderColumns,
      pageLength: 10,
    });
  }

  function initTeamAssetsTable() {
    var $table = $("#team-asset-table");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }
    if ($table.find("tbody td[colspan]").length) {
      return;
    }
    if (isTableInitialized($table[0])) {
      return;
    }

    var dt = new DataTable("#team-asset-table", {
      fixedHeader: { header: true },
      order: [[7, "asc"], [6, "asc"], [0, "asc"]],
      pageLength: 15,
    });

    var branchFilter = "";

    registerSearch(function (settings, searchData, dataIndex) {
      if (getTableId(settings) !== "team-asset-table") {
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

    $("#assetBranchFilter").on("change", function () {
      branchFilter = ($(this).val() || "").trim();
      redraw();
    });
    $("#assetClearFilters").on("click", function () {
      branchFilter = "";
      $("#assetBranchFilter").val("");
      dt.search("");
      redraw();
    });
  }

  $(document).ready(function () {
    initSimpleTable("my-asset-table", [[0, "asc"]]);
    initTeamAssetsTable();
  });
})(jQuery);
