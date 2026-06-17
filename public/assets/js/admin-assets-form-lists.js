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

  function initAssetFormTable(selector, orderCol, actionCol) {
    var $table = $(selector);
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }
    if ($table.find("tbody td[colspan]").length) {
      return;
    }
    if (isTableInitialized($table[0])) {
      return;
    }

    var columnDefs = [];
    if (typeof actionCol === "number") {
      columnDefs.push({ targets: [actionCol], orderable: false, searchable: false });
    }

    new DataTable(selector, {
      fixedHeader: { header: true },
      order: [[orderCol, "asc"]],
      pageLength: 10,
      columnDefs: columnDefs,
    });
  }

  $(function () {
    initAssetFormTable("#branchList", 1, 4);
    initAssetFormTable("#categoryList", 1, null);
  });
})(jQuery);
