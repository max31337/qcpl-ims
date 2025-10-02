import ApexCharts from 'apexcharts';

let analyticsCharts = {};

// Create line chart for assets created
function createAnalyticsLineChart(el, labels = [], data = []) {
  if (analyticsCharts.assetsLine) analyticsCharts.assetsLine.destroy();
  const options = {
    chart: { 
      type: 'line', 
      height: '100%', 
      toolbar: { show: false },
      background: 'transparent'
    },
    series: [{ name: 'Assets created', data }],
    xaxis: { 
      categories: labels,
      labels: {
        style: {
          fontSize: '11px',
          fontWeight: 500
        }
      }
    },
    stroke: { curve: 'smooth', width: 3 },
    colors: ['#3b82f6'],
    fill: { 
      type: 'gradient', 
      gradient: { 
        shade: 'light',
        type: 'vertical',
        shadeIntensity: 0.5, 
        gradientToColors: ['#93c5fd'], 
        opacityFrom: 0.4, 
        opacityTo: 0.1 
      } 
    },
    markers: { 
      size: 5, 
      colors: ['#3b82f6'],
      strokeWidth: 2, 
      strokeColors: '#ffffff',
      hover: { size: 7 }
    },
    grid: { 
      strokeDashArray: 3,
      borderColor: '#e5e7eb'
    },
    tooltip: { 
      theme: 'dark',
      style: {
        fontSize: '12px'
      }
    },
    legend: { show: false },
    yaxis: { 
      min: 0,
      labels: { 
        style: {
          fontSize: '11px',
          fontWeight: 500
        },
        formatter: function (val) {
          return Math.floor(val);
        }
      }
    }
  };
  analyticsCharts.assetsLine = new ApexCharts(el, options);
  analyticsCharts.assetsLine.render();
  return analyticsCharts.assetsLine;
}

// Create bar chart for supplies added
function createAnalyticsBarChart(el, labels = [], data = [], color = '#6366f1') {
  const chartKey = `bar_${el.id}`;
  if (analyticsCharts[chartKey]) analyticsCharts[chartKey].destroy();
  
  // Generate gradient colors based on the base color
  let gradientColor = color;
  if (color === '#6366f1') gradientColor = '#a5b4fc'; // indigo gradient
  if (color === '#10b981') gradientColor = '#6ee7b7'; // emerald gradient
  if (color === '#f59e0b') gradientColor = '#fcd34d'; // amber gradient
  
  const options = {
    chart: { 
      type: 'bar', 
      height: '100%', 
      toolbar: { show: false },
      background: 'transparent'
    },
    series: [{ name: 'Count', data }],
    xaxis: { 
      categories: labels,
      labels: {
        style: {
          fontSize: '11px',
          fontWeight: 500
        }
      }
    },
    plotOptions: { 
      bar: { 
        borderRadius: 6, 
        columnWidth: '50%', 
        distributed: false,
        dataLabels: {
          position: 'top'
        }
      } 
    },
    dataLabels: { 
      enabled: true, 
      offsetY: -20,
      style: { 
        fontSize: '11px', 
        fontWeight: 'bold',
        colors: [color]
      }
    },
    colors: [color],
    fill: {
      type: 'gradient',
      gradient: {
        shade: 'light',
        type: 'vertical',
        shadeIntensity: 0.3,
        gradientToColors: [gradientColor],
        opacityFrom: 0.9,
        opacityTo: 0.6,
        stops: [0, 100]
      }
    },
    grid: { 
      strokeDashArray: 3,
      borderColor: '#e5e7eb',
      yaxis: { lines: { show: true } },
      xaxis: { lines: { show: false } }
    },
    tooltip: { 
      theme: 'dark',
      style: {
        fontSize: '12px'
      }
    },
    legend: { show: false },
    yaxis: { 
      min: 0,
      labels: { 
        style: {
          fontSize: '11px',
          fontWeight: 500
        },
        formatter: function (val) {
          return Math.floor(val);
        }
      }
    }
  };
  analyticsCharts[chartKey] = new ApexCharts(el, options);
  analyticsCharts[chartKey].render();
  return analyticsCharts[chartKey];
}

