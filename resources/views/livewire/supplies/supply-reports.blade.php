<div>
    <x-ui-card title="Supply Reports">
        <div class="text-sm text-gray-500">Reports coming soon. Use this page to export stock reports and historical adjustments.</div>
    </x-ui-card>
</div>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Supply Management Reports</h1>
            <p class="text-muted-foreground">Summary and exports for supplies</p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="exportCsv" class="inline-flex items-center rounded-md border px-3 py-2 text-sm hover:bg-accent">
                <x-ui.icon name="download" class="mr-2 h-4 w-4" /> Export CSV
            </button>
        </div>
    </div>

    <div class="rounded-lg border bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <select wire:model="statusFilter" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <select wire:model="categoryFilter" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-lg border bg-white shadow-sm p-4">
            <div class="text-sm text-muted-foreground">Total Items</div>
            <div class="text-2xl font-semibold">{{ number_format($summary['total_items'] ?? 0) }}</div>
        </div>
        <div class="rounded-lg border bg-white shadow-sm p-4">
            <div class="text-sm text-muted-foreground">Units On Hand</div>
            <div class="text-2xl font-semibold">{{ number_format($summary['on_hand_units'] ?? 0) }}</div>
        </div>
        <div class="rounded-lg border bg-white shadow-sm p-4">
            <div class="text-sm text-muted-foreground">On-Hand Value</div>
            <div class="text-2xl font-semibold">₱{{ number_format($summary['on_hand_value'] ?? 0, 2) }}</div>
        </div>
    </div>

    <div class="rounded-lg border bg-white shadow-sm">
        <div class="p-4 border-b font-medium">By Category</div>
        <div class="p-4 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-2">Category</th>
                        <th class="py-2 text-right">Items</th>
                        <th class="py-2 text-right">Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($summary['by_category'] ?? []) as $row)
                        <tr class="border-t">
                            <td class="py-2">{{ $row->category->name ?? '—' }}</td>
                            <td class="py-2 text-right">{{ number_format($row->c ?? 0) }}</td>
                            <td class="py-2 text-right">₱{{ number_format($row->v ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-6 text-center text-gray-500">No data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
