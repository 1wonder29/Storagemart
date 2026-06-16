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

    var dt = new DataTable("#ticketTables", {
      fixedHeader: { header: true },
      order: [[7, "desc"]],
      pageLength: 10,
      columnDefs: [{ targets: [8], orderable: false, searchable: false }],
    });

    var branchFilter = "";
    var priorityFilter = "";

    $.fn.dataTable.ext.search.push(function (settings, searchData, dataIndex) {
      if (settings.nTable.id !== "ticketTables") {
        return true;
      }

      var row = dt.row(dataIndex).node();
      if (!row) return true;

      var branch = (searchData[1] || "").toLowerCase();
      var priority = (row.getAttribute("data-priority") || "").trim().toLowerCase();

      if (branchFilter && branch.indexOf(branchFilter) === -1) return false;
      if (priorityFilter && priority !== priorityFilter) return false;
      return true;
    });

    function redraw() {
      dt.draw();
    }

    $("#cancelBranchFilter").on("change", function () {
      branchFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });

    $("#cancelPriorityFilter").on("change", function () {
      priorityFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });

    $("#cancelClearFilters").on("click", function () {
      branchFilter = priorityFilter = "";
      $("#cancelBranchFilter, #cancelPriorityFilter").val("");
      dt.search("");
      redraw();
    });
  }

  $(document).ready(initCancelledTicketsTable);
})(jQuery);
