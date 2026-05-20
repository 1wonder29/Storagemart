// Chart.js v3+ defaults (SB Admin look)
if (window.Chart) {
  Chart.defaults.font.family =
    'Nunito, -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
  Chart.defaults.color = '#858796';
}

// Center text plugin
const centerText = {
  id: 'centerText',
  afterDraw(chart) {
    const { ctx } = chart;
    const meta = chart.getDatasetMeta(0);
    if (!meta?.data?.length) return;

    const total = chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
    const { x, y } = meta.data[0];

    ctx.save();
    ctx.font = 'bold 22px Nunito';
    ctx.fillStyle = '#5a5c69';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(total, x, y - 8);
    ctx.font = '600 12px Nunito';
    ctx.fillStyle = '#858796';
    ctx.fillText('tickets', x, y + 12);
    ctx.restore();
  }
};

// Chart
var ctx = document.getElementById("ticketChart");

if (ctx && window.ticketData) {
  // Register plugin once (Chart.js v3+)
  if (window.Chart && typeof Chart.register === 'function' && !Chart.registry.plugins.get('centerText')) {
    Chart.register(centerText);
  }

  const raw = Array.isArray(window.ticketData) ? window.ticketData.map(n => Number(n) || 0) : [];
  const total = raw.reduce((a, b) => a + b, 0);
  const chartData = total > 0 ? raw : [1, 1, 1]; // empty-state placeholder

  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ["Assigned", "In Progress", "Resolved"],
      datasets: [{
        data: chartData,
        backgroundColor: ['#36b9cc','#1cc88a', '#f6c23e'],
        hoverBackgroundColor: ['#2c9faf','#17a673','#dda20a'],
        borderColor: "rgba(234, 236, 244, 1)",
      }]
    },
    options: {
      maintainAspectRatio: false,
      cutout: '80%',
      plugins: {
        legend: {
          display: true,
          position: 'bottom',
          labels: {
            usePointStyle: true,
            padding: 20
          }
        },
        tooltip: {
          backgroundColor: "rgb(255,255,255)",
          bodyColor: "#858796",
          borderColor: '#dddfeb',
          borderWidth: 1,
          padding: 15,
          displayColors: true,
          caretPadding: 10,
          callbacks: {
            label: function(context) {
              if (total <= 0) return 'No data yet';
              const value = Number(context.parsed) || 0;
              const pct = total > 0 ? Math.round((value / total) * 100) : 0;
              return `${context.label}: ${value} (${pct}%)`;
            }
          }
        }
      }
    }

  });
}
