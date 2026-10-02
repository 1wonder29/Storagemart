// Admin dashboard — IT personnel workload (stacked horizontal bar chart)
(function () {
  const el = document.getElementById('adminItWorkloadChart');
  if (!el || !window.Chart || !window.itPersonnelWorkload) return;

  const rows = Array.isArray(window.itPersonnelWorkload) ? window.itPersonnelWorkload : [];
  const labels = rows.map(function (row) {
    return row.name || 'Unknown';
  });

  const sumRow = function (row) {
    return (Number(row.assigned) || 0)
      + (Number(row.resolved) || 0)
      + (Number(row.pending) || 0)
      + (Number(row.overdue) || 0);
  };

  const hasData = rows.length > 0 && rows.some(function (row) {
    return sumRow(row) > 0;
  });

  const assigned = rows.map(function (row) {
    return Number(row.assigned) || 0;
  });
  const resolved = rows.map(function (row) {
    return Number(row.resolved) || 0;
  });
  const pending = rows.map(function (row) {
    return Number(row.pending) || 0;
  });
  const overdue = rows.map(function (row) {
    return Number(row.overdue) || 0;
  });

  const datasets = [
    {
      label: 'Assigned',
      data: hasData ? assigned : [0],
      backgroundColor: '#4e73df',
      borderColor: '#fff',
      borderWidth: 1,
      borderRadius: 4,
      barThickness: 28
    },
    {
      label: 'Resolved',
      data: hasData ? resolved : [0],
      backgroundColor: '#1cc88a',
      borderColor: '#fff',
      borderWidth: 1,
      borderRadius: 4,
      barThickness: 28
    },
    {
      label: 'Pending',
      data: hasData ? pending : [0],
      backgroundColor: '#f6c23e',
      borderColor: '#fff',
      borderWidth: 1,
      borderRadius: 4,
      barThickness: 28
    },
    {
      label: 'Overdue',
      data: hasData ? overdue : [0],
      backgroundColor: '#e74a3b',
      borderColor: '#fff',
      borderWidth: 1,
      borderRadius: 4,
      barThickness: 28
    }
  ];

  const valueLabelPlugin = {
    id: 'workloadValueLabels',
    afterDatasetsDraw: function (chart) {
      const ctx = chart.ctx;
      chart.data.datasets.forEach(function (dataset, datasetIndex) {
        const meta = chart.getDatasetMeta(datasetIndex);
        if (meta.hidden) return;

        meta.data.forEach(function (bar, index) {
          const value = Number(dataset.data[index]) || 0;
          if (value <= 0) return;

          const x = bar.x;
          const y = bar.y;
          const base = bar.base;
          const centerX = (x + base) / 2;
          const segmentWidth = Math.abs(x - base);

          ctx.save();
          ctx.fillStyle = '#fff';
          ctx.font = "600 11px 'Nunito', sans-serif";
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';
          if (segmentWidth >= 22) {
            ctx.fillText(String(value), centerX, y);
          }
          ctx.restore();
        });
      });
    }
  };

  new Chart(el, {
    type: 'bar',
    data: {
      labels: hasData ? labels : ['No IT workload data'],
      datasets: datasets
    },
    plugins: [valueLabelPlugin],
    options: {
      indexAxis: 'y',
      maintainAspectRatio: false,
      responsive: true,
      plugins: {
        legend: {
          position: 'top',
          align: 'center',
          labels: {
            usePointStyle: true,
            pointStyle: 'rect',
            boxWidth: 12,
            padding: 18,
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
              const v = Number(ctx.parsed.x) || 0;
              return ctx.dataset.label + ': ' + v;
            }
          }
        }
      },
      scales: {
        x: {
          stacked: true,
          beginAtZero: true,
          ticks: {
            precision: 0,
            stepSize: 1,
            font: { family: "'Nunito', sans-serif", size: 12 },
            color: '#858796'
          },
          grid: {
            color: 'rgb(234, 236, 244)'
          },
          border: { display: false }
        },
        y: {
          stacked: true,
          grid: { display: false },
          border: { display: false },
          ticks: {
            font: { family: "'Nunito', sans-serif", size: 12 },
            color: '#5a5c69'
          }
        }
      }
    }
  });
})();
