(function ($) {
  "use strict";

  function initInProgressTable() {
    var $table = $("#IT-TicketDatables");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }
    if ($table.find("tbody td[colspan]").length) {
      return;
    }

    var dt = new DataTable("#IT-TicketDatables", {
      fixedHeader: { header: true },
      order: [[6, "desc"]],
      pageLength: 10,
      columnDefs: [{ targets: [7], orderable: false, searchable: false }],
    });

    var branchFilter = "";
    var priorityFilter = "";
    var statusFilter = "";

    $.fn.dataTable.ext.search.push(function (settings, searchData, dataIndex) {
      if (settings.nTable.id !== "IT-TicketDatables") return true;

      var row = dt.row(dataIndex).node();
      if (!row) return true;

      var branch = (row.getAttribute("data-branch") || "").trim().toLowerCase();
      var priority = (row.getAttribute("data-priority") || "").trim().toLowerCase();
      var status = (row.getAttribute("data-status") || "").trim().toLowerCase();

      if (branchFilter && branch !== branchFilter) return false;
      if (priorityFilter && priority !== priorityFilter) return false;
      if (statusFilter && status !== statusFilter) return false;
      return true;
    });

    function redraw() {
      dt.draw();
    }

    $("#ipBranchFilter").on("change", function () {
      branchFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#ipPriorityFilter").on("change", function () {
      priorityFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#ipStatusFilter").on("change", function () {
      statusFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#ipClearFilters").on("click", function () {
      branchFilter = priorityFilter = statusFilter = "";
      $("#ipBranchFilter, #ipPriorityFilter, #ipStatusFilter").val("");
      dt.search("");
      redraw();
    });
  }

  $(document).ready(initInProgressTable);
})(jQuery);
