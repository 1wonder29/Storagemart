// Chart.js v3+ defaults (SB Admin look)
Chart.defaults.font.family =
  'Nunito, -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
Chart.defaults.color = '#858796';

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
    ctx.fillText(total, x, y);
    ctx.restore();
  }
};

// Chart
var ctx = document.getElementById("ticketChart");

if (ctx && window.ticketData) {
  new Chart(ctx, {
    type: 'doughnut',
    plugins: [centerText], // ✅ REGISTER PLUGIN
    data: {
      labels: ["Pending", "Resolved", "Total Tickets"],
      datasets: [{
        data: window.ticketData,
        backgroundColor: ['#f6c23e', '#36b9cc', '#1cc88a'],
        hoverBackgroundColor: ['#dda20a', '#2c9faf', '#17a673'],
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
              return `${context.label}: ${context.parsed}`;
            }
          }
        }
      }
    }

  });
}
