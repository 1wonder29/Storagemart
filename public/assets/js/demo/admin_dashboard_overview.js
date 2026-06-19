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
          backgroundColor: ['#4f46e5', '#16a34a', '#0891b2', '#f37021'],
          hoverBackgroundColor: ['#4338ca', '#15803d', '#0e7490', '#de6126'],
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
          labels: {
            usePointStyle: true,
            padding: 16,
            font: { family: "'Nunito', sans-serif", size: 12 }
          }
        },
        tooltip: {
          backgroundColor: '#fff',
          titleColor: '#0f172a',
          bodyColor: '#64748b',
          borderColor: 'rgba(1, 43, 144, 0.1)',
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

