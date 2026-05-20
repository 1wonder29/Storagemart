// Admin dashboard overview chart (Chart.js v3+ via CDN)
(function () {
  const el = document.getElementById('adminOverviewChart');
  if (!el || !window.Chart || !window.adminOverviewData) return;

  const data = {
    Users: Number(window.adminOverviewData.users) || 0,
    Tickets: Number(window.adminOverviewData.tickets) || 0,
    Assets: Number(window.adminOverviewData.assets) || 0,
    'On-going': Number(window.adminOverviewData.ongoing) || 0
  };

  const labels = Object.keys(data);
  const values = Object.values(data);
  const total = values.reduce((a, b) => a + b, 0);
  const hasData = total > 0;

  new Chart(el, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [
        {
          data: hasData ? values : [1, 1, 1, 1], // placeholder for empty state
          backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'],
          hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a'],
          borderColor: 'rgba(234, 236, 244, 1)'
        }
      ]
    },
    options: {
      maintainAspectRatio: false,
      cutout: '75%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: { usePointStyle: true, padding: 16 }
        },
        tooltip: {
          backgroundColor: '#fff',
          bodyColor: '#858796',
          borderColor: '#dddfeb',
          borderWidth: 1,
          padding: 12,
          callbacks: {
            label: function (ctx) {
              if (!hasData) return 'No data yet';
              const v = Number(ctx.parsed) || 0;
              const pct = total ? Math.round((v / total) * 100) : 0;
              return `${ctx.label}: ${v} (${pct}%)`;
            }
          }
        }
      }
    }
  });
})();

