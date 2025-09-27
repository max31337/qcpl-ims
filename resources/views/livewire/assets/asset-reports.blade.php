<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">Assets Reports</h1>
    <div class="flex gap-2">
      <button wire:click="exportAssets" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Download Excel</button>
      <button wire:click="exportPdf" class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-white hover:bg-gray-800">Print PDF</button>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-5 gap-4 p-4 rounded-lg border bg-white shadow-sm">
    <input type="date" wire:model="dateFrom" class="h-10 w-full rounded-md border px-3 text-sm" />
    <input type="date" wire:model="dateTo" class="h-10 w-full rounded-md border px-3 text-sm" />
    <select wire:model="categoryFilter" class="h-10 w-full rounded-md border px-3 text-sm">
      <option value="">All Categories</option>
      @foreach($categories as $c)
        <option value="{{ $c->id }}">{{ $c->name }}</option>
      @endforeach
    </select>
    <select wire:model="statusFilter" class="h-10 w-full rounded-md border px-3 text-sm">
      <option value="">All Status</option>
      <option value="active">Active</option>
      <option value="condemn">Condemn</option>
      <option value="disposed">Disposed</option>
    </select>
    <select wire:model="branchFilter" class="h-10 w-full rounded-md border px-3 text-sm">
      <option value="">All Branches</option>
      @foreach($branches as $b)
        <option value="{{ $b->id }}">{{ $b->name }}</option>
      @endforeach
    </select>
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
