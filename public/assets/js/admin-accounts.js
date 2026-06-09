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

  function initAccountsTable() {
    var $table = $("#account");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }
    if ($table.find("tbody td[colspan]").length) {
      return;
    }
    if (isTableInitialized($table[0])) {
      return;
    }

    var dt = new DataTable("#account", {
      fixedHeader: { header: true },
      order: [[3, "desc"]],
      pageLength: 10,
      columnDefs: [{ targets: [4], orderable: false, searchable: false }],
    });

    var roleFilter = "";

    registerSearch(function (settings, searchData, dataIndex) {
      if (getTableId(settings) !== "account") {
        return true;
      }

      var row = dt.row(dataIndex).node();
      if (!row) {
        return true;
      }

      var role = (row.getAttribute("data-role") || "").trim().toLowerCase();
      if (roleFilter && role !== roleFilter) {
        return false;
      }
      return true;
    });

    function redraw() {
      dt.draw();
    }

    $("#accountRoleFilter").on("change", function () {
      roleFilter = ($(this).val() || "").trim().toLowerCase();
      redraw();
    });
    $("#accountClearFilters").on("click", function () {
      roleFilter = "";
      $("#accountRoleFilter").val("");
      dt.search("");
      redraw();
    });
  }

  $(document).ready(initAccountsTable);
})(jQuery);
