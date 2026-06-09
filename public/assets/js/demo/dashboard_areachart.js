// Ticket Resolution Time Area Chart (Chart.js v3+)

function itAreaChartTheme() {
  const dark = document.documentElement.classList.contains('it-dark');
  return {
    dark: dark,
    text: dark ? '#a8aeb8' : '#858796',
    textStrong: dark ? '#e2e5ea' : '#5a5c69',
    grid: dark ? 'rgba(56, 62, 72, 0.6)' : 'rgb(234, 236, 244)',
    tooltipBg: dark ? '#2d323c' : '#fff',
    tooltipBorder: dark ? '#383e48' : '#dddfeb',
    fill: dark ? 'rgba(78, 115, 223, 0.12)' : 'rgba(78, 115, 223, 0.05)',
  };
}

const areaCtx = document.getElementById("myAreaChart");

if (areaCtx && window.ticketResolution) {
  const SLA_DAYS = 1;
  const labels = Array.isArray(window.ticketResolution.labels) ? window.ticketResolution.labels : [];
  const series = Array.isArray(window.ticketResolution.data)
    ? window.ticketResolution.data.map(v => Number(v) / 24 || 0) // Convert hours to days
    : [];

  const hasData = series.length > 0 && series.some(v => v > 0);
  const safeSeries = hasData ? series : labels.map(() => 0);
  const avg = hasData ? (series.reduce((a, b) => a + b, 0) / series.length) : 0;

  const theme = itAreaChartTheme();

  window.__itAreaChart = new Chart(areaCtx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: "Resolution Time (days)",
          data: safeSeries,
          tension: 0.3,
          fill: true,
          backgroundColor: theme.fill,
          borderColor: "rgba(78, 115, 223, 1)",
          borderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
          pointBackgroundColor: safeSeries.map(v =>
            v > SLA_DAYS ? '#e74a3b' : '#4e73df'
          ),
          pointBorderColor: theme.dark ? '#252932' : '#fff',
        },
        {
          label: "SLA (1 day)",
          data: Array(safeSeries.length).fill(SLA_DAYS),
          borderColor: "#e74a3b",
          borderDash: [6, 6],
          pointRadius: 0,
          fill: false
        },
        {
          label: "Average",
          data: Array(safeSeries.length).fill(Number(avg.toFixed(2))),
          borderColor: "#858796",
          borderDash: [3, 4],
          pointRadius: 0,
          fill: false
        }
      ]
    },
    options: {
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'bottom',
          labels: { color: theme.text }
        },
        tooltip: {
          backgroundColor: theme.tooltipBg,
          titleColor: theme.textStrong,
          bodyColor: theme.text,
          borderColor: theme.tooltipBorder,
          borderWidth: 1,
          padding: 12,
          callbacks: {
            label: function (context) {
              if (!hasData && context.dataset.label === "Resolution Time (days)") return "No data yet";
              const y = Number(context.parsed.y);
              if (!Number.isFinite(y)) return context.dataset.label;
              return `${context.dataset.label}: ${y.toFixed(2)} days`;
            }
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: {
            maxRotation: 45,
            minRotation: 30,
            color: theme.text
          }
        },
        y: {
          beginAtZero: true,
          suggestedMax: Math.max(SLA_DAYS + 0.25, ...safeSeries) || (SLA_DAYS + 0.25),
          ticks: {
            color: theme.text,
            callback: value => value.toFixed(2) + ' days'
          },
          grid: {
            color: theme.grid
          }
        }
      }
    }
  });

  document.dispatchEvent(new CustomEvent('it-charts-ready'));
}
