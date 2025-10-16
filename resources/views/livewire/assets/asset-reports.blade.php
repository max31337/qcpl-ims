<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">Assets Reports</h1>
    <div class="flex gap-2">
      <a href="{{ route('assets.transfer-histories') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
        <x-ui.icon name="history" class="mr-2 h-4 w-4" /> Transfer Histories
      </a>
      <button wire:click="exportAssets" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
        <x-ui.icon name="file-spreadsheet" class="mr-2 h-4 w-4" /> Download Excel
      </button>
      <button wire:click="exportPdf" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
        <x-ui.icon name="printer" class="mr-2 h-4 w-4" /> Print PDF
      </button>
    </div>
  </div>

  <div class="p-4 rounded-lg border bg-white shadow-sm">
    <form wire:submit.prevent="applyPendingFilters" class="flex flex-col gap-2">
      <x-reports.asset-filters :categories="$categories" :branches="$branches" :divisions="$divisions" :sections="$sections" />
      <div class="mt-2">
        <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-primary text-white hover:bg-primary/90 h-9 px-3">
          Apply Filters
        </button>
      </div>
    </form>
  </div>

  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="p-4 rounded-lg border bg-white shadow-sm">
      <p class="text-sm text-gray-500">Total Assets</p>
      <p class="mt-2 text-2xl font-bold">{{ number_format($summary['total_assets'] ?? 0) }}</p>
    </div>
    <div class="p-4 rounded-lg border bg-white shadow-sm">
      <p class="text-sm text-gray-500">Total Value</p>
      <p class="mt-2 text-2xl font-bold">₱{{ number_format($summary['total_value'] ?? 0, 2) }}</p>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="rounded-lg border bg-white shadow-sm">
      <div class="p-4 border-b font-medium">By Status</div>
      <div class="p-4">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-gray-500">
              <th class="py-2">Status</th>
              <th class="py-2">Count</th>
              <th class="py-2">Value</th>
            </tr>
          </thead>
          <tbody>
            @forelse(($summary['by_status'] ?? []) as $row)
              <tr class="border-t">
                <td class="py-2 capitalize">{{ $row->status }}</td>
                <td class="py-2">{{ number_format($row->count) }}</td>
                <td class="py-2">₱{{ number_format($row->value, 2) }}</td>
              </tr>
            @empty
              <tr><td colspan="3" class="py-6 text-center text-gray-500">No data</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="rounded-lg border bg-white shadow-sm">
      <div class="p-4 border-b font-medium">By Category</div>
      <div class="p-4">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-gray-500">
              <th class="py-2">Category</th>
              <th class="py-2">Count</th>
              <th class="py-2">Value</th>
            </tr>
          </thead>
          <tbody>
            @forelse(($summary['by_category'] ?? []) as $row)
              <tr class="border-t">
                <td class="py-2">{{ optional($row->category)->name }}</td>
                <td class="py-2">{{ number_format($row->count) }}</td>
                <td class="py-2">₱{{ number_format($row->value, 2) }}</td>
              </tr>
            @empty
              <tr><td colspan="3" class="py-6 text-center text-gray-500">No data</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
