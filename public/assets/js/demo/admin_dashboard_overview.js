// Admin dashboard system overview doughnut chart (Chart.js v3+ via CDN)
(function () {
  const el = document.getElementById('adminOverviewChart');
  if (!el || !window.Chart || !window.adminOverviewData) return;

  const overviewColors = {
    Users: '#4e73df',
    Tickets: '#1cc88a',
    Assets: '#36b9cc',
    'In Progress': '#f6c23e'
  };
  const fallbackColors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'];

  const counts = {
    Users: Number(window.adminOverviewData.users) || 0,
    Tickets: Number(window.adminOverviewData.tickets) || 0,
    Assets: Number(window.adminOverviewData.assets) || 0,
    'In Progress': Number(window.adminOverviewData.inProgress ?? window.adminOverviewData.ongoing) || 0
  };

  const labels = Object.keys(counts).filter(function (label) {
    return Number(counts[label]) > 0;
  });
  const values = labels.map(function (label) {
    return Number(counts[label]) || 0;
  });
  const total = values.reduce(function (a, b) {
    return a + b;
  }, 0);
  const hasData = total > 0;

  const backgroundColor = labels.map(function (label, i) {
    return overviewColors[label] || fallbackColors[i % fallbackColors.length];
  });

  new Chart(el, {
    type: 'doughnut',
    data: {
      labels: hasData ? labels : ['No data'],
      datasets: [
        {
          data: hasData ? values : [1],
          backgroundColor: hasData ? backgroundColor : ['#eaecf4'],
          hoverBackgroundColor: hasData ? backgroundColor : ['#dddfeb'],
          borderColor: '#fff',
          borderWidth: 2
        }
      ]
    },
    options: {
      maintainAspectRatio: false,
      cutout: '62%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            usePointStyle: true,
            pointStyle: 'circle',
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
              return ctx.label + ': ' + v + ' (' + pct + '%)';
            }
          }
        }
      }
    }
  });
})();
