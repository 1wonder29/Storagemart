// Admin dashboard ticket status doughnut chart (Chart.js v3+ via CDN)
(function () {
  const el = document.getElementById('adminTicketStatusChart');
  if (!el || !window.Chart || !window.ticketStatusCounts) return;

  const statusColors = {
    Open: '#3b82f6',
    Pending: '#f6c23e',
    'In Progress': '#36b9cc',
    Resolved: '#1cc88a',
    Closed: '#858796',
    Cancelled: '#e74a3b'
  };
  const fallbackColors = ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b', '#36b9cc', '#858796'];

  const counts = window.ticketStatusCounts;
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
    return statusColors[label] || fallbackColors[i % fallbackColors.length];
  });

  new Chart(el, {
    type: 'doughnut',
    data: {
      labels: hasData ? labels : ['No tickets'],
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
