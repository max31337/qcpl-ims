import ApexCharts from 'apexcharts';

let charts = {};

function createLine(el, labels, series) {
  if (charts.line) charts.line.destroy();
  const options = {
    chart: { type: 'line', height: 240, toolbar: { show: false } },
    series: [{ name: 'Adds', data: series }],
    xaxis: { categories: labels },
    stroke: { curve: 'smooth' },
    tooltip: { theme: 'dark' }
  };
  charts.line = new ApexCharts(el, options);
  charts.line.render();
}

function createBar(el, labels, series) {
  if (charts.bar) charts.bar.destroy();
  const options = {
    chart: { type: 'bar', height: 240, toolbar: { show: false } },
    series: [{ name: 'Count', data: series }],
    xaxis: { categories: labels },
    plotOptions: { bar: { borderRadius: 6 } },
    tooltip: { theme: 'dark' }
  };
  charts.bar = new ApexCharts(el, options);
  charts.bar.render();
}

function createDonut(el, labels, series) {
  if (charts.donut) charts.donut.destroy();
  const options = {
    chart: { type: 'donut', height: 220 },
    series: series,
    labels: labels,
    legend: { position: 'bottom' },
    tooltip: { theme: 'dark' }
  };
  charts.donut = new ApexCharts(el, options);
  charts.donut.render();
}

function renderAll(payload) {
  const lineEl = document.getElementById('supply-monthly-line');
  const barEl = document.getElementById('supply-categories-bar');
  const donutEl = document.getElementById('supply-stock-donut');

  if (!lineEl || !barEl || !donutEl) return;

  const categories = payload.categories || [];
  const counts = (payload.categoryCounts && payload.categoryCounts.length) ? payload.categoryCounts : null;
  const values = (payload.categoryValues && payload.categoryValues.length) ? payload.categoryValues : null;

  createLine(lineEl, payload.monthlyLabels || [], payload.monthlyAdds || []);
  createBar(barEl, categories, counts ?? values ?? []);
  createDonut(donutEl, ['OK','Low','Out'], [payload.stockHealth?.ok || 0, payload.stockHealth?.low || 0, payload.stockHealth?.out || 0]);
}

// Listen for Livewire/browser events
window.addEventListener('supplyAnalytics:update', (e) => {
  if (e && e.detail) renderAll(e.detail);
});

// Also support a dashboard-scoped event
window.addEventListener('supplyDashboard:update', (e) => {
  if (e && e.detail) renderAll(e.detail);
});

// If server embedded a payload before module loaded
if (window.__supply_analytics_payload) renderAll(window.__supply_analytics_payload);
if (window.__supply_dashboard_payload) renderAll(window.__supply_dashboard_payload);

// Re-render after Livewire updates if needed
if (window.Livewire && window.Livewire.hook) {
  window.Livewire.hook('message.processed', (message, component) => {
    // Look for last payload on DOM
    if (window.__supply_analytics_payload) renderAll(window.__supply_analytics_payload);
  });
}

export default { renderAll };
