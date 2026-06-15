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

  function initDefectiveTable() {
    var $table = $("#asset_defective");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }
    if ($table.find("tbody td[colspan]").length) {
      return;
    }
    if (isTableInitialized($table[0])) {
      return;
    }

    new DataTable("#asset_defective", {
      fixedHeader: { header: true },
      order: [[0, "asc"]],
      pageLength: 10,
      columnDefs: [{ targets: [8], orderable: false, searchable: false }],
    });
  }

  $(initDefectiveTable);
})(jQuery);
