<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Property Officer Dashboard</h1>
      <p class="text-muted-foreground">Manage assets, track property transfers, and monitor asset lifecycle.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('assets.reports') }}" class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm hover:bg-accent">
        <x-ui.icon name="bar-chart" class="h-4 w-4" />
        <span>Reports</span>
      </a>
      <a href="{{ route('assets.form') }}" class="inline-flex items-center gap-2 rounded-md bg-primary text-primary-foreground px-3 py-2 text-sm hover:bg-primary/90">
        <x-ui.icon name="plus" class="h-4 w-4" />
        <span>Add Asset</span>
      </a>
    </div>
  </div>

  <!-- Enhanced KPI Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Total Assets</p>
          <p class="text-3xl font-bold">{{ number_format($totalAssets ?? 0) }}</p>
          <p class="text-xs text-muted-foreground mt-1">Across all categories</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
          <x-ui.icon name="box" class="h-6 w-6 text-blue-600" />
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Total Asset Value</p>
          <p class="text-3xl font-bold text-green-600">₱{{ number_format($assetsValue ?? 0, 2) }}</p>
          <p class="text-xs text-muted-foreground mt-1">Current valuation</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
          <x-ui.icon name="dollar-sign" class="h-6 w-6 text-green-600" />
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Active Assets</p>
          <p class="text-3xl font-bold text-emerald-600">{{ number_format($activeAssets ?? 0) }}</p>
          <p class="text-xs text-muted-foreground mt-1">In good condition</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center">
          <x-ui.icon name="check-circle" class="h-6 w-6 text-emerald-600" />
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Condemned</p>
          <p class="text-3xl font-bold text-amber-600">{{ number_format($condemnedAssets ?? 0) }}</p>
          <p class="text-xs text-muted-foreground mt-1">Requires action</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center">
          <x-ui.icon name="alert-triangle" class="h-6 w-6 text-amber-600" />
        </div>
      </div>
    </x-ui.card>
  </div>

  <!-- Charts Section -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <x-ui.card class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h4 class="text-lg font-medium">Monthly Asset Additions</h4>
        <x-ui.icon name="line-chart" class="h-5 w-5 text-muted-foreground" />
      </div>
      <div id="assets-monthly-line" style="min-height: 250px;"></div>
      <p class="text-xs text-muted-foreground mt-2">New assets added over the last 12 months</p>
    </x-ui.card>

    <x-ui.card class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h4 class="text-lg font-medium">Assets by Status</h4>
        <x-ui.icon name="pie-chart" class="h-5 w-5 text-muted-foreground" />
      </div>
      <div id="assets-status-donut" class="mx-auto" style="max-width:260px; min-height: 200px;"></div>
      <div class="mt-4 grid grid-cols-3 gap-2 text-center">
        <div class="p-2 rounded-lg bg-emerald-50">
          <div class="text-xs text-muted-foreground">Active</div>
          <div class="text-lg font-bold text-emerald-600">{{ $assetsByStatus['active'] ?? 0 }}</div>
        </div>
        <div class="p-2 rounded-lg bg-amber-50">
          <div class="text-xs text-muted-foreground">Condemned</div>
          <div class="text-lg font-bold text-amber-600">{{ $assetsByStatus['condemned'] ?? 0 }}</div>
        </div>
        <div class="p-2 rounded-lg bg-red-50">
          <div class="text-xs text-muted-foreground">Disposed</div>
          <div class="text-lg font-bold text-red-600">{{ $assetsByStatus['disposed'] ?? 0 }}</div>
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h4 class="text-lg font-medium">Assets by Category</h4>
        <x-ui.icon name="bar-chart" class="h-5 w-5 text-muted-foreground" />
      </div>
      <div id="assets-category-bar" style="min-height: 250px;"></div>
      <p class="text-xs text-muted-foreground mt-2">Total value by category</p>
    </x-ui.card>
  </div>

  <!-- Recent Activity Section -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <x-ui.card class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h4 class="text-lg font-medium">Recent Transfers (Last 30 Days)</h4>
        <a href="{{ route('assets.transfer-histories') }}" class="text-xs text-primary hover:underline">View all</a>
      </div>
      
      @if(!empty($recentTransfers) && $recentTransfers->count() > 0)
        <div class="space-y-3">
          @foreach($recentTransfers as $transfer)
            <div class="flex items-start justify-between border-l-2 border-primary pl-3 py-2">
              <div class="flex-1">
                <div class="font-medium text-sm">{{ $transfer->asset->description ?? 'N/A' }}</div>
                <div class="text-xs text-muted-foreground">
                  {{ $transfer->originBranch->name ?? 'N/A' }} → {{ $transfer->currentBranch->name ?? 'N/A' }}
                </div>
                <div class="text-xs text-muted-foreground mt-1">
                  By {{ $transfer->transferredBy->name ?? 'N/A' }} • {{ $transfer->transfer_date->diffForHumans() }}
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="text-sm text-muted-foreground">No recent transfers</div>
      @endif
    </x-ui.card>

    <x-ui.card class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h4 class="text-lg font-medium">Top Transfer Routes</h4>
        <span class="text-xs text-muted-foreground">Last 30 days</span>
      </div>
      
      @if(!empty($topRoutes) && $topRoutes->count() > 0)
        <ul class="text-sm divide-y">
          @foreach($topRoutes as $route)
            <li class="py-2 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <x-ui.icon name="arrow-right" class="h-4 w-4 text-muted-foreground" />
                <span>{{ $route->origin_name }} → {{ $route->current_name }}</span>
              </div>
              <span class="font-mono text-xs">{{ $route->c }} transfers</span>
            </li>
          @endforeach
        </ul>
      @else
        <div class="text-sm text-muted-foreground">No transfer data available</div>
      @endif
    </x-ui.card>
  </div>

  <x-ui.card class="p-4">
    <div class="text-sm text-gray-600">
      Manage all assets from <a href='{{ route('assets.index') }}' class='text-primary hover:underline'>Assets Management</a> 
      or view detailed <a href='{{ route('assets.reports') }}' class='text-primary hover:underline'>Asset Reports</a>.
    </div>
  </x-ui.card>
</div>

<script>
  // Initial dashboard payload for asset charts
  window.__property_dashboard_payload = {!! json_encode([
    'labels' => $monthlyLineLabels ?? [],
    'assetsValues' => $monthlyLineValues ?? [],
    'assetsByStatus' => is_object($assetsByStatus ?? null) ? ($assetsByStatus->toArray()) : ($assetsByStatus ?? []),
    'categoryLabels' => ($assetsByCategoryValue ?? collect())->pluck('name')->toArray(),
    'categoryValues' => ($assetsByCategoryValue ?? collect())->pluck('v')->toArray(),
  ], JSON_UNESCAPED_UNICODE) !!};

  // Dispatch event for charts to initialize
  window.dispatchEvent(new CustomEvent('propertyDashboard:update', { detail: window.__property_dashboard_payload }));
</script>
