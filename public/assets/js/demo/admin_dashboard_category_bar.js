// Admin dashboard — tickets by category (doughnut chart)
(function () {
  const el = document.getElementById('adminTicketCategoryChart');
  if (!el || !window.Chart || !window.ticketCategoryCounts) return;

  const categoryColors = {
    Hardware: '#4e73df',
    Network: '#1cc88a',
    Software: '#f6c23e',
    Other: '#36b9cc'
  };
  const fallbackColors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'];

  const counts = window.ticketCategoryCounts;
  let labels = [];
  let values = [];

  if (Array.isArray(counts)) {
    counts.forEach(function (item) {
      if (!item || !item.label) return;
      labels.push(String(item.label));
      values.push(Number(item.count) || 0);
    });
  } else if (counts && typeof counts === 'object') {
    labels = Object.keys(counts).filter(function (label) {
      return Number(counts[label]) > 0;
    });
    values = labels.map(function (label) {
      return Number(counts[label]) || 0;
    });
  }

  const total = values.reduce(function (a, b) {
    return a + b;
  }, 0);
  const hasData = total > 0;

  const backgroundColor = labels.map(function (label, i) {
    return categoryColors[label] || fallbackColors[i % fallbackColors.length];
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
