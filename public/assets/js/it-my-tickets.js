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
      order: [[4, "desc"]],
      pageLength: 10,
      columnDefs: [{ targets: [5], orderable: false, searchable: false }],
    });

    var branchFilter = "";
    var priorityFilter = "";
    var statusFilter = "";

    $.fn.dataTable.ext.search.push(function (settings, data) {
      if (settings.nTable.id !== "ticketsTable") return true;
      var issue = (data[1] || "").toLowerCase();
      var branch = (data[2] || "").toLowerCase();
      var badges = (data[3] || "").toLowerCase();

      if (branchFilter && branch.indexOf(branchFilter) === -1) return false;
      if (priorityFilter && badges.indexOf(priorityFilter) === -1) return false;
      if (statusFilter && badges.indexOf(statusFilter) === -1) return false;
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
