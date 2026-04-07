// Ticket Resolution Time Area Chart (Chart.js v3+)

const areaCtx = document.getElementById("myAreaChart");

if (areaCtx && window.ticketResolution) {
  const SLA_HOURS = 8;

  new Chart(areaCtx, {
    type: 'line',
    data: {
      labels: window.ticketResolution.labels,
      datasets: [
        {
          label: "Resolution Time (hours)",
          data: window.ticketResolution.data,
          tension: 0.3,
          fill: true,
          backgroundColor: "rgba(78, 115, 223, 0.05)",
          borderColor: "rgba(78, 115, 223, 1)",
          borderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
          pointBackgroundColor: window.ticketResolution.data.map(v =>
            v > SLA_HOURS ? '#e74a3b' : '#4e73df'
          ),
          pointBorderColor: "#fff",
        },
        {
          label: "SLA (8 hrs)",
          data: Array(window.ticketResolution.data.length).fill(SLA_HOURS),
          borderColor: "#e74a3b",
          borderDash: [6, 6],
          pointRadius: 0,
          fill: false
        }
      ]
    },
    options: {
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'bottom'
        },
        tooltip: {
          backgroundColor: "#fff",
          bodyColor: "#858796",
          borderColor: "#dddfeb",
          borderWidth: 1,
          padding: 12,
          callbacks: {
            label: function (context) {
              return `${context.dataset.label}: ${context.parsed.y} hrs`;
            }
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: {
            maxRotation: 45,
            minRotation: 30
          }
        },
        y: {
          beginAtZero: true,
          ticks: {
            callback: value => value + ' hrs'
          },
          grid: {
            color: "rgb(234, 236, 244)"
          }
        }
      }
    }
  });
}
