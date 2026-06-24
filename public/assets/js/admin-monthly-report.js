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

  function initMonthlyReportTable() {
    var $table = $("#monthlyTicketsTable");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }
    if (isTableInitialized($table[0])) {
      return;
    }

    var dt = new DataTable("#monthlyTicketsTable", {
      order: [[6, "asc"]],
      pageLength: 25,
    });

    var branchFilter = "";
    var categoryFilter = "";
    var priorityFilter = "";
    var statusFilter = "";

    registerSearch(function (settings, searchData, dataIndex) {
      if (getTableId(settings) !== "monthlyTicketsTable") {
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

    $("#monthlyBranchFilter").on("change", function () {
      branchFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#monthlyCategoryFilter").on("change", function () {
      categoryFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#monthlyPriorityFilter").on("change", function () {
      priorityFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#monthlyStatusFilter").on("change", function () {
      statusFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#monthlyClearFilters").on("click", function () {
      branchFilter = categoryFilter = priorityFilter = statusFilter = "";
      $("#monthlyBranchFilter, #monthlyCategoryFilter, #monthlyPriorityFilter, #monthlyStatusFilter").val("");
      dt.search("");
      redraw();
    });
  }

  $(document).ready(initMonthlyReportTable);
})(jQuery);
