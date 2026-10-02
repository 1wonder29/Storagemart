(function (window) {
  'use strict';

  if (window.__devReloadActive) {
    return;
  }
  window.__devReloadActive = true;

  var POLL_MS = 1200;
  var knownVersion = null;

  function baseUrl() {
    if (window.BASE_URL) {
      return String(window.BASE_URL).replace(/\/$/, '');
    }
    var meta = document.querySelector('meta[name="base-url"]');
    if (meta && meta.content) {
      return String(meta.content).replace(/\/$/, '');
    }
    return '';
  }

  function checkForChanges() {
    var url = baseUrl() + '/dev/reload-check?_=' + Date.now();

    fetch(url, {
      credentials: 'same-origin',
      cache: 'no-store',
      headers: { Accept: 'application/json' },
    })
      .then(function (res) {
        if (!res.ok) {
          throw new Error('HTTP ' + res.status);
        }
        return res.json();
      })
      .then(function (data) {
        var version = data && data.version;
        if (typeof version !== 'number') {
          return;
        }

        if (knownVersion !== null && version !== knownVersion) {
          window.location.reload();
          return;
        }

        knownVersion = version;
      })
      .catch(function () {
        /* dev server may be restarting */
      });
  }

  checkForChanges();
  window.setInterval(checkForChanges, POLL_MS);
})(window);
