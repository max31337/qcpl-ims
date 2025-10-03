<div>
    <x-ui-card title="Supply Officer Dashboard">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="p-4 bg-white rounded shadow">
                <div class="text-sm text-gray-500">Low Stock</div>
                <div class="text-2xl font-semibold">{{ $lowStock ?? '—' }}</div>
            </div>
            <div class="p-4 bg-white rounded shadow">
                <div class="text-sm text-gray-500">Out of Stock</div>
                <div class="text-2xl font-semibold">{{ $outOfStock ?? '—' }}</div>
            </div>
            <div class="p-4 bg-white rounded shadow">
                <div class="text-sm text-gray-500">Total SKUs</div>
                <div class="text-2xl font-semibold">{{ $totalSkus ?? '—' }}</div>
            </div>
            <div class="p-4 bg-white rounded shadow">
                <div class="text-sm text-gray-500">On-hand Value</div>
                <div class="text-2xl font-semibold">{{ $onHandValue ?? '—' }}</div>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-medium">Recent Supplies</h3>
            <div class="mt-3 space-y-2">
                @forelse($recent as $s)
                    <x-ui-card>
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="font-medium">{{ $s->description }}</div>
                                <div class="text-sm text-gray-500">Supply #: {{ $s->supply_number ?? '—' }}</div>
                            </div>
                                <div class="text-sm text-gray-600">{{ $s->current_stock }} on hand</div>
                        </div>
                    </x-ui-card>
                @empty
                    <div class="text-sm text-gray-500">No recent supplies.</div>
                @endforelse
            </div>
        </div>
        
            <!-- Charts -->
            <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-4">
                <x-ui-card>
                    <h4 class="text-sm font-medium mb-2">Monthly Additions</h4>
                    <div id="supply-monthly-line"></div>
                </x-ui-card>

                <x-ui-card>
                    <h4 class="text-sm font-medium mb-2">Supplies by Category</h4>
                    <div id="supply-categories-bar"></div>
                </x-ui-card>

                <x-ui-card>
                    <h4 class="text-sm font-medium mb-2">Stock Health</h4>
                    <div id="supply-stock-donut" class="mx-auto" style="max-width:260px"></div>
                </x-ui-card>
            </div>

            <!-- Extended analytics charts -->
            <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-4">
                <x-ui-card>
                    <h4 class="text-sm font-medium mb-2">Low vs Out by Category</h4>
                    <div id="supply-lowout-stacked"></div>
                </x-ui-card>
                <x-ui-card>
                    <h4 class="text-sm font-medium mb-2">Top SKUs by On-hand Value</h4>
                    <div id="supply-topskus-bar"></div>
                </x-ui-card>
                <x-ui-card>
                    <h4 class="text-sm font-medium mb-2">Stock Aging</h4>
                    <div id="supply-aging-pie"></div>
                </x-ui-card>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>
            <script data-supply-analytics-inline>
                // Embed initial payload so the chart module can render immediately if it loads after the event
                window.__supply_analytics_payload = {!! json_encode([
                    'categories' => array_column($suppliesByCategory ?? [], 'category'),
                    'categoryCounts' => array_column($suppliesByCategory ?? [], 'count'),
                    'categoryValues' => array_column($suppliesByCategory ?? [], 'value'),
                    'monthlyLabels' => $monthlyAdds ? array_map(function($i){ return date('M Y', strtotime(now()->subMonths(11-$i)->format('Y-m-01'))); }, range(0, count($monthlyAdds)-1)) : [],
                    'monthlyAdds' => $monthlyAdds ?? [],
                    'stockHealth' => $stockHealth ?? ['ok'=>0,'low'=>0,'out'=>0],
                    // Extended payload mirrors component dispatch
                    'lowVsOutCategories' => array_map(fn($r) => $r['category'] ?? ($r['name'] ?? 'Uncategorized'), $lowVsOutByCategory ?? []),
                    'lowSeries' => array_map(fn($r) => $r['low'] ?? 0, $lowVsOutByCategory ?? []),
                    'outSeries' => array_map(fn($r) => $r['out'] ?? 0, $lowVsOutByCategory ?? []),
                    'topSkuLabels' => array_map(fn($r) => $r['label'] ?? ($r['description'] ?? ''), $topOnHandSkus ?? []),
                    'topSkuValues' => array_map(fn($r) => $r['value'] ?? 0, $topOnHandSkus ?? []),
                    'agingLabels' => $agingBuckets['labels'] ?? ['≤30d','31-60d','61-90d','>90d'],
                    'agingCounts' => $agingBuckets['counts'] ?? [0,0,0,0],
                ]) !!};

                // Store chart instances
                let supplyChartInstances = {};

                // Chart creation functions
                function createLineChart(elementId, labels, data, title) {
                    const element = document.getElementById(elementId);
                    if (!element) return null;

                    // Clean up existing chart
                    if (supplyChartInstances[elementId]) {
                        supplyChartInstances[elementId].destroy();
                        delete supplyChartInstances[elementId];
                    }
                    element.innerHTML = '';

                    const options = {
                        chart: {
                            type: 'line',
                            height: 250,
                            toolbar: { show: false }
                        },
                        series: [{
                            name: title,
                            data: data
                        }],
                        xaxis: {
                            categories: labels,
                            labels: { style: { fontSize: '11px' } }
                        },
                        colors: ['#10b981'],
                        stroke: { curve: 'smooth', width: 3 },
                        dataLabels: { enabled: false },
                        grid: { strokeDashArray: 4 }
                    };

                    supplyChartInstances[elementId] = new ApexCharts(element, options);
                    supplyChartInstances[elementId].render();
                    return supplyChartInstances[elementId];
                }

                function createBarChart(elementId, categories, values, title) {
                    const element = document.getElementById(elementId);
                    if (!element) return null;

                    // Clean up existing chart
                    if (supplyChartInstances[elementId]) {
                        supplyChartInstances[elementId].destroy();
                        delete supplyChartInstances[elementId];
                    }
                    element.innerHTML = '';

                    const options = {
                        chart: {
                            type: 'bar',
                            height: 250,
                            toolbar: { show: false }
                        },
                        series: [{
                            name: title,
                            data: values
                        }],
                        xaxis: {
                            categories: categories,
                            labels: { style: { fontSize: '11px' } }
                        },
                        colors: ['#3b82f6'],
                        dataLabels: { enabled: false },
                        grid: { strokeDashArray: 4 }
                    };

                    supplyChartInstances[elementId] = new ApexCharts(element, options);
                    supplyChartInstances[elementId].render();
                    return supplyChartInstances[elementId];
                }

                function createDonutChart(elementId, labels, values, colors) {
                    const element = document.getElementById(elementId);
                    if (!element) return null;

                    // Clean up existing chart
                    if (supplyChartInstances[elementId]) {
                        supplyChartInstances[elementId].destroy();
                        delete supplyChartInstances[elementId];
                    }
                    element.innerHTML = '';

                    const options = {
                        chart: {
                            type: 'donut',
                            height: 250,
                            toolbar: { show: false }
                        },
                        series: values,
                        labels: labels,
                        colors: colors,
                        legend: { position: 'bottom' },
                        dataLabels: {
                            enabled: true,
                            style: { fontSize: '11px' }
                        },
                        plotOptions: {
                            pie: {
                                donut: { size: '50%' }
                            }
                        }
                    };

                    supplyChartInstances[elementId] = new ApexCharts(element, options);
                    supplyChartInstances[elementId].render();
                    return supplyChartInstances[elementId];
                }

                function createStackedBarChart(elementId, categories, series) {
                    const element = document.getElementById(elementId);
                    if (!element) return null;

                    // Clean up existing chart
                    if (supplyChartInstances[elementId]) {
                        supplyChartInstances[elementId].destroy();
                        delete supplyChartInstances[elementId];
                    }
                    element.innerHTML = '';

                    const options = {
                        chart: {
                            type: 'bar',
                            height: 250,
                            stacked: true,
                            toolbar: { show: false }
                        },
                        series: series,
                        xaxis: {
                            categories: categories,
                            labels: { style: { fontSize: '11px' } }
                        },
                        colors: ['#f59e0b', '#ef4444'],
                        dataLabels: { enabled: false },
                        legend: { position: 'top' }
                    };

                    supplyChartInstances[elementId] = new ApexCharts(element, options);
                    supplyChartInstances[elementId].render();
                    return supplyChartInstances[elementId];
                }

                function createPieChart(elementId, labels, values, colors) {
                    const element = document.getElementById(elementId);
                    if (!element) return null;

                    // Clean up existing chart
                    if (supplyChartInstances[elementId]) {
                        supplyChartInstances[elementId].destroy();
                        delete supplyChartInstances[elementId];
                    }
                    element.innerHTML = '';

                    const options = {
                        chart: {
                            type: 'pie',
                            height: 250,
                            toolbar: { show: false }
                        },
                        series: values,
                        labels: labels,
                        colors: colors,
                        legend: { position: 'bottom' },
                        dataLabels: {
                            enabled: true,
                            style: { fontSize: '11px' }
                        }
                    };

                    supplyChartInstances[elementId] = new ApexCharts(element, options);
                    supplyChartInstances[elementId].render();
                    return supplyChartInstances[elementId];
                }

                // Initialize all charts
                function initializeSupplyCharts() {
                    const payload = window.__supply_analytics_payload;
                    
                    // Monthly Additions Line Chart
                    if (payload.monthlyLabels && payload.monthlyAdds) {
                        createLineChart('supply-monthly-line', payload.monthlyLabels, payload.monthlyAdds, 'Monthly Additions');
                    }

                    // Categories Bar Chart
                    if (payload.categories && payload.categoryCounts) {
                        createBarChart('supply-categories-bar', payload.categories, payload.categoryCounts, 'Supplies by Category');
                    }

                    // Stock Health Donut Chart
                    if (payload.stockHealth) {
                        const stockLabels = ['OK', 'Low Stock', 'Out of Stock'];
                        const stockValues = [payload.stockHealth.ok || 0, payload.stockHealth.low || 0, payload.stockHealth.out || 0];
                        const stockColors = ['#10b981', '#f59e0b', '#ef4444'];
                        createDonutChart('supply-stock-donut', stockLabels, stockValues, stockColors);
                    }

                    // Low vs Out Stacked Bar Chart
                    if (payload.lowVsOutCategories) {
                        const series = [
                            { name: 'Low Stock', data: payload.lowSeries || [] },
                            { name: 'Out of Stock', data: payload.outSeries || [] }
                        ];
                        createStackedBarChart('supply-lowout-stacked', payload.lowVsOutCategories, series);
                    }

                    // Top SKUs Bar Chart
                    if (payload.topSkuLabels && payload.topSkuValues) {
                        createBarChart('supply-topskus-bar', payload.topSkuLabels, payload.topSkuValues, 'Top SKUs Value');
                    }

                    // Aging Pie Chart
                    if (payload.agingLabels && payload.agingCounts) {
                        const agingColors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'];
                        createPieChart('supply-aging-pie', payload.agingLabels, payload.agingCounts, agingColors);
                    }
                }

                // Flag to prevent multiple initializations
                window._supplyChartsInitialized = window._supplyChartsInitialized || false;

                function initializeSupplyChartsOnce() {
                    if (window._supplyChartsInitialized) return;
                    window._supplyChartsInitialized = true;
                    
                    console.log('Initializing supply charts...');
                    setTimeout(initializeSupplyCharts, 100);
                }

                // Initialize charts when DOM is ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initializeSupplyChartsOnce);
                } else {
                    initializeSupplyChartsOnce();
                }
            </script>
    </x-ui-card>
</div>
