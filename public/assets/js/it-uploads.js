(function ($) {
  "use strict";

  function normalize(value) {
    return String(value || "").trim().toLowerCase();
  }

  function applyUploadFilters() {
    var query = normalize($("#uploadSearch").val());
    var dateFilter = normalize($("#uploadDateFilter").val());
    var employeeFilter = normalize($("#uploadEmployeeFilter").val());
    var anyFilter = !!(query || dateFilter || employeeFilter);
    var visibleGroups = 0;
    var visibleFiles = 0;

    $(".upload-date-group").each(function () {
      var $group = $(this);
      var groupDate = normalize($group.data("upload-date"));
      var groupVisible = false;

      if (dateFilter && groupDate !== dateFilter) {
        $group.hide();
        return;
      }

      $group.find(".upload-file-row").each(function () {
        var $row = $(this);
        var ticket = normalize($row.data("ticket"));
        var employee = normalize($row.data("employee"));
        var filename = normalize($row.data("filename"));
        var haystack = ticket + " " + employee + " " + filename;

        var show = true;
        if (query && haystack.indexOf(query) === -1) show = false;
        if (employeeFilter && employee !== employeeFilter) show = false;

        $row.toggle(show);
        if (show) {
          groupVisible = true;
          visibleFiles++;
        }
      });

      $group.toggle(groupVisible);
      if (groupVisible) visibleGroups++;
    });

    $("#uploadNoResults").toggleClass("visible", anyFilter && visibleFiles === 0);
    $("#uploadResultsCount").text(visibleFiles + " file" + (visibleFiles === 1 ? "" : "s"));
  }

  $(document).ready(function () {
    $("#uploadSearch").on("input", applyUploadFilters);
    $("#uploadDateFilter, #uploadEmployeeFilter").on("change", applyUploadFilters);
    $("#uploadClearFilters").on("click", function () {
      $("#uploadSearch").val("");
      $("#uploadDateFilter, #uploadEmployeeFilter").val("");
      applyUploadFilters();
    });
  });
})(jQuery);
