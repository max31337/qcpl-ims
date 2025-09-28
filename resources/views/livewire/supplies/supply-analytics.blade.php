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
                                <div class="font-medium">{{ $s->name }}</div>
                                <div class="text-sm text-gray-500">SKU: {{ $s->code ?? '—' }}</div>
                            </div>
                            <div class="text-sm text-gray-600">{{ $s->quantity }} on hand</div>
                        </div>
                    </x-ui-card>
                @empty
                    <div class="text-sm text-gray-500">No recent supplies.</div>
                @endforelse
            </div>
        </div>
    </x-ui-card>
</div>
