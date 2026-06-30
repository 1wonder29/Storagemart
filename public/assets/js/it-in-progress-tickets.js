(function ($) {
  "use strict";

  function initInProgressTable() {
    var $table = $("#IT-TicketDatables");
    if (!$table.length) {
      return;
    }

    if ($.fn.DataTable.isDataTable($table)) {
      $table.DataTable().destroy();
    }

    if (!$table.find("tbody tr").length || $table.find("tbody td[colspan]").length) {
      return;
    }

    new DataTable("#IT-TicketDatables", {
      fixedHeader: { header: true },
      order: [[6, "desc"]],
      pageLength: 10,
      columnDefs: [{ targets: [7], orderable: false, searchable: false }],
    });
  }

  $(document).ready(initInProgressTable);

  document.addEventListener("tms:ticket-table-refreshed", function (event) {
    if (!event.detail || event.detail.tableId !== "IT-TicketDatables") {
      return;
    }
    initInProgressTable();
  });
})(jQuery);