// Create distributed bar chart (like supplies stock health)
function createDistributedBarChart(el, labels = [], data = [], colors = []) {
  const chartKey = `distributed_bar_${el.id}`;
  if (analyticsCharts[chartKey]) analyticsCharts[chartKey].destroy();
  
  // Generate gradient colors for each bar
  const gradientColors = colors.map(color => {
    if (color === '#ef4444') return '#fca5a5'; // red gradient
    if (color === '#f59e0b') return '#fcd34d'; // amber gradient  
    if (color === '#10b981') return '#6ee7b7'; // emerald gradient
    return color;
  });
  
  const options = {
    chart: { 
      type: 'bar', 
      height: '100%', 
      toolbar: { show: false },
      background: 'transparent'
    },
    series: [{ name: 'Count', data }],
    xaxis: { 
      categories: labels,
      labels: {
        style: {
          fontSize: '11px',
          fontWeight: 500
        }
      }
    },
    plotOptions: { 
      bar: { 
        borderRadius: 6, 
        columnWidth: '50%', 
        distributed: true,
        dataLabels: {
          position: 'top'
        }
      } 
    },
    dataLabels: { 
      enabled: true, 
      offsetY: -20,
      style: { 
        fontSize: '11px', 
        fontWeight: 'bold'
      }
    },
    colors: colors,
    fill: {
      type: 'gradient',
      gradient: {
        shade: 'light',
        type: 'vertical',
        shadeIntensity: 0.3,
        gradientToColors: gradientColors,
        opacityFrom: 0.9,
        opacityTo: 0.6,
        stops: [0, 100]
      }
    },
    grid: { 
      strokeDashArray: 3,
      borderColor: '#e5e7eb',
      yaxis: { lines: { show: true } },
      xaxis: { lines: { show: false } }
    },
    tooltip: { 
      theme: 'dark',
      style: {
        fontSize: '12px'
      }
    },
    legend: { show: false },
    yaxis: { 
      min: 0,
      labels: { 
        style: {
          fontSize: '11px',
          fontWeight: 500
        },
        formatter: function (val) {
          return Math.floor(val);
        }
      }
    }
  };
  analyticsCharts[chartKey] = new ApexCharts(el, options);
  analyticsCharts[chartKey].render();
  return analyticsCharts[chartKey];
}

// Create donut chart
function createAnalyticsDonutChart(el, labels = [], data = [], colors = []) {
  const chartKey = `donut_${el.id}`;
  if (analyticsCharts[chartKey]) analyticsCharts[chartKey].destroy();
  const options = {
    chart: { type: 'donut', height: '100%', toolbar: { show: false } },
    series: data,
    labels,
    colors,
    legend: { show: false },
    dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 'bold' } },
    stroke: { width: 2, colors: ['#ffffff'] },
    tooltip: { theme: 'dark' },
    plotOptions: { pie: { donut: { size: '65%', labels: { show: false } } } }
  };
  analyticsCharts[chartKey] = new ApexCharts(el, options);
  analyticsCharts[chartKey].render();
  return analyticsCharts[chartKey];
}

// Create pie chart
function createAnalyticsPieChart(el, labels = [], data = [], colors = []) {
  const chartKey = `pie_${el.id}`;
  if (analyticsCharts[chartKey]) analyticsCharts[chartKey].destroy();
  const options = {
    chart: { type: 'pie', height: '100%', toolbar: { show: false } },
    series: data,
    labels,
    colors,
    legend: { show: false },
    dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 'bold' } },
    stroke: { width: 2, colors: ['#ffffff'] },
    tooltip: { theme: 'dark' }
  };
  analyticsCharts[chartKey] = new ApexCharts(el, options);
  analyticsCharts[chartKey].render();
  return analyticsCharts[chartKey];
}

