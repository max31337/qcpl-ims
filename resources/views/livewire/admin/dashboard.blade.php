
@php $role = auth()->user()->role ?? null; @endphp
@if($role === 'supply_officer')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Supply Officer Dashboard</h1>
      <p class="text-muted-foreground">Monitor supply levels, track inventory value, and manage stock efficiently.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('supplies.reports') }}" class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm hover:bg-accent">
        <x-ui.icon name="bar-chart" class="h-4 w-4" />
        <span>Reports</span>
      </a>
      <a href="{{ route('supplies.create') }}" class="inline-flex items-center gap-2 rounded-md bg-primary text-primary-foreground px-3 py-2 text-sm hover:bg-primary/90">
        <x-ui.icon name="plus" class="h-4 w-4" />
        <span>Add Supply</span>
      </a>
    </div>
  </div>

  <!-- Enhanced KPI Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Total Supply Items</p>
          <p class="text-3xl font-bold">{{ number_format($supplySkus ?? 0) }}</p>
          <p class="text-xs text-muted-foreground mt-1">Active inventory SKUs</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
          <x-ui.icon name="package" class="h-6 w-6 text-blue-600" />
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Low Stock Alerts</p>
          <p class="text-3xl font-bold text-amber-600">{{ number_format($lowStock ?? 0) }}</p>
          <p class="text-xs text-muted-foreground mt-1">Items below minimum</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center">
          <x-ui.icon name="alert-triangle" class="h-6 w-6 text-amber-600" />
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Total Inventory Value</p>
          <p class="text-3xl font-bold text-green-600">₱{{ number_format($suppliesValue ?? 0, 2) }}</p>
          <p class="text-xs text-muted-foreground mt-1">Current stock valuation</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
          <x-ui.icon name="dollar-sign" class="h-6 w-6 text-green-600" />
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Out of Stock</p>
          <p class="text-3xl font-bold text-red-600">{{ number_format($stockOut ?? 0) }}</p>
          <p class="text-xs text-muted-foreground mt-1">Urgent reorder needed</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center">
          <x-ui.icon name="x-circle" class="h-6 w-6 text-red-600" />
        </div>
      </div>
    </x-ui.card>
  </div>
  <!-- Enhanced Analytics Charts Section -->
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold">Supply Analytics</h2>
      <a href="{{ route('supplies.analytics') }}" class="text-sm text-primary hover:underline">View detailed analytics →</a>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <x-ui-card class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h4 class="text-lg font-medium">Monthly Supply Additions</h4>
          <x-ui.icon name="line-chart" class="h-5 w-5 text-muted-foreground" />
        </div>
        <div id="supply-monthly-line" style="min-height: 250px;"></div>
        <p class="text-xs text-muted-foreground mt-2">New supplies added over the last 12 months</p>
      </x-ui-card>

      <x-ui-card class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h4 class="text-lg font-medium">Supplies by Category</h4>
          <x-ui.icon name="bar-chart" class="h-5 w-5 text-muted-foreground" />
        </div>
        <div id="supply-categories-bar" style="min-height: 250px;"></div>
        <p class="text-xs text-muted-foreground mt-2">Distribution of supplies across categories</p>
      </x-ui-card>

      <x-ui-card class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h4 class="text-lg font-medium">Stock Health Overview</h4>
          <x-ui.icon name="pie-chart" class="h-5 w-5 text-muted-foreground" />
        </div>
        <div id="supply-stock-donut" class="mx-auto" style="max-width:260px; min-height: 200px;"></div>
        <div class="mt-4 grid grid-cols-3 gap-2 text-center">
          <div class="p-2 rounded-lg bg-emerald-50">
            <div class="text-xs text-muted-foreground">Healthy Stock</div>
            <div class="text-lg font-bold text-emerald-600">{{ (int)($stockOk ?? 0) }}</div>
          </div>
          <div class="p-2 rounded-lg bg-amber-50">
            <div class="text-xs text-muted-foreground">Low Stock</div>
            <div class="text-lg font-bold text-amber-600">{{ (int)($stockLow ?? 0) }}</div>
          </div>
          <div class="p-2 rounded-lg bg-red-50">
            <div class="text-xs text-muted-foreground">Out of Stock</div>
            <div class="text-lg font-bold text-red-600">{{ (int)($stockOut ?? 0) }}</div>
          </div>
        </div>
      </x-ui.card>
    </div>
  </div>
  {{-- Use analytics-lite to drive these charts with the same logic as /supplies/analytics --}}
  @livewire('supplies.supply-analytics-lite')

  {{-- Initialize charts with available data --}}
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Wait a bit for Livewire components to fully render
      setTimeout(() => {
        // Manual chart initialization with current data from dashboard
        const chartData = {
          categories: @json(($topSupplyCategories ?? collect())->pluck('name')->toArray()),
          categoryCounts: @json(($topSupplyCategories ?? collect())->pluck('v')->toArray()), // Using values as counts for now
          categoryValues: @json(($topSupplyCategories ?? collect())->pluck('v')->toArray()),
          monthlyLabels: @json(collect(range(0, 11))->map(fn($i) => now()->subMonths(11-$i)->format('M Y'))->toArray()),
          monthlyAdds: @json($suppliesMonthlyValues ?? array_fill(0, 12, 0)),
          stockHealth: {
            ok: {{ $stockOk ?? 0 }},
            low: {{ $stockLow ?? 0 }}, 
            out: {{ $stockOut ?? 0 }}
          }
        };

        console.log('Dashboard chart data:', chartData);

        // Trigger chart rendering
        window.dispatchEvent(new CustomEvent('supplyAnalytics:update', {
          detail: chartData
        }));
      }, 1000); // Increased timeout to ensure DOM is ready
    });
  </script>
  
  {{-- Decision widgets --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
    <x-ui-card>
      <div class="flex items-center justify-between mb-2">
        <h4 class="text-sm font-medium">Critical Low Stock</h4>
        <a href="{{ route('supplies.index') }}" class="text-xs text-primary hover:underline">Manage</a>
      </div>

      @php
        // Use direct query since Livewire component data isn't working
        $currentUser = auth()->user();
        $criticalLowStock = \App\Models\Supply::forUser($currentUser)
          ->whereColumn('current_stock', '<', 'min_stock')
          ->select(
            'id', 'supply_number', 'description', 'current_stock', 'min_stock', 'unit_cost',
            \DB::raw('(min_stock - current_stock) as deficit'),
            \DB::raw('(min_stock - current_stock) * unit_cost as reorder_value')
          )
          ->orderByDesc(\DB::raw('(min_stock - current_stock) * unit_cost'))
          ->limit(8)
          ->get();
        
        $totalReorderGap = \App\Models\Supply::forUser($currentUser)
          ->whereColumn('current_stock', '<', 'min_stock')
          ->selectRaw('SUM( (min_stock - current_stock) * unit_cost ) as gap')
          ->value('gap') ?? 0;
      @endphp
      
      @if($criticalLowStock->count() > 0)
        <div class="overflow-x-auto">
          <table class="min-w-full text-xs">
            <thead>
              <tr class="text-muted-foreground text-[11px]">
                <th class="px-2 py-1 text-left">SKU</th>
                <th class="px-2 py-1 text-right">Stock</th>
                <th class="px-2 py-1 text-right">Min</th>
                <th class="px-2 py-1 text-right">Deficit</th>
                <th class="px-2 py-1 text-right">Reorder ₱</th>
              </tr>
            </thead>
            <tbody>
            @foreach($criticalLowStock as $i)
              <tr class="border-t">
                <td class="px-2 py-1">
                  <div class="font-medium">{{ $i->description }}</div>
                  <div class="text-[11px] text-muted-foreground">{{ $i->supply_number }}</div>
                </td>
                <td class="px-2 py-1 text-right">{{ $i->current_stock }}</td>
                <td class="px-2 py-1 text-right">{{ $i->min_stock }}</td>
                <td class="px-2 py-1 text-right text-amber-600">{{ max(0, ($i->min_stock - $i->current_stock)) }}</td>
                <td class="px-2 py-1 text-right font-semibold">₱{{ number_format(($i->min_stock - $i->current_stock) * (float)$i->unit_cost, 2) }}</td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        <div class="mt-3 text-xs text-muted-foreground">Total reorder gap: <span class="font-medium text-foreground">₱{{ number_format($totalReorderGap, 2) }}</span></div>
      @else
        <div class="text-sm text-muted-foreground">No low-stock items — you're good!</div>
      @endif
    </x-ui-card>

    <x-ui-card>

      @php
        // Direct query for stale SKUs (90+ days)
        $staleThreshold = now()->subDays(90);
        $staleSkusList = \App\Models\Supply::forUser($currentUser)
          ->where('current_stock', '>', 0)
          ->whereRaw('COALESCE(last_updated, updated_at) < ?', [$staleThreshold])
          ->select(
            'id', 'supply_number', 'description', 'current_stock', 'unit_cost', 'updated_at', 'last_updated',
            \DB::raw('(current_stock * unit_cost) as on_hand_value')
          )
          ->orderByDesc('on_hand_value')
          ->limit(8)
          ->get();
        
        $staleSkusTotal = \App\Models\Supply::forUser($currentUser)
          ->where('current_stock', '>', 0)
          ->whereRaw('COALESCE(last_updated, updated_at) < ?', [$staleThreshold])
          ->count();
      @endphp
      
      <div class="flex items-center justify-between mb-2">
        <h4 class="text-sm font-medium">Stale SKUs (90+ days)</h4>
        <div class="text-xs text-muted-foreground">{{ number_format($staleSkusTotal) }} items</div>
      </div>
      
      @if($staleSkusList->count() > 0)
        <ul class="divide-y">
          @foreach($staleSkusList as $s)
            <li class="py-2 text-sm flex items-start justify-between gap-2">
              <div>
                <div class="font-medium">{{ $s->description }}</div>
                <div class="text-xs text-muted-foreground">{{ $s->supply_number }} • Updated {{ optional($s->last_updated ?? $s->updated_at)->diffForHumans() }}</div>
              </div>
              <div class="text-right">
                <div class="text-xs text-muted-foreground">On-hand</div>
                <div class="font-mono">₱{{ number_format(($s->current_stock * (float)$s->unit_cost), 2) }}</div>
              </div>
            </li>
          @endforeach
        </ul>
      @else
        <div class="text-sm text-muted-foreground">No stale SKUs — great rotation.</div>
      @endif
    </x-ui-card>

    <x-ui-card>
      <div class="flex items-center justify-between mb-2">
        <h4 class="text-sm font-medium">Category Risk</h4>
        <a href="{{ route('supplies.analytics') }}" class="text-xs text-primary hover:underline">View analytics</a>
      </div>
      @php
        // Direct query for category risk
        $categoryRisk = \App\Models\Supply::forUser($currentUser)
          ->leftJoin('categories', 'supplies.category_id', '=', 'categories.id')
          ->whereColumn('current_stock', '<', 'min_stock')
          ->selectRaw("COALESCE(categories.name, 'Uncategorized') as name, COUNT(*) as c")
          ->groupBy('categories.name')
          ->orderByDesc('c')
          ->limit(5)
          ->get();
      @endphp
      
      @if($categoryRisk->count() > 0)
        <ul class="text-sm divide-y">
          @foreach($categoryRisk as $c)
            <li class="py-2 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="inline-block h-2 w-2 rounded-full bg-amber-500"></span>
                <span>{{ $c->name }}</span>
              </div>
              <span class="font-mono text-xs">{{ $c->c }} low</span>
            </li>
          @endforeach
        </ul>
      @else
        <div class="text-sm text-muted-foreground">No categories at risk.</div>
      @endif

      @if(!empty($topOnHandSupplies) && $topOnHandSupplies->count())
        <div class="mt-3">
          <div class="text-xs text-muted-foreground mb-1">Top On-hand Value</div>
          <ul class="text-sm space-y-1">
            @foreach($topOnHandSupplies as $t)
              <li class="flex items-center justify-between">
                <span class="truncate">{{ $t->description }}</span>
                <span class="font-mono">₱{{ number_format(($t->current_stock * (float)$t->unit_cost), 2) }}</span>
              </li>
            @endforeach
          </ul>
        </div>
      @endif
    </x-ui-card>
  </div>
  <x-ui.card class="p-4 mt-4">
    <div class="text-sm text-gray-600">For full analytics, go to <a href='{{ route('supplies.analytics') }}' class='text-primary hover:underline'>Supply Analytics</a>.</div>
  </x-ui.card>
  {{-- Old dashboard payload removed; charts are driven by supplyAnalytics:update from lite component --}}
</div>
@else
<div class="space-y-6">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Admin Dashboard</h1>
      <p class="text-sm text-muted-foreground mt-1">Overview of assets, supplies, and activity — quick insights at a glance.</p>
    </div>
    <div class="flex items-center gap-3">
      <button class="inline-flex items-center gap-2 px-3 py-1 rounded-md border bg-card text-sm text-muted-foreground"><x-ui.icon name="refresh-ccw" class="w-4 h-4"/> Refresh</button>
      <a href="{{ route('admin.analytics') }}" class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-primary text-white text-sm">Analytics</a>
    </div>
  </div>

  {{-- KPI cards --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <x-ui-card>
      <div class="flex items-center justify-between gap-4">
        <div>
          <div class="text-sm text-muted-foreground">Total Assets</div>
          <div class="text-2xl font-semibold tracking-tight">{{ number_format($totalAssets ?? 0) }}</div>
        </div>
        <div class="text-muted-foreground bg-muted/50 p-2 rounded-md ring-1 ring-border">
          <x-ui.icon name="package" class="w-5 h-5" />
        </div>
      </div>
    </x-ui-card>

    <x-ui-card>
      <div class="flex items-center justify-between gap-4">
        <div>
          <div class="text-sm text-muted-foreground">Assets Value</div>
          <div class="text-2xl font-semibold tracking-tight">₱{{ number_format($assetsValue ?? 0, 2) }}</div>
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
          <div class="text-2xl font-semibold tracking-tight">{{ number_format($supplySkus ?? 0) }}</div>
        </div>
        <div class="text-muted-foreground bg-muted/50 p-2 rounded-md ring-1 ring-border">
          <x-ui.icon name="box" class="w-5 h-5" />
        </div>
      </div>
    </x-ui-card>

    <x-ui-card>
      <div class="flex items-center justify-between gap-4">
        <div>
          <div class="text-sm text-muted-foreground">Supplies On-hand Value</div>
          <div class="text-2xl font-semibold tracking-tight">₱{{ number_format($suppliesValue ?? 0, 2) }}</div>
        </div>
        <div class="text-muted-foreground bg-muted/50 p-2 rounded-md ring-1 ring-border">
          <x-ui.icon name="dollar-sign" class="w-5 h-5" />
        </div>
      </div>
    </x-ui-card>
  </div>

  {{-- Charts & summaries (shadcn-like card layout) --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <x-ui-card>
      <x-slot name="header">
        <div class="flex items-center justify-between">
          <div>
            <div class="flex items-center gap-2">
              <x-ui.icon name="line-chart" class="w-5 h-5 text-primary" />
              <div class="text-sm font-medium">Assets Created</div>
            </div>
            <div class="text-xs text-muted-foreground">Last 12 months</div>
          </div>
        </div>
      </x-slot>
      <div class="flex items-center justify-center h-56 md:h-64">
        <div id="assetsLineChart" aria-label="Assets created over time" class="w-full h-full"></div>
      </div>
    </x-ui-card>

    <x-ui-card>
      <x-slot name="header">
        <div class="flex items-center justify-between">
          <div>
            <div class="flex items-center gap-2">
              <x-ui.icon name="bar-chart" class="w-5 h-5 text-amber-500" />
              <div class="text-sm font-medium">Supplies Stock Health</div>
            </div>
            <div class="text-xs text-muted-foreground">Out / Low / Healthy</div>
          </div>
        </div>
      </x-slot>
      <div class="flex items-center justify-center h-56 md:h-64">
        <div id="suppliesBarChart" aria-label="Supplies stock health" class="w-full h-full"></div>
      </div>
    </x-ui-card>

    <x-ui-card>
      <x-slot name="header">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <x-ui.icon name="pie-chart" class="w-5 h-5 text-rose-500" />
            <div class="text-sm font-medium">Assets by Status</div>
          </div>
          <div class="flex items-center gap-2">
            <div class="text-xs text-muted-foreground hidden sm:block">Toggle</div>
            <div id="assetsByStatusToggles" class="flex items-center gap-1">
              @foreach(($assetsByStatus ?? []) as $status => $count)
                <button type="button" data-status="{{ $status }}" aria-pressed="true" class="status-toggle inline-flex items-center gap-2 px-2 py-1 rounded-md border bg-background hover:bg-muted text-xs transition">
                  <span class="inline-block h-2 w-2 rounded-full" style="background:{{ $status === 'active' ? '#16a34a' : ($status === 'condemn' ? '#f59e0b' : '#ef4444') }}"></span>
                  <span class="capitalize">{{ $status }}</span>
                </button>
              @endforeach
            </div>
          </div>
        </div>
      </x-slot>
      <div class="flex flex-col items-center justify-center gap-4">
        <div class="flex items-center justify-center">
          <!-- Larger donut for better visibility -->
          <div id="assetsDonutChart" aria-label="Assets by status" class="w-56 h-56"></div>
        </div>
        <div class="w-full">
          <ul class="grid grid-cols-1 gap-2 text-sm">
            @foreach(($assetsByStatus ?? []) as $status => $count)
              <li class="flex items-center justify-between px-3 py-1.5 rounded-md border bg-background">
                <div class="flex items-center gap-2">
                  <span class="inline-block h-3 w-3 rounded-full" style="background:{{ $status === 'active' ? '#16a34a' : ($status === 'condemn' ? '#f59e0b' : '#ef4444') }}"></span>
                  <span class="font-medium capitalize">{{ $status }}</span>
                </div>
                <div class="font-mono text-xs">{{ $count }}</div>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    </x-ui-card>
  </div>

  {{-- Summary sections --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <x-ui-card>
      <x-slot name="header">
        <div class="flex items-center gap-2">
          <x-ui.icon name="bar-chart" class="w-5 h-5 text-emerald-500" />
          <div class="text-sm font-medium">Supplies Stock Health</div>
        </div>
      </x-slot>
      <div class="grid grid-cols-3 gap-4 text-center mb-4">
        <div class="p-3 rounded-lg bg-red-50 border border-red-100">
          <div class="text-sm text-red-600 font-medium">Out of Stock</div>
          <div class="text-2xl font-bold text-red-700">{{ number_format($stockOut ?? 0) }}</div>
        </div>
        <div class="p-3 rounded-lg bg-amber-50 border border-amber-100">
          <div class="text-sm text-amber-600 font-medium">Low Stock</div>
          <div class="text-2xl font-bold text-amber-700">{{ number_format($stockLow ?? 0) }}</div>
        </div>
        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-100">
          <div class="text-sm text-emerald-600 font-medium">Healthy</div>
          <div class="text-2xl font-bold text-emerald-700">{{ number_format($stockOk ?? 0) }}</div>
        </div>
      </div>
      @if(!empty($topSupplyCategories))
        <div class="pt-4 border-t">
          <div class="text-sm font-medium text-muted-foreground mb-3">Top supply categories by value</div>
          <ul class="space-y-2">
            @foreach($topSupplyCategories as $c)
              <li class="flex justify-between items-center py-2 px-3 rounded-md bg-muted/30">
                <span class="font-medium">{{ $c->name }}</span>
                <span class="font-mono text-sm">₱{{ number_format($c->v ?? 0, 2) }}</span>
              </li>
            @endforeach
          </ul>
        </div>
      @endif
    </x-ui-card>

    <x-ui-card>
      <x-slot name="header">
        <div class="flex items-center gap-2">
          <x-ui.icon name="activity" class="w-5 h-5 text-blue-500" />
          <div class="text-sm font-medium">Recent Activity</div>
        </div>
      </x-slot>
      @if(!empty($recentActivity) && $recentActivity->count())
        <div class="space-y-3">
          @foreach($recentActivity as $a)
            <div class="flex items-start gap-3 p-3 rounded-lg bg-muted/30 border">
              <div class="w-2 h-2 rounded-full bg-primary mt-2 flex-shrink-0"></div>
              <div class="flex-1 min-w-0">
                <div class="flex justify-between items-start gap-2">
                  <div class="font-medium truncate">{{ $a->description }}</div>
                  <div class="text-xs text-muted-foreground flex-shrink-0">{{ $a->created_at->diffForHumans() }}</div>
                </div>
                <div class="text-xs text-muted-foreground mt-1">
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">
                    {{ $a->action }}
                  </span>
                  on {{ $a->model }} #{{ $a->model_id }}
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="text-center py-8">
          <x-ui.icon name="clock" class="w-8 h-8 text-muted-foreground mx-auto mb-2" />
          <div class="text-sm text-muted-foreground">No recent activity</div>
        </div>
      @endif
    </x-ui-card>
  </div>
</div>
@endif

<script>
  // Initial dashboard payload for the bundled chart module
  window.__dashboard_payload = {!! json_encode([
    'labels' => $monthlyLineLabels ?? $labels ?? [],
    'assetsValues' => $monthlyLineValues ?? $assetsMonthly ?? [],
    'suppliesMonthly' => $suppliesMonthly ?? [],
    'transfersMonthly' => $transfersMonthly ?? [],
    'stockOut' => (int) ($stockOut ?? 0),
    'stockLow' => (int) ($stockLow ?? 0),
    'stockOk' => (int) ($stockOk ?? 0),
    'assetsByStatus' => is_object($assetsByStatus ?? null) ? ($assetsByStatus->toArray()) : ($assetsByStatus ?? []),
  ], JSON_UNESCAPED_UNICODE) !!};

  // Dispatch event once so the charts module (bundled) can initialize immediately
  window.dispatchEvent(new CustomEvent('dashboard:update', { detail: window.__dashboard_payload }));
</script>
