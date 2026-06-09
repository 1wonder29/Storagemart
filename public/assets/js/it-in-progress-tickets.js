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
      order: [[4, "desc"]],
      pageLength: 10,
      columnDefs: [{ targets: [5], orderable: false, searchable: false }],
    });

    var branchFilter = "";
    var priorityFilter = "";
    var statusFilter = "";

    $.fn.dataTable.ext.search.push(function (settings, data) {
      if (settings.nTable.id !== "IT-TicketDatables") return true;
      var requester = (data[1] || "").toLowerCase();
      var issue = (data[2] || "").toLowerCase();
      var assignment = (data[3] || "").toLowerCase();

      if (branchFilter && requester.indexOf(branchFilter) === -1) return false;
      if (priorityFilter && issue.indexOf(priorityFilter) === -1) return false;
      if (statusFilter && (issue.indexOf(statusFilter) === -1 && assignment.indexOf(statusFilter) === -1)) {
        return false;
      }
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
