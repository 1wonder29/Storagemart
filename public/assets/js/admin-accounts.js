(function ($) {
  "use strict";

  function registerSearch(fn) {
    if (typeof DataTable !== "undefined" && DataTable.ext && DataTable.ext.search) {
      DataTable.ext.search.push(fn);
    } else if ($.fn.dataTable && $.fn.dataTable.ext && $.fn.dataTable.ext.search) {
      $.fn.dataTable.ext.search.push(fn);
    }
  }

  function getTableId(settings) {
    return settings.sTableId || (settings.nTable && settings.nTable.id) || "";
  }

  function initUserDirectory() {
    var $table = $("#userDirectory");
    if (!$table.length || !$table.find("tbody tr").length) {
      return;
    }

    var dt = new DataTable("#userDirectory", {
      fixedHeader: { header: true },
      order: [[5, "desc"]],
      pageLength: 10,
      columnDefs: [{ targets: [6], orderable: false, searchable: false }],
    });

    var filters = { role: "", department: "", branch: "", status: "" };

    registerSearch(function (settings, searchData, dataIndex) {
      if (getTableId(settings) !== "userDirectory") {
        return true;
      }
      var row = dt.row(dataIndex).node();
      if (!row) {
        return true;
      }
      var roles = (row.getAttribute("data-role") || "").split(/\s+/);
      if (filters.role && roles.indexOf(filters.role) === -1) return false;
      if (filters.department && row.getAttribute("data-department") !== filters.department) return false;
      if (filters.branch && row.getAttribute("data-branch") !== filters.branch) return false;
      if (filters.status && row.getAttribute("data-status") !== filters.status) return false;
      return true;
    });

    var selects = {
      role: "#userRoleFilter",
      department: "#userDepartmentFilter",
      branch: "#userBranchFilter",
      status: "#userStatusFilter",
    };

    Object.keys(selects).forEach(function (key) {
      $(selects[key]).on("change", function () {
        filters[key] = ($(this).val() || "").toLowerCase();
        dt.draw();
      });
    });

    $("#userClearFilters").on("click", function () {
      Object.keys(selects).forEach(function (key) {
        filters[key] = "";
        $(selects[key]).val("");
      });
      dt.search("").draw();
    });
  }

  function initManageUserModal() {
    var $modal = $("#manageUserModal");
    if (!$modal.length) {
      return;
    }

    $(document).on("click", ".js-manage-user", function () {
      var $btn = $(this);
      var name = String($btn.data("name"));
      var accountId = parseInt($btn.data("account-id"), 10) || 0;
      var status = String($btn.data("status"));
      var tickets = parseInt($btn.data("tickets"), 10) || 0;
      var items = parseInt($btn.data("items"), 10) || 0;
      var assets = parseInt($btn.data("assets"), 10) || 0;
      var hasHistory = tickets > 0 || items > 0;

      $("#manageUserAccountId").val(accountId);
      $("#manageUserEmployeeId").val($btn.data("employee-id"));
      $("#manageUserDisplayName").val(name);
      $("#manageUserName").text(name);

      var $history = $("#manageUserHistory");
      if (hasHistory) {
        var parts = [];
        if (tickets > 0) parts.push(tickets + " ticket record(s)");
        if (items > 0) parts.push(items + " inventory item record(s)");
        $history
          .html(
            "This person has " + parts.join(" and ") + ". To keep that history, they can't be permanently deleted." +
            (accountId > 0 ? " <strong>Deactivate</strong> them instead — they won't be able to log in." : "")
          )
          .removeClass("d-none");
        $("#manageUserDeletable").addClass("d-none");
      } else {
        $history.addClass("d-none");
        $("#manageUserDeletable").removeClass("d-none");
        $("#manageUserAssets")
          .toggleClass("d-none", assets === 0)
          .text(assets > 0 ? assets + " asset(s) assigned to them will be returned to inventory." : "");
      }

      $("#manageUserDeleteBtn").toggleClass("d-none", hasHistory);
      $("#manageUserDeactivateBtn").toggleClass("d-none", accountId === 0 || status !== "active");
      $("#manageUserActivateBtn").toggleClass("d-none", accountId === 0 || status === "active");

      $modal.modal("show");
    });

    $modal.on("click", "button[data-action]", function () {
      $("#manageUserAction").val($(this).data("action"));
    });
  }

  $(document).ready(function () {
    initUserDirectory();
    initManageUserModal();
  });
})(jQuery);
