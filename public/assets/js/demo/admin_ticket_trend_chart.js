// Admin ticket report — monthly ticket trend line chart
(function () {
  const el = document.getElementById('monthlyTicketTrendChart');
  if (!el || !window.Chart || !window.monthlyTicketTrend) {
    return;
  }

  const labels = Array.isArray(window.monthlyTicketTrend.labels)
    ? window.monthlyTicketTrend.labels
    : [];
  const series = Array.isArray(window.monthlyTicketTrend.data)
    ? window.monthlyTicketTrend.data.map(function (value) {
        const n = Number(value);
        return Number.isFinite(n) ? n : 0;
      })
    : [];

  const hasData = series.some(function (value) {
    return value > 0;
  });
  const maxValue = Math.max.apply(null, hasData ? series : [0]);
  const yMax = Math.max(5, Math.ceil((maxValue + 1) / 5) * 5);

  const valueLabelPlugin = {
    id: 'monthlyTrendValueLabels',
    afterDatasetsDraw: function (chart) {
      const ctx = chart.ctx;
      const meta = chart.getDatasetMeta(0);

      meta.data.forEach(function (point, index) {
        const value = Number(series[index]) || 0;
        if (value <= 0) {
          return;
        }

        const props = point.getProps(['x', 'y'], true);
        ctx.save();
        ctx.fillStyle = '#1e3a8a';
        ctx.font = "700 11px 'Nunito', sans-serif";
        ctx.textAlign = 'center';
        ctx.textBaseline = 'bottom';
        ctx.fillText(String(value), props.x, props.y - 8);
        ctx.restore();
      });
    },
  };

  new Chart(el, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Tickets filed',
          data: series,
          tension: 0.35,
          fill: true,
          backgroundColor: 'rgba(78, 115, 223, 0.12)',
          borderColor: '#4e73df',
          borderWidth: 2.5,
          pointRadius: 5,
          pointHoverRadius: 7,
          pointBackgroundColor: '#4e73df',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
        },
      ],
    },
    plugins: [valueLabelPlugin],
    options: {
      maintainAspectRatio: false,
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#fff',
          titleColor: '#0f172a',
          bodyColor: '#64748b',
          borderColor: 'rgba(78, 115, 223, 0.2)',
          borderWidth: 1,
          padding: 12,
          callbacks: {
            label: function (ctx) {
              return 'Tickets: ' + (Number(ctx.parsed.y) || 0);
            },
          },
        },
      },
      scales: {
        x: {
          grid: { display: false },
          border: { display: false },
          ticks: {
            font: { family: "'Nunito', sans-serif", size: 12 },
            color: '#64748b',
          },
        },
        y: {
          beginAtZero: true,
          suggestedMax: yMax,
          ticks: {
            precision: 0,
            stepSize: Math.max(1, Math.ceil(yMax / 5)),
            font: { family: "'Nunito', sans-serif", size: 12 },
            color: '#64748b',
          },
          grid: {
            color: 'rgba(148, 163, 184, 0.2)',
          },
          border: { display: false },
        },
      },
    },
  });
})();
