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

  function initAdminTicketsTable() {
    var $table = $("#logsTicket");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }
    if ($table.find("tbody td[colspan]").length) {
      return;
    }
    if (isTableInitialized($table[0])) {
      return;
    }

    var dt = new DataTable("#logsTicket", {
      fixedHeader: { header: true },
      order: [[5, "desc"]],
      pageLength: 10,
      columnDefs: [{ targets: [7], orderable: false, searchable: false }],
    });

    var branchFilter = "";
    var priorityFilter = "";
    var statusFilter = "";

    registerSearch(function (settings, searchData, dataIndex) {
      if (getTableId(settings) !== "logsTicket") {
        return true;
      }

      var row = dt.row(dataIndex).node();
      if (!row) {
        return true;
      }

      var branch = (row.getAttribute("data-branch") || "").trim().toLowerCase();
      var priority = (row.getAttribute("data-priority") || "").trim().toLowerCase();
      var status = (row.getAttribute("data-status") || "").trim().toLowerCase();

      if (branchFilter && branch !== branchFilter) {
        return false;
      }
      if (priorityFilter && priority !== priorityFilter) {
        return false;
      }
      if (statusFilter && !window.tmsMatchesStatusFilter(status, statusFilter)) {
        return false;
      }
      return true;
    });

    function redraw() {
      dt.draw();
    }

    $("#adminBranchFilter").on("change", function () {
      branchFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#adminPriorityFilter").on("change", function () {
      priorityFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#adminStatusFilter").on("change", function () {
      statusFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#adminClearFilters").on("click", function () {
      branchFilter = priorityFilter = statusFilter = "";
      $("#adminBranchFilter, #adminPriorityFilter, #adminStatusFilter").val("");
      dt.search("");
      redraw();
    });

    var params = new URLSearchParams(window.location.search);
    var initialStatus = (params.get("status") || "").trim();
    if (initialStatus !== "") {
      var $statusSelect = $("#adminStatusFilter");
      var matched = false;
      $statusSelect.find("option").each(function () {
        if (($(this).val() || "").trim().toLowerCase() === initialStatus.toLowerCase()) {
          $statusSelect.val($(this).val());
          matched = true;
          return false;
        }
      });
      if (matched) {
        statusFilter = initialStatus.toLowerCase();
        redraw();
      }
    }
  }

  $(document).ready(initAdminTicketsTable);
})(jQuery);
