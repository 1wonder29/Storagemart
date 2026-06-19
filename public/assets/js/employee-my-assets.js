(function ($) {
  "use strict";

  function initAssetsTable() {
    var $table = $("#assetUser");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }

    new DataTable("#assetUser", {
      fixedHeader: { header: true },
      order: [[0, "asc"]],
      pageLength: 10,
      columnDefs: [{ targets: [4], orderable: false, searchable: false }],
    });
  }

  $(document).ready(initAssetsTable);
})(jQuery);
