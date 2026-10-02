// Admin dashboard — tickets by branch (vertical bar chart)
(function () {
  const el = document.getElementById('adminTicketBranchChart');
  if (!el || !window.Chart || !window.ticketBranchCounts) return;

  const counts = window.ticketBranchCounts;
  const palette = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69'];

  let labels = [];
  let values = [];

  if (Array.isArray(counts)) {
    counts.forEach(function (item) {
      if (!item || !item.label) return;
      labels.push(String(item.label));
      values.push(Number(item.count) || 0);
    });
  } else if (counts && typeof counts === 'object') {
    labels = Object.keys(counts);
    values = labels.map(function (label) {
      return Number(counts[label]) || 0;
    });
  }

  const total = values.reduce(function (a, b) {
    return a + b;
  }, 0);
  const maxValue = Math.max.apply(null, values.concat([0]));
  const backgroundColor = labels.map(function (_, index) {
    return palette[index % palette.length];
  });

  new Chart(el, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Tickets',
          data: values,
          backgroundColor: backgroundColor,
          borderColor: backgroundColor,
          borderWidth: 0,
          borderRadius: { topLeft: 6, topRight: 6, bottomLeft: 0, bottomRight: 0 },
          borderSkipped: false,
          maxBarThickness: 48,
          barPercentage: 0.72,
          categoryPercentage: 0.8
        }
      ]
    },
    options: {
      indexAxis: 'x',
      maintainAspectRatio: false,
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#fff',
          titleColor: '#0f172a',
          bodyColor: '#64748b',
          borderColor: 'rgba(1, 43, 144, 0.1)',
          borderWidth: 1,
          padding: 12,
          displayColors: true,
          callbacks: {
            label: function (ctx) {
              const v = Number(ctx.parsed.y) || 0;
              const pct = total ? Math.round((v / total) * 100) : 0;
              return v + ' ticket' + (v === 1 ? '' : 's') + ' (' + pct + '%)';
            }
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          border: { display: false },
          ticks: {
            autoSkip: labels.length > 8,
            maxRotation: 45,
            minRotation: labels.length > 4 ? 30 : 0,
            font: { family: "'Nunito', sans-serif", size: 11 },
            color: '#5a5c69'
          }
        },
        y: {
          beginAtZero: true,
          suggestedMax: maxValue > 0 ? maxValue + 1 : 4,
          ticks: {
            precision: 0,
            stepSize: 1,
            font: { family: "'Nunito', sans-serif", size: 12 },
            color: '#858796'
          },
          grid: {
            color: 'rgb(234, 236, 244)'
          },
          border: { display: false },
          title: {
            display: true,
            text: 'Number of tickets',
            color: '#5a5c69',
            font: { family: "'Nunito', sans-serif", size: 12, weight: '600' }
          }
        }
      }
    }
  });
})();
