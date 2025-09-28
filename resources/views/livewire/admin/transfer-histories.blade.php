<div class="space-y-6">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Transfer Histories</h1>
      <p class="text-muted-foreground">Complete history of all asset transfers and movements</p>
    </div>
    <div class="flex items-center gap-2">
      <x-ui.icon name="transfer" class="h-8 w-8 text-muted-foreground" />
    </div>
  </div>

  {{-- Filters --}}
  <x-ui.card class="p-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <x-ui.label>Search</x-ui.label>
        <x-ui.input type="text" placeholder="Asset description, property number, branch, remarks..." wire:model.live.debounce.300ms="search" />
      </div>
      <div>
        <x-ui.label>From Date</x-ui.label>
        <x-ui.input type="date" wire:model.live="fromDate" />
      </div>
      <div>
        <x-ui.label>To Date</x-ui.label>
        <x-ui.input type="date" wire:model.live="toDate" />
      </div>
      <div>
        <x-ui.label>Per Page</x-ui.label>
        <select class="w-full border rounded h-9 px-2" wire:model.live="perPage">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
      </div>
    </div>
  </x-ui.card>

  {{-- Transfer History Table --}}
  <x-ui.card class="p-0">
    <div class="p-4 border-b">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Transfer Records</h2>
        <div class="flex items-center gap-2">
          <x-ui.icon name="list" class="h-5 w-5 text-muted-foreground" />
          <span class="text-sm text-muted-foreground">{{ $transfers->total() }} total records</span>
        </div>
      </div>
    </div>

    <div class="overflow-x-auto">
      <x-ui.table>
        <thead>
          <tr>
            <th class="px-4 py-3 text-left cursor-pointer hover:bg-muted/50" wire:click="sortBy('transfer_date')">
              <div class="flex items-center gap-2">
                Date
                @if($sortBy === 'transfer_date')
                  <x-ui.icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="h-4 w-4" />
                @endif
              </div>
            </th>
            <th class="px-4 py-3 text-left cursor-pointer hover:bg-muted/50" wire:click="sortBy('asset_id')">
              <div class="flex items-center gap-2">
                Asset
                @if($sortBy === 'asset_id')
                  <x-ui.icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="h-4 w-4" />
                @endif
              </div>
            </th>
            <th class="px-4 py-3 text-left">From</th>
            <th class="px-4 py-3 text-left">To</th>
            <th class="px-4 py-3 text-left">Transferred By</th>
            <th class="px-4 py-3 text-left">Remarks</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($transfers as $transfer)
            <tr class="border-t hover:bg-muted/30">
              <td class="px-4 py-3">
                <div class="text-sm">
                  <div class="font-medium">{{ $transfer->transfer_date->format('M d, Y') }}</div>
                  <div class="text-muted-foreground">{{ $transfer->transfer_date->format('H:i') }}</div>
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm">
                  <div class="font-medium">{{ $transfer->asset->property_number ?? 'N/A' }}</div>
                  <div class="text-muted-foreground">{{ $transfer->asset->description ?? 'Deleted Asset' }}</div>
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm">
                  <div class="font-medium">{{ $transfer->originBranch->name ?? 'N/A' }}</div>
                  @if($transfer->origin_division_name)
                    <div class="text-muted-foreground">{{ $transfer->origin_division_name }}</div>
                  @endif
                  @if($transfer->origin_section_name)
                    <div class="text-muted-foreground text-xs">{{ $transfer->origin_section_name }}</div>
                  @endif
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm">
                  <div class="font-medium">{{ $transfer->currentBranch->name ?? 'N/A' }}</div>
                  @if($transfer->current_division_name)
                    <div class="text-muted-foreground">{{ $transfer->current_division_name }}</div>
                  @endif
                  @if($transfer->current_section_name)
                    <div class="text-muted-foreground text-xs">{{ $transfer->current_section_name }}</div>
                  @endif
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm">{{ $transfer->transferredBy->name ?? 'N/A' }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm text-muted-foreground max-w-xs truncate">
                  {{ $transfer->remarks ?: 'No remarks' }}
                </div>
              </td>
              <td class="px-4 py-3 text-right">
                @if($transfer->asset)
                  <a href="{{ route('assets.history', $transfer->asset->id) }}" 
                     class="inline-flex items-center gap-1 text-sm text-primary hover:text-primary/80">
                    <x-ui.icon name="eye" class="h-4 w-4" />
                    View Asset
                  </a>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                <div class="flex flex-col items-center gap-2">
                  <x-ui.icon name="search" class="h-8 w-8" />
                  <p>No transfer records found</p>
                  <p class="text-sm">Try adjusting your search criteria or date range</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </x-ui.table>
    </div>

    {{-- Pagination --}}
    @if($transfers->hasPages())
      <div class="p-4 border-t">
        {{ $transfers->links() }}
      </div>
    @endif
  </x-ui.card>

  {{-- Summary Stats --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <x-ui.card class="p-4">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-blue-100 rounded-lg">
          <x-ui.icon name="transfer" class="h-5 w-5 text-blue-600" />
        </div>
        <div>
          <p class="text-sm text-muted-foreground">Total Transfers</p>
          <p class="text-2xl font-semibold">{{ number_format($transfers->total()) }}</p>
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-4">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-green-100 rounded-lg">
          <x-ui.icon name="calendar" class="h-5 w-5 text-green-600" />
        </div>
        <div>
          <p class="text-sm text-muted-foreground">Date Range</p>
          <p class="text-sm font-semibold">
            {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('M d') : 'All' }} - 
            {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('M d, Y') : 'All' }}
          </p>
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-4">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-orange-100 rounded-lg">
          <x-ui.icon name="filter" class="h-5 w-5 text-orange-600" />
        </div>
        <div>
          <p class="text-sm text-muted-foreground">Showing Results</p>
          <p class="text-sm font-semibold">{{ $transfers->count() }} of {{ $transfers->total() }}</p>
        </div>
      </div>
    </x-ui.card>
  </div>
</div>