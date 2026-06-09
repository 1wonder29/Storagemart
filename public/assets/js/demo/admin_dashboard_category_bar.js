// Admin dashboard ticket category bar chart (Chart.js v3+ via CDN)
(function () {
  const el = document.getElementById('adminTicketCategoryChart');
  if (!el || !window.Chart || !window.ticketCategoryCounts) return;

  const counts = window.ticketCategoryCounts;
  const labels = ['Network', 'Software', 'Hardware'];
  const values = [
    Number(counts.network) || 0,
    Number(counts.software) || 0,
    Number(counts.hardware) || 0
  ];
  const total = values.reduce((a, b) => a + b, 0);

  new Chart(el, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Filed Tickets',
          data: values,
          backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
          borderColor: ['#2e59d9', '#17a673', '#2c9faf'],
          borderWidth: 1,
          borderRadius: 4,
          maxBarThickness: 72
        }
      ]
    },
    options: {
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#fff',
          bodyColor: '#858796',
          borderColor: '#dddfeb',
          borderWidth: 1,
          padding: 12,
          callbacks: {
            label: function (ctx) {
              const v = Number(ctx.parsed.y) || 0;
              const pct = total ? Math.round((v / total) * 100) : 0;
              return `${v} ticket${v === 1 ? '' : 's'} (${pct}%)`;
            }
          }
        }
      },
      scales: {
        x: {
          grid: { display: false }
        },
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0,
            stepSize: 1
          },
          grid: {
            color: 'rgb(234, 236, 244)'
          },
          title: {
            display: true,
            text: 'Number of Tickets'
          }
        }
      }
    }
  });
})();