function renderAnalyticsCharts(payload) {
  try {
    // Assets line chart
    const assetsLineEl = document.getElementById('assetsAnalyticsLine');
    if (assetsLineEl && payload.labels && payload.assetsMonthly) {
      createAnalyticsLineChart(assetsLineEl, payload.labels, payload.assetsMonthly);
    }

    // Supplies bar chart
    const suppliesBarEl = document.getElementById('suppliesAnalyticsBar');
    if (suppliesBarEl && payload.labels && payload.suppliesMonthly) {
      createAnalyticsBarChart(suppliesBarEl, payload.labels, payload.suppliesMonthly, '#6366f1');
    }

    // Transfers bar chart
    const transfersBarEl = document.getElementById('transfersAnalyticsBar');
    if (transfersBarEl && payload.labels && payload.transfersMonthly) {
      createAnalyticsBarChart(transfersBarEl, payload.labels, payload.transfersMonthly, '#10b981');
    }

    // Assets by status donut
    const assetsStatusEl = document.getElementById('assetsStatusDonut');
    if (assetsStatusEl && payload.assetsByStatus) {
      const statusLabels = Object.keys(payload.assetsByStatus);
      const statusData = Object.values(payload.assetsByStatus);
      const statusColors = statusLabels.map(s => s === 'active' ? '#16a34a' : (s === 'condemn' ? '#f59e0b' : '#ef4444'));
      createAnalyticsDonutChart(assetsStatusEl, statusLabels, statusData, statusColors);
    }

    // Stock health donut
    const stockHealthEl = document.getElementById('stockHealthDonut');
    if (stockHealthEl && (payload.stockOut !== undefined || payload.stockLow !== undefined || payload.stockOk !== undefined)) {
      const stockLabels = ['Out', 'Low', 'Healthy'];
      const stockData = [payload.stockOut || 0, payload.stockLow || 0, payload.stockOk || 0];
      const stockColors = ['#ef4444', '#f59e0b', '#16a34a'];
      createAnalyticsDonutChart(stockHealthEl, stockLabels, stockData, stockColors);
    }

    // Assets by category pie
    const assetsCategoryEl = document.getElementById('assetsCategoryPie');
    if (assetsCategoryEl && payload.assetsValueByCategory) {
      const categoryLabels = payload.assetsValueByCategory.map(item => item.name);
      const categoryData = payload.assetsValueByCategory.map(item => item.v);
      const categoryColors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#84cc16','#f97316','#14b8a6','#eab308'];
      createAnalyticsPieChart(assetsCategoryEl, categoryLabels, categoryData, categoryColors);
    }

    // Supplies by category pie
    const suppliesCategoryEl = document.getElementById('suppliesCategoryPie');
    if (suppliesCategoryEl && payload.suppliesValueByCategory) {
      const categoryLabels = payload.suppliesValueByCategory.map(item => item.name);
      const categoryData = payload.suppliesValueByCategory.map(item => item.v);
      const categoryColors = ['#10b981','#3b82f6','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#84cc16','#f97316','#14b8a6','#eab308'];
      createAnalyticsPieChart(suppliesCategoryEl, categoryLabels, categoryData, categoryColors);
    }

  } catch (err) {
    console.error('Analytics charts error:', err);
  }
}

// Listen for analytics update events
window.addEventListener('analytics:update', (e) => {
  renderAnalyticsCharts(e.detail || {});
});

// Hook into Livewire lifecycle
if (window.Livewire) {
  window.Livewire.hook('message.processed', (message, component) => {
    if (window.__analytics_payload) {
      renderAnalyticsCharts(window.__analytics_payload);
    }
  });
}

// Initialize with payload if available
if (window.__analytics_payload) {
  renderAnalyticsCharts(window.__analytics_payload);
}

export { renderAnalyticsCharts };