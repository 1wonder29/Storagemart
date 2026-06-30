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

  function initReportTable(config) {
    var tableId = config.tableId;
    var $table = $("#" + tableId);
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }
    if (isTableInitialized($table[0])) {
      return;
    }

    var dt = new DataTable("#" + tableId, {
      order: [[6, "asc"]],
      pageLength: 25,
    });

    var branchFilter = "";
    var categoryFilter = "";
    var priorityFilter = "";
    var statusFilter = "";

    registerSearch(function (settings, searchData, dataIndex) {
      if (getTableId(settings) !== tableId) {
        return true;
      }

      var row = dt.row(dataIndex).node();
      if (!row) {
        return true;
      }

      var branch = (row.getAttribute("data-branch") || "").trim().toLowerCase();
      var category = (row.getAttribute("data-category") || "").trim().toLowerCase();
      var priority = (row.getAttribute("data-priority") || "").trim().toLowerCase();
      var status = (row.getAttribute("data-status") || "").trim().toLowerCase();

      if (branchFilter && branch !== branchFilter) {
        return false;
      }
      if (categoryFilter && category !== categoryFilter) {
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

    $(config.branchFilter).on("change", function () {
      branchFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $(config.categoryFilter).on("change", function () {
      categoryFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $(config.priorityFilter).on("change", function () {
      priorityFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $(config.statusFilter).on("change", function () {
      statusFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $(config.clearFilters).on("click", function () {
      branchFilter = categoryFilter = priorityFilter = statusFilter = "";
      $(config.branchFilter + ", " + config.categoryFilter + ", " + config.priorityFilter + ", " + config.statusFilter).val("");
      dt.search("");
      redraw();
    });
  }

  function initTicketReportTypeToggle() {
    var $type = $("#reportType");
    if (!$type.length) {
      return;
    }

    var $monthly = $(".report-period-monthly");
    var $weekly = $(".report-period-weekly");
    var $month = $("#month");
    var $week = $("#week");
    var exportBase = $("#ticketReportExport").attr("href").split("?")[0];

    function syncPeriodFields() {
      var isWeekly = $type.val() === "weekly";
      $monthly.toggle(!isWeekly);
      $weekly.toggle(isWeekly);
      $month.prop("disabled", isWeekly);
      $week.prop("disabled", !isWeekly);
    }

    function buildExportUrl() {
      var params = {
        type: $type.val(),
        year: $("#year").val(),
      };
      if ($type.val() === "weekly") {
        params.week = $("#week").val();
      } else {
        params.month = $("#month").val();
      }
      return exportBase + "?" + $.param(params);
    }

    $type.on("change", function () {
      syncPeriodFields();
      $("#ticketReportExport").attr("href", buildExportUrl());
    });

    $("#year, #month, #week").on("change", function () {
      $("#ticketReportExport").attr("href", buildExportUrl());
    });

    syncPeriodFields();
  }

  $(document).ready(function () {
    initReportTable({
      tableId: "ticketReportTable",
      branchFilter: "#ticketBranchFilter",
      categoryFilter: "#ticketCategoryFilter",
      priorityFilter: "#ticketPriorityFilter",
      statusFilter: "#ticketStatusFilter",
      clearFilters: "#ticketClearFilters",
    });

    initTicketReportTypeToggle();
  });
})(jQuery);
