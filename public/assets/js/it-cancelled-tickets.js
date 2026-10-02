(function ($) {
  "use strict";

  function initCancelledTicketsTable() {
    var $table = $("#ticketTables");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }
    if ($table.find("tbody td[colspan]").length) {
      return;
    }

    new DataTable("#ticketTables", {
      fixedHeader: { header: true },
      order: [[7, "desc"]],
      pageLength: 10,
      columnDefs: [{ targets: [8], orderable: false, searchable: false }],
    });
  }

  $(document).ready(initCancelledTicketsTable);
})(jQuery);
