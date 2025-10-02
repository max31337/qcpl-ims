import ApexCharts from 'apexcharts';

let charts = {};

function createLine(el, labels, series) {
  if (charts.line) charts.line.destroy();
  const options = {
    chart: { 
      type: 'line', 
      height: 250, 
      toolbar: { show: false },
      fontFamily: 'Inter, system-ui, sans-serif'
    },
    series: [{ 
      name: 'New Supplies', 
      data: series,
      color: '#3b82f6'
    }],
    xaxis: { 
      categories: labels,
      labels: {
        style: { fontSize: '12px', colors: '#6b7280' }
      }
    },
    yaxis: {
      labels: {
        style: { fontSize: '12px', colors: '#6b7280' }
      }
    },
    stroke: { 
      curve: 'smooth',
      width: 3
    },
    fill: {
      type: 'gradient',
      gradient: {
        shade: 'light',
        type: 'vertical',
        shadeIntensity: 0.3,
        gradientToColors: ['#93c5fd'],
        inverseColors: false,
        opacityFrom: 0.8,
        opacityTo: 0.1,
      }
    },
    grid: {
      strokeDashArray: 3,
      borderColor: '#e5e7eb'
    },
    tooltip: { 
      theme: 'light',
      style: { fontSize: '12px' }
    }
  };
  charts.line = new ApexCharts(el, options);
  charts.line.render();
}

function createBar(el, labels, series) {
  if (charts.bar) charts.bar.destroy();
  const options = {
    chart: { 
      type: 'bar', 
      height: 250, 
      toolbar: { show: false },
      fontFamily: 'Inter, system-ui, sans-serif'
    },
    series: [{ 
      name: 'Supply Count', 
      data: series 
    }],
    xaxis: { 
      categories: labels,
      labels: {
        style: { fontSize: '11px', colors: '#6b7280' },
        rotate: -45
      }
    },
    yaxis: {
      labels: {
        style: { fontSize: '12px', colors: '#6b7280' }
      }
    },
    plotOptions: { 
      bar: { 
        borderRadius: 8,
        dataLabels: {
          position: 'top'
        }
      } 
    },
    fill: {
      type: 'gradient',
      gradient: {
        shade: 'light',
        type: 'vertical',
        shadeIntensity: 0.3,
        gradientToColors: ['#60a5fa'],
        inverseColors: false,
        opacityFrom: 0.9,
        opacityTo: 0.7,
      }
    },
    colors: ['#3b82f6'],
    grid: {
      strokeDashArray: 3,
      borderColor: '#e5e7eb'
    },
    tooltip: { 
      theme: 'light',
      style: { fontSize: '12px' }
    }
  };
  charts.bar = new ApexCharts(el, options);
  charts.bar.render();
}

function createDonut(el, labels, series) {
  if (charts.donut) charts.donut.destroy();
  const options = {
    chart: { 
      type: 'donut', 
      height: 200,
      fontFamily: 'Inter, system-ui, sans-serif'
    },
    series: series,
    labels: labels,
    legend: { 
      show: false  // We'll use custom legend below
    },
    colors: ['#10b981', '#f59e0b', '#ef4444'], // OK, Low, Out
    plotOptions: {
      pie: {
        donut: {
          size: '70%',
          labels: {
            show: true,
            total: {
              show: true,
              label: 'Total Items',
              fontSize: '14px',
              fontWeight: 600,
              color: '#374151'
            }
          }
        }
      }
    },
    dataLabels: {
      enabled: true,
      formatter: function(val, opts) {
        return Math.round(val) + '%';
      },
      style: {
        fontSize: '12px',
        fontWeight: 'bold',
        colors: ['#ffffff']
      }
    },
    tooltip: { 
      theme: 'light',
      style: { fontSize: '12px' },
      y: {
        formatter: function(val) {
          return val + ' items';
        }
      }
    }
  };
  charts.donut = new ApexCharts(el, options);
  charts.donut.render();
}

