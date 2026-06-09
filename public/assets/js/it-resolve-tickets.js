(function ($) {
  "use strict";

  function initResolveTicketsTable() {
    var $table = $("#ticketTables");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }
    if ($table.find("tbody td[colspan]").length) {
      return;
    }

    var dt = new DataTable("#ticketTables", {
      fixedHeader: { header: true },
      order: [[4, "desc"]],
      pageLength: 10,
      columnDefs: [{ targets: [5], orderable: false, searchable: false }],
    });

    var branchFilter = "";
    var purposeFilter = "";
    var resultFilter = "";

    $.fn.dataTable.ext.search.push(function (settings, data) {
      if (settings.nTable.id !== "ticketTables") {
        return true;
      }
      var branch = (data[1] || "").toLowerCase();
      var purpose = (data[2] || "").toLowerCase();
      var resolution = (data[3] || "").toLowerCase();

      if (branchFilter && branch.indexOf(branchFilter) === -1) return false;
      if (purposeFilter && purpose.indexOf(purposeFilter) === -1) return false;
      if (resultFilter && resolution.indexOf(resultFilter) === -1) return false;
      return true;
    });

    function redraw() {
      dt.draw();
    }

    $("#resolveBranchFilter").on("change", function () {
      branchFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });

    $("#resolvePurposeFilter").on("change", function () {
      purposeFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });

    $("#resolveResultFilter").on("change", function () {
      resultFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });

    $("#resolveClearFilters").on("click", function () {
      branchFilter = purposeFilter = resultFilter = "";
      $("#resolveBranchFilter, #resolvePurposeFilter, #resolveResultFilter").val("");
      dt.search("");
      redraw();
    });
  }

  $(document).ready(initResolveTicketsTable);
})(jQuery);
