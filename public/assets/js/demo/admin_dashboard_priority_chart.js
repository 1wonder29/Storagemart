// Admin dashboard — tickets by priority (pie chart)
(function () {
  const el = document.getElementById('adminTicketPriorityChart');
  if (!el || !window.Chart || !window.ticketPriorityCounts) return;

  const priorityMeta = [
    { key: 'High (P2)', color: '#fd7e14' },
    { key: 'Medium (P3)', color: '#f6c23e' },
    { key: 'Low (P4)', color: '#1cc88a' }
  ];

  const counts = window.ticketPriorityCounts;
  const labels = [];
  const values = [];
  const backgroundColor = [];

  priorityMeta.forEach(function (item) {
    const value = Number(counts[item.key]) || 0;
    if (value <= 0) {
      return;
    }
    labels.push(item.key);
    values.push(value);
    backgroundColor.push(item.color);
  });

  const total = values.reduce(function (a, b) {
    return a + b;
  }, 0);
  const hasData = total > 0;

  const percentLabelPlugin = {
    id: 'priorityPercentLabels',
    afterDatasetsDraw: function (chart) {
      if (!hasData) return;

      const ctx = chart.ctx;
      const meta = chart.getDatasetMeta(0);

      meta.data.forEach(function (arc, index) {
        const value = Number(values[index]) || 0;
        if (value <= 0) return;

        const pct = total ? Math.round((value / total) * 100) : 0;
        const props = arc.getProps(['x', 'y', 'startAngle', 'endAngle', 'innerRadius', 'outerRadius'], true);
        const angle = (props.startAngle + props.endAngle) / 2;
        const radius = (props.innerRadius + props.outerRadius) / 2;
        const x = props.x + Math.cos(angle) * radius;
        const y = props.y + Math.sin(angle) * radius;

        ctx.save();
        ctx.fillStyle = '#fff';
        ctx.font = "700 12px 'Nunito', sans-serif";
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(pct + '%', x, y);
        ctx.restore();
      });
    }
  };

  new Chart(el, {
    type: 'pie',
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
    plugins: [percentLabelPlugin],
    options: {
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
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
              const pct = total ? ((v / total) * 100).toFixed(2) : '0.00';
              return v + ' (' + pct + '%)';
            }
          }
        }
      }
    }
  });
})();
