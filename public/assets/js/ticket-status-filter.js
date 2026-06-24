(function (window) {
  "use strict";

  window.TMS_OPEN_TICKET_FILTER = "__open__";
  window.TMS_OPEN_TICKET_STATUSES = ["pending", "in progress", "on hold", "reopened"];

  window.tmsMatchesStatusFilter = function (status, filter) {
    if (!filter) {
      return true;
    }
    if (filter === window.TMS_OPEN_TICKET_FILTER) {
      return window.TMS_OPEN_TICKET_STATUSES.indexOf(status) !== -1;
    }
    return status === filter;
  };
})(window);
