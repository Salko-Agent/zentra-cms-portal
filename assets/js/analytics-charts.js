// ===== Zentra Analytics – Chart.js Wrapper Functions =====
// Requires Chart.js 4.x loaded via CDN

const CHART_COLORS = {
  accent:     '#3D8EFF',
  accentHover:'#60A3FF',
  green:      '#22c55e',
  red:        '#ef4444',
  gold:       '#eab308',
  orange:     '#f97316',
  purple:     '#a855f7',
  cyan:       '#06b6d4',
  grid:       '#1C2E4A',
  text:       '#6E8BAD',
  textLight:  '#DCE9F8',
  surface:    '#0C1526',
};

const PALETTE = [
  CHART_COLORS.accent,
  CHART_COLORS.green,
  CHART_COLORS.gold,
  CHART_COLORS.orange,
  CHART_COLORS.purple,
  CHART_COLORS.cyan,
  CHART_COLORS.red,
  '#ec4899',
];

// Global Chart.js defaults for dark theme
if (typeof Chart !== 'undefined') {
  Chart.defaults.color = CHART_COLORS.text;
  Chart.defaults.borderColor = CHART_COLORS.grid;
  Chart.defaults.plugins.legend.labels.boxWidth = 12;
  Chart.defaults.plugins.legend.labels.padding = 16;
  Chart.defaults.plugins.tooltip.backgroundColor = '#0C1526';
  Chart.defaults.plugins.tooltip.borderColor = CHART_COLORS.grid;
  Chart.defaults.plugins.tooltip.borderWidth = 1;
  Chart.defaults.plugins.tooltip.titleColor = CHART_COLORS.textLight;
  Chart.defaults.plugins.tooltip.bodyColor = CHART_COLORS.text;
  Chart.defaults.plugins.tooltip.padding = 10;
  Chart.defaults.plugins.tooltip.cornerRadius = 8;
}

/**
 * Create a line chart (e.g. daily visitors).
 */
function createLineChart(canvasId, labels, datasets) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return null;

  const ds = datasets.map((d, i) => ({
    label: d.label,
    data: d.data,
    borderColor: d.color || PALETTE[i],
    backgroundColor: (d.color || PALETTE[i]) + '20',
    fill: d.fill !== false,
    tension: 0.3,
    pointRadius: 2,
    pointHoverRadius: 5,
    borderWidth: 2,
  }));

  return new Chart(ctx, {
    type: 'line',
    data: { labels, datasets: ds },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { intersect: false, mode: 'index' },
      scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: true },
      },
    },
  });
}

/**
 * Create a bar chart (e.g. top pages).
 */
function createBarChart(canvasId, labels, values, color) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return null;

  return new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        data: values,
        backgroundColor: (color || CHART_COLORS.accent) + '80',
        borderColor: color || CHART_COLORS.accent,
        borderWidth: 1,
        borderRadius: 4,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      indexAxis: 'y',
      plugins: { legend: { display: false } },
      scales: {
        x: { beginAtZero: true },
        y: {
          ticks: {
            callback: function(val) {
              const label = this.getLabelForValue(val);
              return label.length > 30 ? label.substring(0, 27) + '...' : label;
            },
          },
        },
      },
    },
  });
}

/**
 * Create a doughnut chart (e.g. traffic sources).
 */
function createDoughnutChart(canvasId, labels, values) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return null;

  return new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data: values,
        backgroundColor: PALETTE.slice(0, values.length),
        borderColor: CHART_COLORS.surface,
        borderWidth: 2,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '65%',
      plugins: {
        legend: {
          position: 'right',
          labels: { font: { size: 12 } },
        },
      },
    },
  });
}

/**
 * Create a gauge/score chart (circular progress).
 */
function createGaugeChart(canvasId, score, label, color) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return null;

  if (!color) {
    color = score >= 90 ? CHART_COLORS.green : (score >= 50 ? CHART_COLORS.gold : CHART_COLORS.red);
  }

  const chart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      datasets: [{
        data: [score, 100 - score],
        backgroundColor: [color, CHART_COLORS.grid],
        borderWidth: 0,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      cutout: '78%',
      rotation: -90,
      circumference: 360,
      plugins: {
        legend: { display: false },
        tooltip: { enabled: false },
      },
    },
    plugins: [{
      id: 'centerText',
      afterDraw(chart) {
        const { ctx: c, width, height } = chart;
        c.save();
        c.font = 'bold 1.5rem Inter, sans-serif';
        c.fillStyle = color;
        c.textAlign = 'center';
        c.textBaseline = 'middle';
        c.fillText(score, width / 2, height / 2 - 6);
        if (label) {
          c.font = '0.65rem Inter, sans-serif';
          c.fillStyle = CHART_COLORS.text;
          c.fillText(label, width / 2, height / 2 + 16);
        }
        c.restore();
      },
    }],
  });

  return chart;
}

/**
 * Helper: Format a date label for charts (DD.MM).
 */
function formatDateLabel(dateStr) {
  const d = new Date(dateStr);
  return String(d.getDate()).padStart(2, '0') + '.' + String(d.getMonth() + 1).padStart(2, '0');
}
