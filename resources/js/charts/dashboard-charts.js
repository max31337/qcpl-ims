import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

let assetsLineChart = null;
let suppliesBarChart = null;
let assetsDonutChart = null;
let currentStatusPayload = {};
let activeStatuses = new Set();

function createLineChart(ctx, labels = [], data = []) {
  if (assetsLineChart) assetsLineChart.destroy();
  assetsLineChart = new Chart(ctx, {
    type: 'line',
    data: { labels, datasets: [{ label: 'Assets created', data, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.12)', tension: 0.3, fill: true, pointRadius: 3 }] },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } }
  });
  return assetsLineChart;
}

function createBarChart(ctx, labels = ['Out','Low','Healthy'], data = []) {
  if (suppliesBarChart) suppliesBarChart.destroy();
  suppliesBarChart = new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets: [{ data, backgroundColor: ['#ef4444', '#f59e0b', '#10b981'] }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
  });
  return suppliesBarChart;
}

function createDonutChart(ctx, labels = [], data = [], colors = []) {
  if (assetsDonutChart) assetsDonutChart.destroy();
  assetsDonutChart = new Chart(ctx, {
    type: 'doughnut',
    data: { labels, datasets: [{ data, backgroundColor: colors }] },
    options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { display: false } } }
  });
  return assetsDonutChart;
}

function ensureCanvasHighDPR(canvas) {
  const dpr = window.devicePixelRatio || 1;
  // prefer explicit width/height attributes on the canvas, fall back to client size or 300
  const attrW = parseInt(canvas.getAttribute('width')) || Math.max(1, Math.round(canvas.clientWidth)) || 300;
  const attrH = parseInt(canvas.getAttribute('height')) || Math.max(1, Math.round(canvas.clientHeight)) || 300;
  canvas.width = attrW * dpr;
  canvas.height = attrH * dpr;
  canvas.style.maxWidth = attrW + 'px';
  canvas.style.maxHeight = attrH + 'px';
  canvas.style.width = 'auto';
  canvas.style.height = 'auto';
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
      // set container height if needed
      assetsEl.style.height = '180px';
      createLineChart(assetsEl.getContext('2d'), payload.labels, payload.assetsValues);
    }

    // Bar
    const suppliesEl = document.getElementById('suppliesBarChart');
    if (suppliesEl) {
      suppliesEl.style.height = '180px';
      createBarChart(suppliesEl.getContext('2d'), ['Out','Low','Healthy'], [payload.stockOut || 0, payload.stockLow || 0, payload.stockOk || 0]);
    }

    // Donut
    const donutEl = document.getElementById('assetsDonutChart');
    if (donutEl && payload.assetsByStatus) {
      ensureCanvasHighDPR(donutEl, 300, 300);
      const statusObj = payload.assetsByStatus || {};
      const sLabels = Object.keys(statusObj || {});
      // compute values respecting activeStatuses set
      const sValues = sLabels.map(k => (activeStatuses.has(k) ? (statusObj[k] || 0) : 0));
      const sColors = sLabels.map(s => s === 'active' ? '#16a34a' : (s === 'condemn' ? '#f59e0b' : '#ef4444'));
      createDonutChart(donutEl.getContext('2d'), sLabels, sValues, sColors);
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
