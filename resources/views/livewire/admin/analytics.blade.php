<div class="space-y-6">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Admin Analytics</h1>
      <p class="text-sm text-muted-foreground mt-1">Deep dive into assets, supplies, and transfer insights</p>
    </div>
    <div class="flex items-center gap-3">
      <button class="inline-flex items-center gap-2 px-3 py-1 rounded-md border bg-card text-sm text-muted-foreground">
        <x-ui.icon name="refresh-ccw" class="w-4 h-4"/> Refresh
      </button>
      <x-ui.icon name="line-chart" class="h-8 w-8 text-muted-foreground" />
    </div>
  </div>

  {{-- Filters --}}
  <x-ui-card>
    <x-slot name="header">
      <div class="flex items-center gap-2">
        <x-ui.icon name="filter" class="w-5 h-5 text-primary" />
        <div class="text-sm font-medium">Filter & Period</div>
      </div>
    </x-slot>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <x-ui.label class="text-sm font-medium">View Period</x-ui.label>
        <select class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" wire:model.live="period">
          <option value="alltime">All Time</option>
          <option value="monthly">Monthly View</option>
          <option value="yearly">Yearly View</option>
        </select>
      </div>
      <div x-show="$wire.period !== 'alltime'">
        <x-ui.label class="text-sm font-medium">Year</x-ui.label>
        <select class="mt-1.5 flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" wire:model.live="selectedYear">
          @for($year = now()->year; $year >= now()->year - 5; $year--)
            <option value="{{ $year }}">{{ $year }}</option>
          @endfor
        </select>
      </div>
    </div>
  </x-ui-card>

  {{-- KPI summary --}}
  <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
    <x-ui-card>
      <div class="flex items-center justify-between gap-4">
        <div>
          <div class="text-sm text-muted-foreground">Total Assets</div>
          <div class="text-2xl font-semibold tracking-tight">{{ number_format($kpis['assetsTotal'] ?? 0) }}</div>
        </div>
        <div class="text-muted-foreground bg-muted/50 p-2 rounded-md ring-1 ring-border">
          <x-ui.icon name="package" class="w-5 h-5" />
        </div>
      </div>
    </x-ui-card>

    <x-ui-card>
      <div class="flex items-center justify-between gap-4">
        <div>
          <div class="text-sm text-muted-foreground">Asset Value</div>
          <div class="text-2xl font-semibold tracking-tight">₱{{ number_format($kpis['assetsValue'] ?? 0,2) }}</div>
        </div>
        <div class="text-muted-foreground bg-muted/50 p-2 rounded-md ring-1 ring-border">
          <x-ui.icon name="credit-card" class="w-5 h-5" />
        </div>
      </div>
    </x-ui-card>

    <x-ui-card>
      <div class="flex items-center justify-between gap-4">
        <div>
          <div class="text-sm text-muted-foreground">Supply SKUs</div>
          <div class="text-2xl font-semibold tracking-tight">{{ number_format($kpis['suppliesSkus'] ?? 0) }}</div>
        </div>
        <div class="text-muted-foreground bg-muted/50 p-2 rounded-md ring-1 ring-border">
          <x-ui.icon name="box" class="w-5 h-5" />
        </div>
      </div>
    </x-ui-card>

    <x-ui-card>
      <div class="flex items-center justify-between gap-4">
        <div>
          <div class="text-sm text-muted-foreground">Supplies Value</div>
          <div class="text-2xl font-semibold tracking-tight">₱{{ number_format($kpis['suppliesValue'] ?? 0,2) }}</div>
        </div>
        <div class="text-muted-foreground bg-muted/50 p-2 rounded-md ring-1 ring-border">
          <x-ui.icon name="dollar-sign" class="w-5 h-5" />
        </div>
      </div>
    </x-ui-card>

    <x-ui-card>
      <div class="flex items-center justify-between gap-4">
        <div>
          <div class="text-sm text-muted-foreground">Transfers</div>
          <div class="text-2xl font-semibold tracking-tight">{{ number_format($kpis['transfersInRange'] ?? 0) }}</div>
        </div>
        <div class="text-muted-foreground bg-muted/50 p-2 rounded-md ring-1 ring-border">
          <x-ui.icon name="arrow-right-left" class="w-5 h-5" />
        </div>
      </div>
    </x-ui-card>
  </div>

  {{-- Time series charts --}}
  <x-ui-card>
    <x-slot name="header">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <x-ui.icon name="activity" class="w-5 h-5 text-primary" />
          <div class="text-lg font-semibold">Monthly Activity Trends</div>
        </div>
        <div class="text-xs text-muted-foreground">Last 12 months</div>
      </div>
    </x-slot>
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      {{-- Assets created line chart --}}
      <div class="space-y-3">
        <div class="flex items-center justify-between">
          <h3 class="font-medium text-sm">Assets Created</h3>
          <x-ui.icon name="line-chart" class="w-4 h-4 text-muted-foreground" />
        </div>
        <div class="flex items-center justify-center h-48">
          <div id="assetsAnalyticsLine" class="w-full h-full"></div>
        </div>
      </div>

      {{-- Supplies added bar chart --}}
      <div class="space-y-3">
        <div class="flex items-center justify-between">
          <h3 class="font-medium text-sm">Supplies Added</h3>
          <x-ui.icon name="bar-chart" class="w-4 h-4 text-muted-foreground" />
        </div>
        <div class="flex items-center justify-center h-48">
          <div id="suppliesAnalyticsBar" class="w-full h-full"></div>
        </div>
      </div>

      {{-- Transfers bar chart --}}
      <div class="space-y-3">
        <div class="flex items-center justify-between">
          <h3 class="font-medium text-sm">Asset Transfers</h3>
          <x-ui.icon name="arrow-right-left" class="w-4 h-4 text-muted-foreground" />
        </div>
        <div class="flex items-center justify-center h-48">
          <div id="transfersAnalyticsBar" class="w-full h-full"></div>
        </div>
      </div>
    </div>
  </x-ui-card>

  {{-- Distribution Charts --}}
  <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <x-ui-card>
      <x-slot name="header">
        <div class="flex items-center gap-2">
          <x-ui.icon name="pie-chart" class="w-5 h-5 text-green-500" />
          <h3 class="font-medium">Assets by Status</h3>
        </div>
      </x-slot>
      <div class="flex items-center justify-center gap-6">
        <div class="flex items-center justify-center">
          <div id="assetsStatusDonut" class="w-44 h-44"></div>
        </div>
        <div class="space-y-3">
          <div class="flex items-center gap-3">
            <span class="inline-block h-3 w-3 rounded-full bg-green-500"></span>
            <span class="text-sm font-medium">Active</span>
            <span class="text-sm text-muted-foreground">{{ $assetsByStatus['active'] ?? 0 }}</span>
          </div>
          <div class="flex items-center gap-3">
            <span class="inline-block h-3 w-3 rounded-full bg-amber-500"></span>
            <span class="text-sm font-medium">Condemn</span>
            <span class="text-sm text-muted-foreground">{{ $assetsByStatus['condemn'] ?? 0 }}</span>
          </div>
          <div class="flex items-center gap-3">
            <span class="inline-block h-3 w-3 rounded-full bg-red-500"></span>
            <span class="text-sm font-medium">Disposed</span>
            <span class="text-sm text-muted-foreground">{{ $assetsByStatus['disposed'] ?? 0 }}</span>
          </div>
        </div>
      </div>
    </x-ui-card>

    <x-ui-card>
      <x-slot name="header">
        <div class="flex items-center gap-2">
          <x-ui.icon name="package" class="w-5 h-5 text-blue-500" />
          <h3 class="font-medium">Supply Stock Health</h3>
        </div>
      </x-slot>
      <div class="flex items-center justify-center gap-6">
        <div class="flex items-center justify-center">
          <div id="stockHealthDonut" class="w-44 h-44"></div>
        </div>
        <div class="space-y-3">
          <div class="flex items-center gap-3">
            <span class="inline-block h-3 w-3 rounded-full bg-red-500"></span>
            <span class="text-sm font-medium">Out of Stock</span>
            <span class="text-sm text-muted-foreground">{{ $stockOut ?? 0 }}</span>
          </div>
          <div class="flex items-center gap-3">
            <span class="inline-block h-3 w-3 rounded-full bg-amber-500"></span>
            <span class="text-sm font-medium">Low Stock</span>
            <span class="text-sm text-muted-foreground">{{ $stockLow ?? 0 }}</span>
          </div>
          <div class="flex items-center gap-3">
            <span class="inline-block h-3 w-3 rounded-full bg-green-500"></span>
            <span class="text-sm font-medium">Healthy</span>
            <span class="text-sm text-muted-foreground">{{ $stockOk ?? 0 }}</span>
          </div>
        </div>
      </div>
    </x-ui-card>

    {{-- Branch Rankings --}}
    <x-ui-card>
      <x-slot name="header">
        <div class="flex items-center gap-2">
          <x-ui.icon name="building" class="w-5 h-5 text-purple-500" />
          <h3 class="font-medium">Top Branches by Asset Count</h3>
        </div>
      </x-slot>
      <div class="space-y-3">
        @php $vals=$assetsCountByBranch?->pluck('c')->all() ?? []; $mx=max($vals ?: [1]); @endphp
        @forelse($assetsCountByBranch as $r)
          <div class="flex items-center justify-between p-3 rounded-lg bg-muted/30 border">
            <div class="flex-1">
              <div class="font-medium text-sm">{{ $r->name }}</div>
              <div class="w-full bg-muted rounded-full h-2 mt-2">
                <div class="h-2 bg-primary rounded-full transition-all duration-300" style="width: {{ $mx>0 ? (($r->c ?? 0)/$mx*100) : 0 }}%"></div>
              </div>
            </div>
            <div class="ml-4 text-right">
              <div class="font-mono text-lg font-semibold">{{ number_format($r->c ?? 0) }}</div>
              <div class="text-xs text-muted-foreground">assets</div>
            </div>
          </div>
        @empty
          <div class="text-center py-8">
            <x-ui.icon name="building" class="w-8 h-8 text-muted-foreground mx-auto mb-2" />
            <div class="text-sm text-muted-foreground">No branch data available</div>
          </div>
        @endforelse
      </div>
    </x-ui-card>

    <x-ui-card>
      <x-slot name="header">
        <div class="flex items-center gap-2">
          <x-ui.icon name="building" class="w-5 h-5 text-orange-500" />
          <h3 class="font-medium">Top Branches by Asset Value</h3>
        </div>
      </x-slot>
      <div class="space-y-3">
        @php $vals=$assetsValueByBranch?->pluck('v')->all() ?? []; $mx=max($vals ?: [1]); @endphp
        @forelse($assetsValueByBranch as $r)
          <div class="flex items-center justify-between p-3 rounded-lg bg-muted/30 border">
            <div class="flex-1">
              <div class="font-medium text-sm">{{ $r->name }}</div>
              <div class="w-full bg-muted rounded-full h-2 mt-2">
                <div class="h-2 bg-primary rounded-full transition-all duration-300" style="width: {{ $mx>0 ? (($r->v ?? 0)/$mx*100) : 0 }}%"></div>
              </div>
            </div>
            <div class="ml-4 text-right">
              <div class="font-mono text-lg font-semibold">₱{{ number_format($r->v ?? 0, 2) }}</div>
              <div class="text-xs text-muted-foreground">value</div>
            </div>
          </div>
        @empty
          <div class="text-center py-8">
            <x-ui.icon name="building" class="w-8 h-8 text-muted-foreground mx-auto mb-2" />
            <div class="text-sm text-muted-foreground">No branch data available</div>
          </div>
        @endforelse
      </div>
    </x-ui-card>

    {{-- Category Distribution Charts --}}
    <x-ui-card>
      <x-slot name="header">
        <div class="flex items-center gap-2">
          <x-ui.icon name="pie-chart" class="w-5 h-5 text-blue-500" />
          <h3 class="font-medium">Assets by Category</h3>
        </div>
      </x-slot>
      <div class="flex items-center justify-center gap-6">
        <div class="flex items-center justify-center">
          <div id="assetsCategoryPie" class="w-52 h-52"></div>
        </div>
        <div class="space-y-2 max-h-52 overflow-y-auto">
          @php $palette=['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#84cc16','#f97316','#14b8a6','#eab308']; @endphp
          @foreach($assetsValueByCategory as $i=>$r)
            @php $color=$palette[$i % count($palette)]; @endphp
            <div class="flex items-center justify-between gap-3 p-2 rounded-md bg-muted/30">
              <div class="flex items-center gap-2">
                <span class="inline-block h-3 w-3 rounded-full flex-shrink-0" style="background: {{ $color }}"></span>
                <span class="text-sm font-medium">{{ $r->name }}</span>
              </div>
              <span class="text-sm text-muted-foreground font-mono">₱{{ number_format($r->v ?? 0,2) }}</span>
            </div>
          @endforeach
        </div>
      </div>
    </x-ui-card>

    <x-ui-card>
      <x-slot name="header">
        <div class="flex items-center gap-2">
          <x-ui.icon name="pie-chart" class="w-5 h-5 text-emerald-500" />
          <h3 class="font-medium">Supplies by Category</h3>
        </div>
      </x-slot>
      <div class="flex items-center justify-center gap-6">
        <div class="flex items-center justify-center">
          <div id="suppliesCategoryPie" class="w-52 h-52"></div>
        </div>
        <div class="space-y-2 max-h-52 overflow-y-auto">
          @php $sPalette=['#10b981','#3b82f6','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#84cc16','#f97316','#14b8a6','#eab308']; @endphp
          @foreach($suppliesValueByCategory as $i=>$r)
            @php $color=$sPalette[$i % count($sPalette)]; @endphp
            <div class="flex items-center justify-between gap-3 p-2 rounded-md bg-muted/30">
              <div class="flex items-center gap-2">
                <span class="inline-block h-3 w-3 rounded-full flex-shrink-0" style="background: {{ $color }}"></span>
                <span class="text-sm font-medium">{{ $r->name }}</span>
              </div>
              <span class="text-sm text-muted-foreground font-mono">₱{{ number_format($r->v ?? 0,2) }}</span>
            </div>
          @endforeach
        </div>
      </div>
    </x-ui-card>

    {{-- Additional Analytics Tables --}}
    <x-ui-card class="xl:col-span-2">
      <x-slot name="header">
        <div class="flex items-center gap-2">
          <x-ui.icon name="bar-chart" class="w-5 h-5 text-indigo-500" />
          <h3 class="font-medium">Asset Value by Category</h3>
        </div>
      </x-slot>
      <div class="space-y-3">
        @php $vals=$assetsValueByCategory?->pluck('v')->all() ?? []; $mx=max($vals ?: [1]); @endphp
        @forelse($assetsValueByCategory as $r)
          <div class="flex items-center justify-between p-3 rounded-lg bg-muted/30 border">
            <div class="flex-1">
              <div class="font-medium text-sm">{{ $r->name }}</div>
              <div class="w-full bg-muted rounded-full h-3 mt-2">
                <div class="h-3 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all duration-500" style="width: {{ $mx>0 ? (($r->v ?? 0)/$mx*100) : 0 }}%"></div>
              </div>
            </div>
            <div class="ml-4 text-right">
              <div class="font-mono text-lg font-semibold">₱{{ number_format($r->v ?? 0, 2) }}</div>
              <div class="text-xs text-muted-foreground">total value</div>
            </div>
          </div>
        @empty
          <div class="text-center py-8">
            <x-ui.icon name="bar-chart" class="w-8 h-8 text-muted-foreground mx-auto mb-2" />
            <div class="text-sm text-muted-foreground">No category data available</div>
          </div>
        @endforelse
      </div>
    </x-ui-card>

    <x-ui-card class="xl:col-span-2">
      <x-slot name="header">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <x-ui.icon name="arrow-right-left" class="w-5 h-5 text-cyan-500" />
            <h3 class="font-medium">Top Transfer Routes</h3>
          </div>
          <a href="{{ route('admin.transfer-histories') }}" 
             class="inline-flex items-center gap-1 text-sm text-primary hover:text-primary/80 transition-colors"
             title="View all transfer histories">
            <span class="text-xs">View All</span>
            <x-ui.icon name="arrow-right" class="h-4 w-4" />
          </a>
        </div>
      </x-slot>
      <div class="space-y-3">
        @forelse($topRoutes as $r)
          <div class="flex items-center justify-between p-3 rounded-lg bg-muted/30 border">
            <div class="flex items-center gap-3">
              <div class="w-2 h-2 rounded-full bg-cyan-500 flex-shrink-0"></div>
              <div class="font-medium text-sm">
                {{ $r->origin_name ?? ('Branch #'.$r->origin_branch_id) }} 
                <x-ui.icon name="arrow-right" class="w-4 h-4 inline mx-2 text-muted-foreground" />
                {{ $r->current_name ?? ('Branch #'.$r->current_branch_id) }}
              </div>
            </div>
            <div class="text-right">
              <div class="font-mono text-lg font-semibold">{{ $r->c }}</div>
              <div class="text-xs text-muted-foreground">transfers</div>
            </div>
          </div>
        @empty
          <div class="text-center py-8">
            <x-ui.icon name="arrow-right-left" class="w-8 h-8 text-muted-foreground mx-auto mb-2" />
            <div class="text-sm text-muted-foreground">No transfer data available</div>
          </div>
        @endforelse
      </div>
    </x-ui-card>
  </div>
</div>

<script>
  // Analytics payload for charts
  window.__analytics_payload = {!! json_encode([
    'labels' => $labels ?? [],
    'assetsMonthly' => $assetsMonthly ?? [],
    'suppliesMonthly' => $suppliesMonthly ?? [],
    'transfersMonthly' => $transfersMonthly ?? [],
    'assetsByStatus' => is_object($assetsByStatus ?? null) ? $assetsByStatus->toArray() : ($assetsByStatus ?? []),
    'stockOut' => (int) ($stockOut ?? 0),
    'stockLow' => (int) ($stockLow ?? 0),
    'stockOk' => (int) ($stockOk ?? 0),
    'assetsValueByCategory' => ($assetsValueByCategory ?? collect())->map(function($item) {
      return ['name' => $item->name, 'v' => $item->v ?? 0];
    })->toArray(),
    'suppliesValueByCategory' => ($suppliesValueByCategory ?? collect())->map(function($item) {
      return ['name' => $item->name, 'v' => $item->v ?? 0];
    })->toArray(),
  ], JSON_UNESCAPED_UNICODE) !!};

  // Dispatch event to initialize charts
  window.dispatchEvent(new CustomEvent('analytics:update', { detail: window.__analytics_payload }));
</script>
