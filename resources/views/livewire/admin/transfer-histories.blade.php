<div class="space-y-6">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Transfer Histories</h1>
      <p class="text-muted-foreground">Complete history of all asset transfers and movements</p>
    </div>
    <div class="flex items-center gap-2">
      <div class="p-2 bg-blue-100 rounded-lg">
        <x-ui.icon name="history" class="h-6 w-6 text-blue-600" />
      </div>
    </div>
  </div>

  {{-- Filters --}}
  <x-ui.card class="p-4">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold flex items-center gap-2">
        <x-ui.icon name="filter" class="h-5 w-5 text-primary" />
        Filters & Search
      </h2>
      @if($search || $fromDate || $toDate)
        <button wire:click="$set('search', ''); $set('fromDate', ''); $set('toDate', '');" 
                class="text-sm text-muted-foreground hover:text-foreground flex items-center gap-1">
          <x-ui.icon name="x" class="h-4 w-4" />
          Clear Filters
        </button>
      @endif
    </div>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <x-ui.label class="flex items-center gap-2">
          <x-ui.icon name="search" class="h-4 w-4" />
          Search
        </x-ui.label>
        <x-ui.input type="text" placeholder="Asset description, property number, branch, remarks..." wire:model.live.debounce.300ms="search" />
      </div>
      <div>
        <x-ui.label class="flex items-center gap-2">
          <x-ui.icon name="calendar" class="h-4 w-4" />
          From Date
        </x-ui.label>
        <x-ui.input type="date" wire:model.live="fromDate" />
      </div>
      <div>
        <x-ui.label class="flex items-center gap-2">
          <x-ui.icon name="calendar" class="h-4 w-4" />
          To Date
        </x-ui.label>
        <x-ui.input type="date" wire:model.live="toDate" />
      </div>
      <div>
        <x-ui.label class="flex items-center gap-2">
          <x-ui.icon name="list" class="h-4 w-4" />
          Per Page
        </x-ui.label>
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
    <div class="p-4 border-b bg-muted/20">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold flex items-center gap-2">
          <x-ui.icon name="transfer" class="h-5 w-5 text-primary" />
          Transfer Records
        </h2>
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2 text-sm text-muted-foreground">
            <x-ui.icon name="list" class="h-4 w-4" />
            <span>{{ $transfers->total() }} total records</span>
          </div>
          @if($transfers->hasPages())
            <div class="flex items-center gap-2 text-sm text-muted-foreground">
              <x-ui.icon name="eye" class="h-4 w-4" />
              <span>Page {{ $transfers->currentPage() }} of {{ $transfers->lastPage() }}</span>
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="overflow-x-auto">
      <x-ui.table>
        <thead class="bg-muted/30">
          <tr>
            <th class="px-4 py-3 text-left cursor-pointer hover:bg-muted/70 transition-colors" wire:click="sortBy('transfer_date')">
              <div class="flex items-center gap-2 font-semibold">
                <x-ui.icon name="calendar" class="h-4 w-4 text-muted-foreground" />
                Date
                @if($sortBy === 'transfer_date')
                  <x-ui.icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="h-4 w-4 text-primary" />
                @else
                  <x-ui.icon name="arrow-up-down" class="h-4 w-4 text-muted-foreground opacity-50" />
                @endif
              </div>
            </th>
            <th class="px-4 py-3 text-left cursor-pointer hover:bg-muted/70 transition-colors" wire:click="sortBy('asset_id')">
              <div class="flex items-center gap-2 font-semibold">
                <x-ui.icon name="package" class="h-4 w-4 text-muted-foreground" />
                Asset
                @if($sortBy === 'asset_id')
                  <x-ui.icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="h-4 w-4 text-primary" />
                @else
                  <x-ui.icon name="arrow-up-down" class="h-4 w-4 text-muted-foreground opacity-50" />
                @endif
              </div>
            </th>
            <th class="px-4 py-3 text-left">
              <div class="flex items-center gap-2 font-semibold">
                <x-ui.icon name="arrow-left" class="h-4 w-4 text-muted-foreground" />
                From
              </div>
            </th>
            <th class="px-4 py-3 text-left">
              <div class="flex items-center gap-2 font-semibold">
                <x-ui.icon name="arrow-right" class="h-4 w-4 text-muted-foreground" />
                To
              </div>
            </th>
            <th class="px-4 py-3 text-left">
              <div class="flex items-center gap-2 font-semibold">
                <x-ui.icon name="users" class="h-4 w-4 text-muted-foreground" />
                Transferred By
              </div>
            </th>
            <th class="px-4 py-3 text-left">
              <div class="flex items-center gap-2 font-semibold">
                <x-ui.icon name="info" class="h-4 w-4 text-muted-foreground" />
                Remarks
              </div>
            </th>
            <th class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-2 font-semibold">
                <x-ui.icon name="eye" class="h-4 w-4 text-muted-foreground" />
                Actions
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          @forelse($transfers as $transfer)
            <tr class="border-t hover:bg-muted/30 transition-colors">
              <td class="px-4 py-3">
                <div class="text-sm">
                  <div class="font-medium flex items-center gap-2">
                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    {{ $transfer->transfer_date->format('M d, Y') }}
                  </div>
                  <div class="text-muted-foreground ml-4">{{ $transfer->transfer_date->format('H:i A') }}</div>
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm">
                  <div class="font-medium flex items-center gap-2">
                    <x-ui.icon name="package" class="h-4 w-4 text-blue-600" />
                    {{ $transfer->asset->property_number ?? 'N/A' }}
                  </div>
                  <div class="text-muted-foreground max-w-xs truncate">{{ $transfer->asset->description ?? 'Deleted Asset' }}</div>
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm">
                  <div class="font-medium flex items-center gap-2">
                    <x-ui.icon name="building" class="h-4 w-4 text-orange-600" />
                    {{ $transfer->originBranch->name ?? 'N/A' }}
                  </div>
                  @if($transfer->origin_division_name)
                    <div class="text-muted-foreground ml-6">{{ $transfer->origin_division_name }}</div>
                  @endif
                  @if($transfer->origin_section_name)
                    <div class="text-muted-foreground text-xs ml-6">{{ $transfer->origin_section_name }}</div>
                  @endif
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm">
                  <div class="font-medium flex items-center gap-2">
                    <x-ui.icon name="building" class="h-4 w-4 text-green-600" />
                    {{ $transfer->currentBranch->name ?? 'N/A' }}
                  </div>
                  @if($transfer->current_division_name)
                    <div class="text-muted-foreground ml-6">{{ $transfer->current_division_name }}</div>
                  @endif
                  @if($transfer->current_section_name)
                    <div class="text-muted-foreground text-xs ml-6">{{ $transfer->current_section_name }}</div>
                  @endif
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm flex items-center gap-2">
                  <x-ui.icon name="users" class="h-4 w-4 text-muted-foreground" />
                  {{ $transfer->transferredBy->name ?? 'N/A' }}
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm text-muted-foreground max-w-xs">
                  <div class="flex items-start gap-2">
                    <x-ui.icon name="info" class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                    <span class="truncate">{{ $transfer->remarks ?: 'No remarks' }}</span>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 text-right">
                @if($transfer->asset)
                  <a href="{{ route('assets.history', $transfer->asset->id) }}" 
                     class="inline-flex items-center gap-2 px-3 py-1.5 text-sm text-primary hover:text-primary/80 hover:bg-primary/10 rounded-md transition-colors">
                    <x-ui.icon name="eye" class="h-4 w-4" />
                    View Asset
                  </a>
                @else
                  <span class="text-sm text-muted-foreground">Asset Deleted</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-4 py-12 text-center text-muted-foreground">
                <div class="flex flex-col items-center gap-4">
                  <div class="p-4 bg-muted/30 rounded-full">
                    <x-ui.icon name="transfer" class="h-12 w-12 text-muted-foreground" />
                  </div>
                  <div>
                    <p class="text-lg font-medium">No transfer records found</p>
                    <p class="text-sm mt-1">Try adjusting your search criteria or date range</p>
                  </div>
                  @if($search || $fromDate || $toDate)
                    <button wire:click="$set('search', ''); $set('fromDate', ''); $set('toDate', '');" 
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-colors">
                      <x-ui.icon name="x" class="h-4 w-4" />
                      Clear All Filters
                    </button>
                  @endif
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
        {{ $transfers->links('pagination::custom-light') }}
      </div>
    @endif
  </x-ui.card>

  {{-- Summary Stats --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <x-ui.card class="p-4 hover:shadow-md transition-shadow">
      <div class="flex items-center gap-3">
        <div class="p-3 bg-blue-100 rounded-xl">
          <x-ui.icon name="transfer" class="h-6 w-6 text-blue-600" />
        </div>
        <div class="flex-1">
          <p class="text-sm font-medium text-muted-foreground">Total Transfers</p>
          <p class="text-3xl font-bold text-blue-900">{{ number_format($transfers->total()) }}</p>
          <p class="text-xs text-muted-foreground mt-1">
            @if($fromDate || $toDate)
              In selected period
            @else
              All time
            @endif
          </p>
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-4 hover:shadow-md transition-shadow">
      <div class="flex items-center gap-3">
        <div class="p-3 bg-green-100 rounded-xl">
          <x-ui.icon name="calendar" class="h-6 w-6 text-green-600" />
        </div>
        <div class="flex-1">
          <p class="text-sm font-medium text-muted-foreground">Date Range</p>
          <p class="text-lg font-bold text-green-900">
            {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('M j') : 'All' }} - 
            {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('M j, Y') : 'All' }}
          </p>
          <p class="text-xs text-muted-foreground mt-1">
            @if($fromDate && $toDate)
              {{ \Carbon\Carbon::parse($fromDate)->diffInDays(\Carbon\Carbon::parse($toDate)) + 1 }} days
            @else
              No date filter
            @endif
          </p>
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-4 hover:shadow-md transition-shadow">
      <div class="flex items-center gap-3">
        <div class="p-3 bg-orange-100 rounded-xl">
          <x-ui.icon name="eye" class="h-6 w-6 text-orange-600" />
        </div>
        <div class="flex-1">
          <p class="text-sm font-medium text-muted-foreground">Showing Results</p>
          <p class="text-lg font-bold text-orange-900">{{ $transfers->count() }} of {{ $transfers->total() }}</p>
          <p class="text-xs text-muted-foreground mt-1">
            @if($transfers->hasPages())
              Page {{ $transfers->currentPage() }} of {{ $transfers->lastPage() }}
            @else
              All results
            @endif
          </p>
        </div>
      </div>
    </x-ui.card>
  </div>
</div>