function createStackedBar(el, categories, lowSeries, outSeries) {
  if (charts.stacked) charts.stacked.destroy();
  const options = {
    chart: { type: 'bar', height: 260, stacked: true, toolbar: { show: false } },
    series: [
      { name: 'Low', data: lowSeries },
      { name: 'Out', data: outSeries }
    ],
    xaxis: { categories },
    plotOptions: { bar: { borderRadius: 6 } },
    legend: { position: 'bottom' },
    colors: ['#f59e0b', '#ef4444']
  };
  charts.stacked = new ApexCharts(el, options);
  charts.stacked.render();
}

function createHBar(el, labels, values) {
  if (charts.hbar) charts.hbar.destroy();
  const options = {
    chart: { type: 'bar', height: 260, toolbar: { show: false } },
    series: [{ name: 'Value', data: values }],
    xaxis: { categories: labels, labels: { formatter: (val) => `₱${Number(val).toLocaleString()}` } },
    plotOptions: { bar: { horizontal: true, borderRadius: 6 } },
    dataLabels: { enabled: false },
    tooltip: { y: { formatter: (val) => `₱${Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` } }
  };
  charts.hbar = new ApexCharts(el, options);
  charts.hbar.render();
}

function createPie(el, labels, counts) {
  if (charts.pie) charts.pie.destroy();
  const options = {
    chart: { type: 'pie', height: 240 },
    series: counts,
    labels,
    legend: { position: 'bottom' }
  };
  charts.pie = new ApexCharts(el, options);
  charts.pie.render();
}

function renderAll(payload) {
  console.log('Supply charts renderAll called with payload:', payload);
  
  const lineEl = document.getElementById('supply-monthly-line');
  const barEl = document.getElementById('supply-categories-bar');
  const donutEl = document.getElementById('supply-stock-donut');
  const lowOutEl = document.getElementById('supply-lowout-stacked');
  const topSkusEl = document.getElementById('supply-topskus-bar');
  const agingPieEl = document.getElementById('supply-aging-pie');

  console.log('Chart elements found:', {
    line: !!lineEl,
    bar: !!barEl, 
    donut: !!donutEl
  });

  if (!lineEl || !barEl || !donutEl) {
    console.warn('Some chart elements not found, skipping render');
    return;
  }

  const categories = payload.categories || [];
  const counts = (payload.categoryCounts && payload.categoryCounts.length) ? payload.categoryCounts : null;
  const values = (payload.categoryValues && payload.categoryValues.length) ? payload.categoryValues : null;

  // Render main charts
  console.log('Rendering line chart with:', payload.monthlyLabels, payload.monthlyAdds);
  createLine(lineEl, payload.monthlyLabels || [], payload.monthlyAdds || []);
  
  console.log('Rendering bar chart with categories:', categories, 'values:', counts ?? values ?? []);
  createBar(barEl, categories, counts ?? values ?? []);
  
  const sh = payload.stockHealth || {
    ok: (payload.stockOk != null ? payload.stockOk : 0),
    low: (payload.stockLow != null ? payload.stockLow : 0),
    out: (payload.stockOut != null ? payload.stockOut : 0),
  };
  console.log('Stock health data:', sh);
  createDonut(donutEl, ['OK','Low','Out'], [sh.ok || 0, sh.low || 0, sh.out || 0]);

  if (lowOutEl && payload.lowVsOutCategories) {
    createStackedBar(lowOutEl, payload.lowVsOutCategories || [], payload.lowSeries || [], payload.outSeries || []);
  }
  if (topSkusEl && payload.topSkuLabels) {
    createHBar(topSkusEl, payload.topSkuLabels || [], payload.topSkuValues || []);
  }
  if (agingPieEl && payload.agingLabels) {
    createPie(agingPieEl, payload.agingLabels || [], payload.agingCounts || []);
  }
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
