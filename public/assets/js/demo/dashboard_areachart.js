// Ticket Resolution Time Area Chart (Chart.js v3+)

function itAreaChartTheme() {
  const dark = document.documentElement.classList.contains('it-dark');
  return {
    dark: dark,
    text: dark ? '#a8aeb8' : '#64748b',
    textStrong: dark ? '#e2e5ea' : '#0f172a',
    grid: dark ? 'rgba(56, 62, 72, 0.6)' : 'rgba(1, 43, 144, 0.08)',
    tooltipBg: dark ? '#2d323c' : '#fff',
    tooltipBorder: dark ? '#383e48' : 'rgba(1, 43, 144, 0.12)',
    fill: dark ? 'rgba(0, 106, 212, 0.18)' : 'rgba(0, 106, 212, 0.12)',
    line: 'rgba(0, 70, 173, 1)',
    sla: '#e74a3b',
  };
}

function formatDays(value) {
  const n = Number(value);
  if (!Number.isFinite(n)) return '0 days';
  if (n < 1) return `${n.toFixed(1)} day${n === 1 ? '' : 's'}`;
  return `${n.toFixed(1)} days`;
}

function shortenTicketLabel(label) {
  const text = String(label || '').trim();
  if (text.length <= 18) return text;
  return text.replace(/^Ticket\s*#?\s*/i, '');
}

const areaCtx = document.getElementById('myAreaChart');

if (areaCtx && window.ticketResolution) {
  const SLA_DAYS = 1;
  const rawLabels = Array.isArray(window.ticketResolution.labels) ? window.ticketResolution.labels : [];
  const rawSeries = Array.isArray(window.ticketResolution.data) ? window.ticketResolution.data : [];

  const labels = rawLabels.map(shortenTicketLabel);
  const series = rawSeries.map((value) => {
    const hours = Number(value);
    if (Number.isFinite(hours) && hours > 24 && hours === Math.floor(hours)) {
      return Math.round((hours / 24) * 10) / 10;
    }
    return Math.round(Number(value) * 10) / 10 || 0;
  });

  const hasData = series.length > 0 && series.some((v) => v > 0);
  const safeSeries = hasData ? series : labels.map(() => 0);
  const maxValue = Math.max(SLA_DAYS, ...safeSeries, 0);
  const yMax = Math.ceil((maxValue + 0.5) * 2) / 2;

  const theme = itAreaChartTheme();

  window.__itAreaChart = new Chart(areaCtx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Resolution time',
          data: safeSeries,
          tension: 0.35,
          fill: true,
          backgroundColor: theme.fill,
          borderColor: theme.line,
          borderWidth: 2.5,
          pointRadius: safeSeries.map((v) => (v > 0 ? 5 : 3)),
          pointHoverRadius: 7,
          pointBackgroundColor: safeSeries.map((v) => (v > SLA_DAYS ? theme.sla : theme.line)),
          pointBorderColor: theme.dark ? '#252932' : '#fff',
          pointBorderWidth: 2,
        },
        {
          label: `SLA (${SLA_DAYS} day)`,
          data: Array(safeSeries.length).fill(SLA_DAYS),
          borderColor: theme.sla,
          borderDash: [6, 6],
          borderWidth: 1.5,
          pointRadius: 0,
          fill: false,
        },
      ],
    },
    options: {
      maintainAspectRatio: false,
      interaction: {
        mode: 'nearest',
        intersect: false,
      },
      plugins: {
        legend: {
          display: true,
          position: 'bottom',
          labels: {
            color: theme.text,
            usePointStyle: true,
            padding: 18,
            font: { family: 'Nunito, sans-serif', size: 12 },
          },
        },
        tooltip: {
          backgroundColor: theme.tooltipBg,
          titleColor: theme.textStrong,
          bodyColor: theme.text,
          borderColor: theme.tooltipBorder,
          borderWidth: 1,
          padding: 12,
          callbacks: {
            title(items) {
              const item = items[0];
              return item ? `Ticket ${item.label}` : '';
            },
            label(context) {
              if (!hasData && context.dataset.label === 'Resolution time') return 'No data yet';
              const y = Number(context.parsed.y);
              if (!Number.isFinite(y)) return context.dataset.label;
              if (context.dataset.label.startsWith('SLA')) {
                return `${context.dataset.label}`;
              }
              const breached = y > SLA_DAYS ? ' (over SLA)' : ' (within SLA)';
              return `Resolution: ${formatDays(y)}${breached}`;
            },
          },
        },
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: {
            maxRotation: 35,
            minRotation: 0,
            autoSkip: true,
            maxTicksLimit: 8,
            color: theme.text,
            font: { size: 11 },
          },
        },
        y: {
          beginAtZero: true,
          max: yMax,
          ticks: {
            color: theme.text,
            maxTicksLimit: 6,
            callback: (value) => formatDays(value),
          },
          grid: {
            color: theme.grid,
          },
        },
      },
    },
  });

  document.dispatchEvent(new CustomEvent('it-charts-ready'));
}
