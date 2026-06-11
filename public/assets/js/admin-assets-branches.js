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

  function initBranchListTable() {
    var $table = $("#branchList");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }
    if (isTableInitialized($table[0])) {
      return;
    }

    new DataTable("#branchList", {
      fixedHeader: { header: true },
      order: [[1, "asc"]],
      pageLength: 10,
      columnDefs: [{ targets: [4], orderable: false, searchable: false }],
    });
  }

  $(document).ready(initBranchListTable);
})(jQuery);
