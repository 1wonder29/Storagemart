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

  function initAssetDirectoryTable() {
    var $table = $("#asset");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }
    if ($table.find("tbody td[colspan]").length) {
      return;
    }
    if (isTableInitialized($table[0])) {
      return;
    }

    var dt = new DataTable("#asset", {
      fixedHeader: { header: true },
      order: [[0, "asc"]],
      pageLength: 10,
      columnDefs: [{ targets: [6], orderable: false, searchable: false }],
    });

    var categoryFilter = "";

    registerSearch(function (settings, searchData, dataIndex) {
      if (getTableId(settings) !== "asset") {
        return true;
      }
      var row = dt.row(dataIndex).node();
      if (!row) {
        return true;
      }
      var category = (row.getAttribute("data-category") || "").trim().toLowerCase();
      if (categoryFilter && category !== categoryFilter) {
        return false;
      }
      return true;
    });

    function redraw() {
      dt.draw();
    }

    $("#assetCategoryFilter").on("change", function () {
      categoryFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#assetClearFilters").on("click", function () {
      categoryFilter = "";
      $("#assetCategoryFilter").val("");
      dt.search("");
      redraw();
    });
  }

  $(document).ready(initAssetDirectoryTable);
})(jQuery);
