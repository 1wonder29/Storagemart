// Admin dashboard ticket status doughnut chart (Chart.js v3+ via CDN)
(function () {
  const el = document.getElementById('adminTicketStatusChart');
  if (!el || !window.Chart || !window.ticketStatusCounts) return;

  const statusColors = {
    Pending: '#f6c23e',
    'In Progress': '#36b9cc',
    Resolved: '#1cc88a',
    Closed: '#858796',
    Cancelled: '#e74a3b',
    Ongoing: '#4e73df'
  };
  const fallbackColors = ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b', '#36b9cc', '#858796'];

  const counts = window.ticketStatusCounts;
  const labels = Object.keys(counts);
  const values = labels.map((label) => Number(counts[label]) || 0);
  const total = values.reduce((a, b) => a + b, 0);
  const hasData = total > 0;

  const backgroundColor = labels.map((label, i) =>
    statusColors[label] || fallbackColors[i % fallbackColors.length]
  );

  new Chart(el, {
    type: 'doughnut',
    data: {
      labels: hasData ? labels : ['No tickets'],
      datasets: [
        {
          data: hasData ? values : [1],
          backgroundColor: hasData ? backgroundColor : ['#eaecf4'],
          hoverBackgroundColor: hasData
            ? backgroundColor.map((color) => color)
            : ['#dddfeb'],
          borderColor: '#fff',
          borderWidth: 2
        }
      ]
    },
    options: {
      maintainAspectRatio: false,
      cutout: '65%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: { usePointStyle: true, padding: 14 }
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
