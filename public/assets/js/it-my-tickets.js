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

    var dt = new DataTable("#ticketsTable", {
      fixedHeader: { header: true },
      order: [[5, "desc"]],
      pageLength: 10,
      columnDefs: [{ targets: [6], orderable: false, searchable: false }],
    });

    var branchFilter = "";
    var priorityFilter = "";
    var statusFilter = "";

    $.fn.dataTable.ext.search.push(function (settings, searchData, dataIndex) {
      if (settings.nTable.id !== "ticketsTable") return true;

      var row = dt.row(dataIndex).node();
      if (!row) return true;

      var priority = (row.getAttribute("data-priority") || "").trim().toLowerCase();
      var status = (row.getAttribute("data-status") || "").trim().toLowerCase();
      var branch = (row.getAttribute("data-branch") || "").trim().toLowerCase();

      if (branchFilter && branch !== branchFilter) return false;
      if (priorityFilter && priority !== priorityFilter) return false;
      if (statusFilter && status !== statusFilter) return false;
      return true;
    });

    function redraw() {
      dt.draw();
    }

    $("#myBranchFilter").on("change", function () {
      branchFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#myPriorityFilter").on("change", function () {
      priorityFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#myStatusFilter").on("change", function () {
      statusFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#myClearFilters").on("click", function () {
      branchFilter = priorityFilter = statusFilter = "";
      $("#myBranchFilter, #myPriorityFilter, #myStatusFilter").val("");
      dt.search("");
      redraw();
    });
  }

  $(document).ready(initMyTicketsTable);
})(jQuery);
