<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Asset Analytics</h1>
      <p class="text-sm text-muted-foreground mt-1">Branch-scoped asset analytics for property officers.</p>
    </div>
  </div>

  <x-ui.card class="p-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div>
        <p class="text-sm text-muted-foreground">Total Assets</p>
        <p class="text-3xl font-bold">{{ number_format($kpis['totalAssets'] ?? 0) }}</p>
      </div>
      <div>
        <p class="text-sm text-muted-foreground">Total Asset Value</p>
        <p class="text-3xl font-bold text-green-600">₱{{ number_format($kpis['assetsValue'] ?? 0,2) }}</p>
      </div>
      <div>
        <p class="text-sm text-muted-foreground">Active Assets</p>
        <p class="text-3xl font-bold text-emerald-600">{{ number_format($kpis['activeAssets'] ?? 0) }}</p>
      </div>
    </div>
  </x-ui.card>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <x-ui.card class="p-6">
      <h4 class="text-lg font-medium mb-4">Monthly Asset Additions</h4>
      <div id="assets-monthly-line" style="min-height: 240px;"></div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <h4 class="text-lg font-medium mb-4">Assets by Status</h4>
      <div id="assets-status-donut" style="min-height: 240px; max-width:280px; margin:0 auto"></div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <h4 class="text-lg font-medium mb-4">Assets by Category</h4>
      <div id="assets-category-bar" style="min-height: 240px;"></div>
    </x-ui.card>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>
  <script>
    const payload = {!! json_encode([
      'labels' => $labels ?? [],
      'assetsMonthly' => $assetsMonthly ?? [],
      'assetsByStatus' => is_object($assetsByStatus ?? null) ? ($assetsByStatus->toArray()) : ($assetsByStatus ?? []),
      'categoryLabels' => ($assetsValueByCategory ?? collect())->pluck('name')->toArray(),
      'categoryValues' => ($assetsValueByCategory ?? collect())->pluck('v')->toArray(),
    ], JSON_UNESCAPED_UNICODE) !!};

    document.addEventListener('DOMContentLoaded', function() {
      setTimeout(() => {
        // line
        if (payload.labels && payload.assetsMonthly) {
          new ApexCharts(document.getElementById('assets-monthly-line'), {
            chart: { type: 'line', height: 240, toolbar: { show: false } },
            series: [{ name: 'Assets Added', data: payload.assetsMonthly }],
            xaxis: { categories: payload.labels },
            colors: ['#3b82f6']
          }).render();
        }

        // status donut
        if (payload.assetsByStatus && Object.keys(payload.assetsByStatus).length > 0) {
          new ApexCharts(document.getElementById('assets-status-donut'), {
            chart: { type: 'donut', height: 240 },
            series: Object.values(payload.assetsByStatus),
            labels: Object.keys(payload.assetsByStatus).map(s => s.charAt(0).toUpperCase()+s.slice(1)),
            legend: { show: false }
          }).render();
        }

        // category
        if (payload.categoryLabels && payload.categoryLabels.length > 0) {
          new ApexCharts(document.getElementById('assets-category-bar'), {
            chart: { type: 'bar', height: 240, toolbar: { show: false } },
            series: [{ name: 'Value (₱)', data: payload.categoryValues }],
            xaxis: { categories: payload.categoryLabels },
            colors: ['#8b5cf6']
          }).render();
        }

      }, 50);
    });
  </script>

</div>