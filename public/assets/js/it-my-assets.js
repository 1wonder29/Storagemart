(function ($) {
  "use strict";

  function initAssetsTable() {
    var $table = $("#assetUser");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }

    var dt = new DataTable("#assetUser", {
      fixedHeader: { header: true },
      order: [[0, "asc"]],
      pageLength: 10,
      columnDefs: [{ targets: [4], orderable: false, searchable: false }],
    });

    var typeFilter = "";

    $.fn.dataTable.ext.search.push(function (settings, data) {
      if (settings.nTable.id !== "assetUser") return true;
      if (!typeFilter) return true;
      return (data[1] || "").toLowerCase().indexOf(typeFilter) !== -1;
    });

    function redraw() {
      dt.draw();
    }

    $("#assetTypeFilter").on("change", function () {
      typeFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });

    $("#assetClearFilters").on("click", function () {
      typeFilter = "";
      $("#assetTypeFilter").val("");
      dt.search("");
      redraw();
    });
  }

  $(document).ready(initAssetsTable);
})(jQuery);
