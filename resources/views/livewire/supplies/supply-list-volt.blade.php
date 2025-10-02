<?php

use App\Models\Supply;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public $search = '';

    #[Url]
    public $category = '';

    #[Url]
    public $status = '';

    public function clearFilters()
    {
        $this->search = '';
        $this->category = '';
        $this->status = '';
        $this->resetPage();
    }

    public function with(): array
    {
        $user = Auth::user();
        $q = Supply::forUser($user)->with('category');

        if ($this->search) {
            $q->where(function($r){
                $r->where('supply_number','like','%'.$this->search.'%')
                  ->orWhere('description','like','%'.$this->search.'%')
                  ->orWhere('sku','like','%'.$this->search.'%');
            });
        }

        if ($this->category) {
            $q->where('category_id', $this->category);
        }

        if ($this->status) {
            $q->where('status', $this->status);
        }

        $supplies = $q->orderByDesc('last_updated')->paginate(12);
        $categories = Category::where('type','supply')->orderBy('name')->get();

        return [
            'supplies' => $supplies,
            'categories' => $categories
        ];
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Supply Management</h1>
            <p class="text-sm text-muted-foreground mt-1">Manage consumable supplies, adjust stock, and review reports.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('supplies.reports') }}" class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm hover:bg-accent">
                <x-ui.icon name="bar-chart" class="h-4 w-4" />
                <span>Reports</span>
            </a>
            <a href="{{ route('supplies.create') }}" wire:navigate class="inline-flex items-center gap-2 rounded-md bg-primary text-primary-foreground px-3 py-2 text-sm hover:bg-primary/90">
                <x-ui.icon name="plus" class="h-4 w-4" />
                <span>New Supply</span>
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <x-ui-card>
        <!-- Debug: Show current filter values -->
        @if(app()->environment('local'))
            <div class="p-2 bg-gray-100 text-xs mb-2">
                Debug - Search: "{{ $search }}" | Category: "{{ $category }}" | Status: "{{ $status }}"
            </div>
        @endif
        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex-1 flex items-center gap-3">
                <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Search supplies (name, number, SKU)" />
                <x-ui-select wire:model.live="category" class="min-w-[160px]">
                    <option value="">All Categories</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </x-ui-select>
                <x-ui-select wire:model.live="status" class="min-w-[140px]">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </x-ui-select>
            </div>

            <div class="flex items-center gap-2">
                <x-ui-button wire:click="clearFilters" variant="secondary">Clear</x-ui-button>
                <a href="{{ route('supplies.reports') }}" wire:navigate class="inline-flex items-center rounded-md border px-3 py-2 text-sm hover:bg-accent">Export</a>
            </div>
        </div>
    </x-ui-card>

    <!-- Table Card -->
    <x-ui-card>
        <div class="overflow-x-auto">
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
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($supplies as $s)
                        <tr class="border-b hover:bg-accent/40" wire:key="supply-{{ $s->id }}">
                            <td class="py-3 px-4 font-mono text-xs">{{ $s->supply_number }}</td>
                            <td class="py-3 px-4">{{ $s->description }}</td>
                            <td class="py-3 px-4">{{ $s->category->name ?? '—' }}</td>
                            <td class="py-3 px-4 text-right">
                                <span class="inline-flex items-center {{ $s->current_stock < $s->min_stock ? 'text-red-600 font-semibold' : '' }}">{{ number_format($s->current_stock) }}</span>
                            </td>
                            <td class="py-3 px-4 text-right">{{ number_format($s->min_stock) }}</td>
                            <td class="py-3 px-4 text-right">₱{{ number_format($s->unit_cost, 2) }}</td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs {{ $s->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">{{ ucfirst($s->status) }}</span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('supplies.adjust', ['id' => $s->id]) }}" wire:navigate class="inline-flex items-center justify-center rounded-md h-8 w-8 hover:bg-accent" aria-label="Adjust stock">
                                        <x-ui.icon name="arrow-up-down" class="h-4 w-4" />
                                    </a>
                                    <a href="{{ route('supplies.edit', ['id' => $s->id]) }}" wire:navigate class="inline-flex items-center justify-center rounded-md h-8 w-8 hover:bg-accent" aria-label="Edit">
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
        </div>

        <div class="mt-4">{{ $supplies->links('pagination::custom-light') }}</div>
    </x-ui-card>
</div>