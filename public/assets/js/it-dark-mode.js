(function () {
  "use strict";

  var STORAGE_KEY = "it-dark-mode";

  function isDarkEnabled() {
    return document.documentElement.classList.contains("it-dark");
  }

  function getChartTheme() {
    var dark = isDarkEnabled();
    return {
      dark: dark,
      text: dark ? "#a8aeb8" : "#858796",
      textStrong: dark ? "#e2e5ea" : "#5a5c69",
      grid: dark ? "rgba(56, 62, 72, 0.6)" : "rgb(234, 236, 244)",
      tooltipBg: dark ? "#2d323c" : "#fff",
      tooltipBorder: dark ? "#383e48" : "#dddfeb",
      doughnutBorder: dark ? "#252932" : "rgba(234, 236, 244, 1)",
    };
  }

  function refreshCharts() {
    var theme = getChartTheme();

    if (window.Chart) {
      Chart.defaults.color = theme.text;
    }

    if (window.__itTicketChart) {
      var tc = window.__itTicketChart;
      tc.data.datasets[0].borderColor = theme.doughnutBorder;
      if (tc.options.plugins && tc.options.plugins.tooltip) {
        tc.options.plugins.tooltip.backgroundColor = theme.tooltipBg;
        tc.options.plugins.tooltip.borderColor = theme.tooltipBorder;
        tc.options.plugins.tooltip.titleColor = theme.textStrong;
        tc.options.plugins.tooltip.bodyColor = theme.text;
      }
      if (tc.options.plugins && tc.options.plugins.legend && tc.options.plugins.legend.labels) {
        tc.options.plugins.legend.labels.color = theme.text;
      }
      tc.update("none");
    }

    if (window.__itAreaChart) {
      var ac = window.__itAreaChart;
      if (ac.options.plugins && ac.options.plugins.tooltip) {
        ac.options.plugins.tooltip.backgroundColor = theme.tooltipBg;
        ac.options.plugins.tooltip.borderColor = theme.tooltipBorder;
        ac.options.plugins.tooltip.titleColor = theme.textStrong;
        ac.options.plugins.tooltip.bodyColor = theme.text;
      }
      if (ac.options.plugins && ac.options.plugins.legend && ac.options.plugins.legend.labels) {
        ac.options.plugins.legend.labels.color = theme.text;
      }
      if (ac.options.scales) {
        if (ac.options.scales.x && ac.options.scales.x.ticks) {
          ac.options.scales.x.ticks.color = theme.text;
        }
        if (ac.options.scales.y) {
          if (ac.options.scales.y.ticks) ac.options.scales.y.ticks.color = theme.text;
          if (ac.options.scales.y.grid) ac.options.scales.y.grid.color = theme.grid;
        }
      }
      ac.update("none");
    }

    document.dispatchEvent(new CustomEvent("it-theme-change", { detail: theme }));
  }

  function updateToggleUi(enabled) {
    var btn = document.getElementById("itDarkModeToggle");
    var icon = document.getElementById("itDarkModeIcon");
    if (!btn || !icon) return;

    btn.classList.toggle("it-dark-active", enabled);
    btn.setAttribute("aria-pressed", enabled ? "true" : "false");
    btn.setAttribute("title", enabled ? "Switch to light mode" : "Switch to dark mode");
    icon.className = enabled ? "fas fa-sun" : "fas fa-moon";
  }

  function setDarkMode(enabled) {
    document.documentElement.classList.toggle("it-dark", enabled);
    try {
      localStorage.setItem(STORAGE_KEY, enabled ? "1" : "0");
    } catch (e) {}
    updateToggleUi(enabled);
    refreshCharts();
  }

  function initDarkMode() {
    var btn = document.getElementById("itDarkModeToggle");
    updateToggleUi(isDarkEnabled());

    if (window.Chart) {
      Chart.defaults.color = getChartTheme().text;
    }

    if (btn) {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        setDarkMode(!isDarkEnabled());
      });
    }

    document.addEventListener("it-charts-ready", refreshCharts);
    setTimeout(refreshCharts, 600);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initDarkMode);
  } else {
    initDarkMode();
  }
})();
