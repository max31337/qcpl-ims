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

            <script>
                // Embed initial payload so the chart module can render immediately if it loads after the event
                window.__supply_analytics_payload = {!! json_encode([
                    'categories' => array_column($suppliesByCategory ?? [], 'category'),
                    'categoryCounts' => array_column($suppliesByCategory ?? [], 'count'),
                    'categoryValues' => array_column($suppliesByCategory ?? [], 'value'),
                    'monthlyLabels' => $monthlyAdds ? array_map(function($i){ return date('M Y', strtotime(now()->subMonths(11-$i)->format('Y-m-01'))); }, range(0, count($monthlyAdds)-1)) : [],
                    'monthlyAdds' => $monthlyAdds ?? [],
                    'stockHealth' => $stockHealth ?? ['ok'=>0,'low'=>0,'out'=>0]
                ]) !!};
            </script>
    </x-ui-card>
</div>
