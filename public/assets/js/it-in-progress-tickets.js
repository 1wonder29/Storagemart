(function ($) {
  "use strict";

  var inProgressSearchAdded = false;
  var branchFilter = "";
  var priorityFilter = "";
  var statusFilter = "";

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

    var dt = new DataTable("#IT-TicketDatables", {
      fixedHeader: { header: true },
      order: [[6, "desc"]],
      pageLength: 10,
      columnDefs: [{ targets: [7], orderable: false, searchable: false }],
    });

    if (!inProgressSearchAdded) {
      $.fn.dataTable.ext.search.push(function (settings, searchData, dataIndex) {
        if (settings.nTable.id !== "IT-TicketDatables") return true;

        var api = new $.fn.dataTable.Api(settings);
        var row = api.row(dataIndex).node();
        if (!row) return true;

        var branch = (row.getAttribute("data-branch") || "").trim().toLowerCase();
        var priority = (row.getAttribute("data-priority") || "").trim().toLowerCase();
        var status = (row.getAttribute("data-status") || "").trim().toLowerCase();

        if (branchFilter && branch !== branchFilter) return false;
        if (priorityFilter && priority !== priorityFilter) return false;
        if (statusFilter && status !== statusFilter) return false;
        return true;
      });
      inProgressSearchAdded = true;
    }

    function redraw() {
      dt.draw();
    }

    $("#ipBranchFilter").off("change.tmsInProgress").on("change.tmsInProgress", function () {
      branchFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#ipPriorityFilter").off("change.tmsInProgress").on("change.tmsInProgress", function () {
      priorityFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#ipStatusFilter").off("change.tmsInProgress").on("change.tmsInProgress", function () {
      statusFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#ipClearFilters").off("click.tmsInProgress").on("click.tmsInProgress", function () {
      branchFilter = priorityFilter = statusFilter = "";
      $("#ipBranchFilter, #ipPriorityFilter, #ipStatusFilter").val("");
      dt.search("");
      redraw();
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
