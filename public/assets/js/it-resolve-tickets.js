(function ($) {
  "use strict";

  function initResolveTicketsTable() {
    var $table = $("#ticketTables");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }
    if ($table.find("tbody td[colspan]").length) {
      return;
    }

    new DataTable("#ticketTables", {
      fixedHeader: { header: true },
      order: [[4, "desc"]],
      pageLength: 10,
      columnDefs: [{ targets: [5], orderable: false, searchable: false }],
    });
  }

  $(document).ready(initResolveTicketsTable);
})(jQuery);
