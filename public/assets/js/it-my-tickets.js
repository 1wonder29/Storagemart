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

  function initMyTicketsTable() {
    var $table = $("#ticketsTable");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }
    if ($table.find("tbody td[colspan]").length) {
      return;
    }
    if (isTableInitialized($table[0])) {
      return;
    }

    new DataTable("#ticketsTable", {
      fixedHeader: { header: true },
      order: [[5, "desc"]],
      pageLength: 10,
      columnDefs: [{ targets: [6], orderable: false, searchable: false }],
    });
  }

  $(document).ready(initMyTicketsTable);
})(jQuery);
