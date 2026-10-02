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

  function getTableId(settings) {
    return (
      settings.sTableId ||
      (settings.nTable && settings.nTable.id) ||
      (settings.nTable && settings.nTable.getAttribute && settings.nTable.getAttribute("id")) ||
      ""
    );
  }

  function registerSearch(fn) {
    if (typeof DataTable !== "undefined" && DataTable.ext && DataTable.ext.search) {
      DataTable.ext.search.push(fn);
    } else if ($.fn.dataTable && $.fn.dataTable.ext && $.fn.dataTable.ext.search) {
      $.fn.dataTable.ext.search.push(fn);
    }
  }

  function initPendingsTable() {
    var $table = $("#pendings");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }
    if ($table.find("tbody td[colspan]").length) {
      return;
    }
    if (isTableInitialized($table[0])) {
      return;
    }

    var dt = new DataTable("#pendings", {
      fixedHeader: { header: true },
      order: [[6, "desc"]],
      pageLength: 10,
      columnDefs: [{ targets: [7], orderable: false, searchable: false }],
    });

    var deptFilter = "";
    var branchFilter = "";
    var priorityFilter = "";

    registerSearch(function (settings, searchData, dataIndex) {
      if (getTableId(settings) !== "pendings") {
        return true;
      }
      var row = dt.row(dataIndex).node();
      if (!row) {
        return true;
      }
      var dept = (row.getAttribute("data-department") || "").trim().toLowerCase();
      var branch = (row.getAttribute("data-branch") || "").trim().toLowerCase();
      var priority = (row.getAttribute("data-priority") || "").trim().toLowerCase();
      if (deptFilter && dept !== deptFilter) return false;
      if (branchFilter && branch !== branchFilter) return false;
      if (priorityFilter && priority !== priorityFilter) return false;
      return true;
    });

    function redraw() {
      dt.draw();
    }

    $("#pendingDeptFilter").on("change", function () {
      deptFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#pendingBranchFilter").on("change", function () {
      branchFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#pendingPriorityFilter").on("change", function () {
      priorityFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#pendingClearFilters").on("click", function () {
      deptFilter = branchFilter = priorityFilter = "";
      $("#pendingDeptFilter, #pendingBranchFilter, #pendingPriorityFilter").val("");
      dt.search("");
      redraw();
    });
  }

  function initPendingModals() {
    $("#approveAssignModal").on("show.bs.modal", function (e) {
      var ticketId = $(e.relatedTarget).data("ticket-id") || "";
      $(this).find("#approve_ticket_id").val(ticketId);
    });

    $("#declineModal").on("show.bs.modal", function (e) {
      var ticketId = $(e.relatedTarget).data("ticket-id") || "";
      $(this).find("#decline_ticket_id").val(ticketId);
    });

    $(document).on("click", ".viewTicketBtn", function () {
      var ticketId = $(this).data("ticket-id");
      $("#view_ticket_number").val($(this).data("ticket-num") || "");
      $("#view_employee").val($(this).data("employee") || "");
      $("#view_priority").val($(this).data("priority") || "");
      $("#view_status").val($(this).data("status") || "");
      $("#view_concern").val($(this).data("concern") || "");
      $("#viewTicketModal").modal("show");
      if (window.TicketComments) {
        TicketComments.load("#viewTicketModal .ticket-comments-section", ticketId);
      }
    });
  }

  $(document).ready(function () {
    initPendingsTable();
    initPendingModals();
  });
})(jQuery);
