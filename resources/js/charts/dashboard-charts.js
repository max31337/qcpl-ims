import ApexCharts from 'apexcharts';

let assetsLineChart = null;
let suppliesBarChart = null;
let assetsDonutChart = null;
let currentStatusPayload = {};
let activeStatuses = new Set();

function createLineChart(el, labels = [], data = []) {
  if (assetsLineChart) assetsLineChart.destroy();
  const options = {
    chart: { type: 'line', height: '100%', toolbar: { show: false } },
    series: [{ name: 'Assets created', data }],
    xaxis: { categories: labels },
    stroke: { 
      curve: 'smooth', 
      width: 8,
      colors: ['#646cfaff'],
      lineCap: 'round'
    },
    colors: ['#646cfaff'],
    fill: { 
      type: 'solid',
      opacity: 1,
      colors: ['#646cfaff']
    },
    markers: { 
      size: 8, 
      colors: ['#0095ffff'],
      strokeWidth: 4, 
      strokeColors: '#ffffff',
      hover: { size: 10 },
      strokeOpacity: 1,
      fillOpacity: 1
    },
    grid: { strokeDashArray: 4 },
    tooltip: { theme: 'dark' },
    legend: { show: false },
    yaxis: { 
      min: 0,
      labels: { 
        formatter: function (val) {
          return Math.floor(val);
        }
      }
    }
  };
  assetsLineChart = new ApexCharts(el, options);
  assetsLineChart.render();
  return assetsLineChart;
}

function createBarChart(el, labels = ['Out','Low','Healthy'], data = []) {
  if (suppliesBarChart) suppliesBarChart.destroy();
  const options = {
    chart: { type: 'bar', height: '100%', toolbar: { show: false } },
    series: [{ name: 'Count', data }],
    xaxis: { categories: labels },
    plotOptions: { bar: { borderRadius: 8, columnWidth: '60%', distributed: true } },
    dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 'bold' } },
    colors: ['#ef4444', '#f59e0b', '#10b981'],
    grid: { strokeDashArray: 4, yaxis: { lines: { show: true } } },
    tooltip: { theme: 'dark' },
    legend: { show: false }
  };
  suppliesBarChart = new ApexCharts(el, options);
  suppliesBarChart.render();
  return suppliesBarChart;
}

function createDonutChart(el, labels = [], data = [], colors = []) {
  if (assetsDonutChart) assetsDonutChart.destroy();
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
  assetsDonutChart = new ApexCharts(el, options);
  assetsDonutChart.render();
  return assetsDonutChart;
}

function renderAll(payload) {
  try {
    // store payload for toggles and Livewire re-renders
    currentStatusPayload = payload.assetsByStatus || {};
    // initialize activeStatuses if empty
    if (!activeStatuses.size) {
      Object.keys(currentStatusPayload || {}).forEach(k => activeStatuses.add(k));
    }
    // Line
    const assetsEl = document.getElementById('assetsLineChart');
    if (assetsEl && payload.labels && payload.assetsValues) {
      createLineChart(assetsEl, payload.labels, payload.assetsValues);
    }

    // Bar
    const suppliesEl = document.getElementById('suppliesBarChart');
    if (suppliesEl) {
      createBarChart(suppliesEl, ['Out','Low','Healthy'], [payload.stockOut || 0, payload.stockLow || 0, payload.stockOk || 0]);
    }

    // Donut
    const donutEl = document.getElementById('assetsDonutChart');
    if (donutEl && payload.assetsByStatus) {
      const statusObj = payload.assetsByStatus || {};
      const sLabels = Object.keys(statusObj || {});
      // compute values respecting activeStatuses set
      const sValues = sLabels.map(k => (activeStatuses.has(k) ? (statusObj[k] || 0) : 0));
      const sColors = sLabels.map(s => s === 'active' ? '#16a34a' : (s === 'condemn' ? '#f59e0b' : '#ef4444'));
      createDonutChart(donutEl, sLabels, sValues, sColors);
    }
  } catch (err) {
    // swallow - charts are non-critical
    // console.error('dashboard charts error', err);
  }
}

// Listen to a custom browser event dispatched by Livewire
window.addEventListener('dashboard:update', (e) => {
  renderAll(e.detail || {});
});

// Also hook into Livewire lifecycle: when Livewire finishes a DOM update, re-render charts
if (window.Livewire) {
  window.Livewire.hook('message.processed', (message, component) => {
    // If it was the dashboard component that updated, or simply always try to rerender
    // we can attempt to read data attributes embedded on the page, but we rely on the browser event
    // in most cases. Still, attempt to re-run render using a small payload read from window.__dashboard_payload
    if (window.__dashboard_payload) {
      renderAll(window.__dashboard_payload);
    }
  });
}

// Expose a method for manual updates
export { renderAll };

// Toggle button handling: delegate clicks from the container if present
document.addEventListener('click', (e) => {
  const btn = e.target.closest && e.target.closest('.status-toggle');
  if (!btn) return;
  const status = btn.getAttribute('data-status');
  if (!status) return;
  // toggle
  if (activeStatuses.has(status)) {
    activeStatuses.delete(status);
    btn.setAttribute('aria-pressed', 'false');
    btn.classList.add('opacity-50');
  } else {
    activeStatuses.add(status);
    btn.setAttribute('aria-pressed', 'true');
    btn.classList.remove('opacity-50');
  }
  // re-render donut from current payload
  if (window.__dashboard_payload) {
    renderAll(window.__dashboard_payload);
  } else if (currentStatusPayload) {
    renderAll({ assetsByStatus: currentStatusPayload, labels: [], assetsValues: [] });
  }
});

// If the inline payload was emitted before the module loaded, initialize now
if (window.__dashboard_payload) {
  // ensure toggles are initialized
  try {
    renderAll(window.__dashboard_payload);
  } catch (err) {
    // silent
  }
}
