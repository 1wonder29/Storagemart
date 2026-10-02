(function (window) {
  "use strict";

  window.tmsMatchesStatusFilter = function (status, filter) {
    if (!filter) {
      return true;
    }
    return status === filter;
  };
})(window);
