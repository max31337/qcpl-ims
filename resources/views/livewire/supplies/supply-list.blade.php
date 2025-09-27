<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Supply Management</h1>
            <p class="text-muted-foreground">Manage consumable supplies and stock levels</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('supplies.reports') }}" class="inline-flex items-center rounded-md border px-3 py-2 text-sm hover:bg-accent">
                <x-ui.icon name="bar-chart" class="mr-2 h-4 w-4" /> Reports
            </a>
            <a href="{{ route('supplies.create') }}" class="inline-flex items-center rounded-md bg-primary text-primary-foreground px-3 py-2 text-sm hover:bg-primary/90">
                <x-ui.icon name="plus" class="mr-2 h-4 w-4" /> New Supply
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="rounded-lg border bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <x-ui.input placeholder="Search (name or number)" wire:model.debounce.300ms="search" />
            <select wire:model="status" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <select wire:model="category" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="rounded-lg border bg-white shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-3 px-4">Supply #</th>
                    <th class="py-3 px-4">Description</th>
                    <th class="py-3 px-4">Category</th>
                    <th class="py-3 px-4 text-right">On Hand</th>
                    <th class="py-3 px-4 text-right">Min</th>
                    <th class="py-3 px-4 text-right">Unit Cost</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-right"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($supplies as $s)
                    <tr class="border-b hover:bg-accent/40">
                        <td class="py-2 px-4 font-mono">{{ $s->supply_number }}</td>
                        <td class="py-2 px-4">{{ $s->description }}</td>
                        <td class="py-2 px-4">{{ $s->category->name ?? '—' }}</td>
                        <td class="py-2 px-4 text-right {{ $s->current_stock < $s->min_stock ? 'text-red-600 font-semibold' : '' }}">{{ number_format($s->current_stock) }}</td>
                        <td class="py-2 px-4 text-right">{{ number_format($s->min_stock) }}</td>
                        <td class="py-2 px-4 text-right">₱{{ number_format($s->unit_cost, 2) }}</td>
                        <td class="py-2 px-4">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs {{ $s->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">{{ $s->status }}</span>
                        </td>
                        <td class="py-2 px-4 text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('supplies.adjust', $s->id) }}" class="inline-flex items-center justify-center rounded-md h-8 w-8 hover:bg-accent" title="Adjust Stock">
                                    <x-ui.icon name="arrow-up-down" class="h-4 w-4" />
                                </a>
                                <a href="{{ route('supplies.edit', $s->id) }}" class="inline-flex items-center justify-center rounded-md h-8 w-8 hover:bg-accent" title="Edit">
                                    <x-ui.icon name="pencil" class="h-4 w-4" />
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-muted-foreground">No supplies found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $supplies->links() }}</div>
    </div>
</div>
