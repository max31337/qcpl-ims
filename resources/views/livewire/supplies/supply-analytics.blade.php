<div class="space-y-8">
    <!-- Page Header -->
    <div class="bg-white rounded-xl p-6 text-black shadow-lg">
        <h1 class="text-2xl font-bold mb-2">Supply Officer Analytics</h1>
        <p class="text-muted-foreground">Monitor inventory levels, track movements, and analyze supply trends</p>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Low Stock Card -->
        <div class="bg-white rounded-xl shadow-sm border border-orange-100 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-orange-600 mb-1">Low Stock</div>
                    <div class="text-3xl font-bold text-orange-700">{{ $lowStock ?? '—' }}</div>
                    <div class="text-xs text-orange-500 mt-1">Items need restocking</div>
                </div>
                <div class="bg-orange-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Out of Stock Card -->
        <div class="bg-white rounded-xl shadow-sm border border-red-100 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-red-600 mb-1">Out of Stock</div>
                    <div class="text-3xl font-bold text-red-700">{{ $outOfStock ?? '—' }}</div>
                    <div class="text-xs text-red-500 mt-1">Items unavailable</div>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 4h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008l.196-.294m-2.312-5.012L21 14"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total SKUs Card -->
        <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-blue-600 mb-1">Total SKUs</div>
                    <div class="text-3xl font-bold text-blue-700">{{ $totalSkus ?? '—' }}</div>
                    <div class="text-xs text-blue-500 mt-1">Items in inventory</div>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- On-hand Value Card -->
        <div class="bg-white rounded-xl shadow-sm border border-green-100 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-green-600 mb-1">On-hand Value</div>
                    <div class="text-3xl font-bold text-green-700">{{ $onHandValue ?? '—' }}</div>
                    <div class="text-xs text-green-500 mt-1">Total inventory worth</div>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Supplies Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="bg-indigo-100 rounded-lg p-2 mr-3">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Recent Supplies</h3>
            </div>
            <span class="text-sm text-gray-500">Latest additions</span>
        </div>
        
        <div class="space-y-3">
            @forelse($recent as $s)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-150">
                    <div class="flex items-center space-x-4">
                        <div class="bg-white rounded-lg p-2 shadow-sm">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">{{ $s->description }}</div>
                            <div class="text-sm text-gray-500">Supply #: {{ $s->supply_number ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        @php
                            $stockLevel = $s->current_stock ?? 0;
                            $stockColor = $stockLevel > 10 ? 'text-green-600 bg-green-100' : ($stockLevel > 0 ? 'text-orange-600 bg-orange-100' : 'text-red-600 bg-red-100');
                        @endphp
                        <div class="px-3 py-1 rounded-full text-sm font-medium {{ $stockColor }}">
                            {{ $stockLevel }} on hand
                        </div>
                        @if($stockLevel <= 0)
                            <div class="text-red-500">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        @elseif($stockLevel <= 10)
                            <div class="text-orange-500">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="text-gray-500">No recent supplies found</p>
                    <p class="text-sm text-gray-400 mt-1">Add some supplies to see them here</p>
                </div>
            @endforelse
        </div>
    </div>
    <!-- Analytics Charts -->
    <div class="space-y-8">
        <!-- Primary Charts Row -->
        <div>
            <div class="flex items-center mb-6">
                <div class="bg-purple-100 rounded-lg p-2 mr-3">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Supply Analytics</h3>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Monthly Additions Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="bg-green-100 rounded-lg p-2 mr-3">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900">Monthly Additions</h4>
                        </div>
                        <div class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">Trend</div>
                    </div>
                    <div id="supply-monthly-line" class="h-64"></div>
                </div>

                <!-- Categories Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="bg-blue-100 rounded-lg p-2 mr-3">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900">By Category</h4>
                        </div>
                        <div class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">Distribution</div>
                    </div>
                    <div id="supply-categories-bar" class="h-64"></div>
                </div>

                <!-- Stock Health Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="bg-indigo-100 rounded-lg p-2 mr-3">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900">Stock Health</h4>
                        </div>
                        <div class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">Status</div>
                    </div>
                    <div id="supply-stock-donut" class="h-64 flex items-center justify-center"></div>
                </div>
            </div>
        </div>

        <!-- Advanced Analytics Row -->
        <div>
            <div class="flex items-center mb-6">
                <div class="bg-amber-100 rounded-lg p-2 mr-3">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Advanced Analytics</h3>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Low vs Out Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="bg-orange-100 rounded-lg p-2 mr-3">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900">Stock Issues</h4>
                        </div>
                        <div class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">Alerts</div>
                    </div>
                    <div id="supply-lowout-stacked" class="h-64"></div>
                </div>

                <!-- Top SKUs Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="bg-emerald-100 rounded-lg p-2 mr-3">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900">Top Value SKUs</h4>
                        </div>
                        <div class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">Value</div>
                    </div>
                    <div id="supply-topskus-bar" class="h-64"></div>
                </div>

                <!-- Stock Aging Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="bg-violet-100 rounded-lg p-2 mr-3">
                                <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-900">Stock Aging</h4>
                        </div>
                        <div class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">Age</div>
                    </div>
                    <div id="supply-aging-pie" class="h-64 flex items-center justify-center"></div>
                </div>
            </div>
        </div>
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
</div>
