<div class="space-y-6">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Admin Dashboard</h1>
      <p class="text-muted-foreground">Complete overview of assets, supplies, and system activity across all branches.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('admin.invitations') }}" class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm hover:bg-accent">
        <x-ui.icon name="users" class="h-4 w-4" />
        <span>Manage Users</span>
      </a>
      <a href="{{ route('admin.analytics') }}" class="inline-flex items-center gap-2 rounded-md bg-primary text-primary-foreground px-3 py-2 text-sm hover:bg-primary/90">
        <x-ui.icon name="bar-chart" class="h-4 w-4" />
        <span>Analytics</span>
      </a>
    </div>
  </div>

  {{-- Enhanced KPI Cards --}}
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Total Assets</p>
          <p class="text-3xl font-bold">{{ number_format($totalAssets ?? 0) }}</p>
          <p class="text-xs text-muted-foreground mt-1">Active inventory items</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
          <x-ui.icon name="package" class="h-6 w-6 text-blue-600" />
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Assets Value</p>
          <p class="text-3xl font-bold text-green-600">₱{{ number_format($assetsValue ?? 0, 2) }}</p>
          <p class="text-xs text-muted-foreground mt-1">Total asset valuation</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
          <x-ui.icon name="credit-card" class="h-6 w-6 text-green-600" />
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Supply SKUs</p>
          <p class="text-3xl font-bold text-purple-600">{{ number_format($supplySkus ?? 0) }}</p>
          <p class="text-xs text-muted-foreground mt-1">Unique supply items</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
          <x-ui.icon name="box" class="h-6 w-6 text-purple-600" />
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Supplies Value</p>
          <p class="text-3xl font-bold text-emerald-600">₱{{ number_format($suppliesValue ?? 0, 2) }}</p>
          <p class="text-xs text-muted-foreground mt-1">Current stock valuation</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center">
          <x-ui.icon name="credit-card" class="h-6 w-6 text-emerald-600" />
        </div>
      </div>
    </x-ui.card>
  </div>

  {{-- User & Branch Stats --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Total Users</p>
          <p class="text-2xl font-bold">{{ number_format($totalUsers ?? 0) }}</p>
          <p class="text-xs text-green-600 mt-1">{{ number_format($activeUsers ?? 0) }} active</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
          <x-ui.icon name="users" class="h-6 w-6 text-indigo-600" />
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Pending Approvals</p>
          <p class="text-2xl font-bold text-amber-600">{{ number_format($pendingApprovals ?? 0) }}</p>
          <p class="text-xs text-muted-foreground mt-1">User registrations</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center">
          <x-ui.icon name="clock" class="h-6 w-6 text-amber-600" />
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Branches</p>
          <p class="text-2xl font-bold">{{ number_format($totalBranches ?? 0) }}</p>
          <p class="text-xs text-green-600 mt-1">{{ number_format($activeBranches ?? 0) }} active</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-cyan-100 flex items-center justify-center">
          <x-ui.icon name="building" class="h-6 w-6 text-cyan-600" />
        </div>
      </div>
    </x-ui.card>
  </div>


  {{-- Enhanced Summary Sections --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <x-ui-card class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h4 class="text-lg font-medium">Top Supply Categories</h4>
  <x-ui.icon name="boxes" class="h-5 w-5 text-muted-foreground" />
      </div>
      @if(!empty($topSupplyCategories) && $topSupplyCategories->count() > 0)
        <ul class="space-y-3">
          @foreach($topSupplyCategories as $c)
            <li class="flex justify-between items-center py-3 px-4 rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100">
              <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                  <x-ui.icon name="folder" class="h-4 w-4 text-blue-600" />
                </div>
                <span class="font-medium">{{ $c->name }}</span>
              </div>
              <span class="font-mono text-sm bg-white px-2 py-1 rounded border">₱{{ number_format($c->v ?? 0, 2) }}</span>
            </li>
          @endforeach
        </ul>
      @else
        <div class="text-sm text-muted-foreground">No supply categories available</div>
      @endif
    </x-ui-card>

    <x-ui-card class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h4 class="text-lg font-medium">Recent Activity</h4>
        <a href="{{ route('admin.activity-logs') }}" class="text-xs text-primary hover:underline">View all</a>
      </div>
      @if(!empty($recentActivity) && $recentActivity->count())
        <div class="space-y-3 max-h-96 overflow-y-auto">
          @foreach($recentActivity->take(5) as $a)
            <div class="flex items-start gap-3 p-4 rounded-lg bg-gradient-to-r from-gray-50 to-slate-50 border border-gray-100">
              <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                <x-ui.icon name="zap" class="h-4 w-4 text-blue-600" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex justify-between items-start gap-2">
                  <div class="font-medium truncate text-sm">{{ $a->description }}</div>
                  <div class="text-xs text-muted-foreground flex-shrink-0">{{ $a->created_at->diffForHumans() }}</div>
                </div>
                <div class="text-xs text-muted-foreground mt-1">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                    {{ $a->action }}
                  </span>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="text-center py-8">
          <div class="text-sm text-muted-foreground">No recent activity</div>
        </div>
      @endif
    </x-ui-card>
  </div>

  {{-- Quick Actions --}}
  <x-ui.card class="p-6">
    <h3 class="text-lg font-medium mb-4">Admin Quick Actions</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
      <a href="{{ route('admin.invitations') }}" class="flex flex-col items-center justify-center p-4 rounded-lg border hover:bg-accent transition-colors">
        <x-ui.icon name="users" class="h-8 w-8 text-primary mb-2" />
        <span class="text-sm font-medium">User Management</span>
      </a>
      <a href="{{ route('admin.activity-logs') }}" class="flex flex-col items-center justify-center p-4 rounded-lg border hover:bg-accent transition-colors">
        <x-ui.icon name="activity" class="h-8 w-8 text-primary mb-2" />
        <span class="text-sm font-medium">Activity Logs</span>
      </a>
      <a href="{{ route('admin.transfer-histories') }}" class="flex flex-col items-center justify-center p-4 rounded-lg border hover:bg-accent transition-colors">
        <x-ui.icon name="history" class="h-8 w-8 text-primary mb-2" />
        <span class="text-sm font-medium">Transfer Histories</span>
      </a>
      <a href="{{ route('admin.analytics') }}" class="flex flex-col items-center justify-center p-4 rounded-lg border hover:bg-accent transition-colors">
        <x-ui.icon name="bar-chart" class="h-8 w-8 text-primary mb-2" />
        <span class="text-sm font-medium">System Analytics</span>
      </a>
    </div>
  </x-ui.card>
</div>

<script>
  // Initial dashboard payload for the bundled chart module
  window.__dashboard_payload = {!! json_encode([
    'labels' => $monthlyLineLabels ?? [],
    'assetsValues' => $monthlyLineValues ?? [],
    // For Supplies Added bar chart, pass stockOut, stockLow, stockOk
    'stockOut' => ($assetsByStatus['out'] ?? $assetsByStatus['out_of_stock'] ?? 0) + ($assetsByStatus['outofstock'] ?? 0),
    'stockLow' => $assetsByStatus['low'] ?? $assetsByStatus['low_stock'] ?? 0,
    'stockOk' => $assetsByStatus['active'] ?? 0,
    'assetsByStatus' => [
      'active' => $assetsByStatus['active'] ?? 0,
      'condemned' => $assetsByStatus['condemned'] ?? $assetsByStatus['condemn'] ?? 0,
      'disposed' => $assetsByStatus['disposed'] ?? 0,
    ],
  ], JSON_UNESCAPED_UNICODE) !!};

  // Dispatch event once so the charts module (bundled) can initialize immediately
  window.dispatchEvent(new CustomEvent('dashboard:update', { detail: window.__dashboard_payload }));
</script>
