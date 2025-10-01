
@php $role = auth()->user()->role ?? null; @endphp
@if($role === 'supply_officer')
<div class="space-y-6">
  <div>
    <h1 class="text-3xl font-bold tracking-tight">Supply Officer Dashboard</h1>
    <p class="text-muted-foreground">Welcome! Use the sidebar to manage supplies and view your activity.</p>
  </div>
  <x-ui.card class="p-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="rounded-md border bg-card p-4">
        <p class="text-xs text-muted-foreground">Supply SKUs</p>
        <p class="text-2xl font-semibold">{{ number_format($supplySkus ?? 0) }}</p>
      </div>
      <div class="rounded-md border bg-card p-4">
        <p class="text-xs text-muted-foreground">Low stock</p>
        <p class="text-2xl font-semibold">{{ number_format($lowStock ?? 0) }}</p>
      </div>
      <div class="rounded-md border bg-card p-4">
        <p class="text-xs text-muted-foreground">On-hand value</p>
        <p class="text-2xl font-semibold">₱{{ number_format($suppliesValue ?? 0, 2) }}</p>
      </div>
    </div>
  </x-ui.card>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <x-ui-card>
      <h4 class="text-sm font-medium mb-2">Monthly Additions</h4>
      <div id="supply-monthly-line"></div>
    </x-ui-card>
    <x-ui-card>
      <h4 class="text-sm font-medium mb-2">Supplies by Category</h4>
      <div id="supply-categories-bar"></div>
    </x-ui-card>
    <x-ui-card>
      <h4 class="text-sm font-medium mb-2">Stock Health</h4>
      <div id="supply-stock-donut" class="mx-auto" style="max-width:260px"></div>
      <div class="mt-3 grid grid-cols-3 text-center text-xs">
        <div>
          <div class="text-muted-foreground">OK</div>
          <div class="font-semibold text-emerald-600">{{ (int)($stockOk ?? 0) }}</div>
        </div>
        <div>
          <div class="text-muted-foreground">Low</div>
          <div class="font-semibold text-amber-600">{{ (int)($stockLow ?? 0) }}</div>
        </div>
        <div>
          <div class="text-muted-foreground">Out</div>
          <div class="font-semibold text-rose-600">{{ (int)($stockOut ?? 0) }}</div>
        </div>
      </div>
    </x-ui-card>
  </div>
  {{-- Use analytics-lite to drive these charts with the same logic as /supplies/analytics --}}
  @livewire('supplies.supply-analytics-lite')
  
  {{-- Decision widgets --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-4">
    <x-ui-card>
      <div class="flex items-center justify-between mb-2">
        <h4 class="text-sm font-medium">Critical Low Stock</h4>
        <a href="{{ route('supplies.index') }}" class="text-xs text-primary hover:underline">Manage</a>
      </div>
      @if(!empty($lowStockItems) && $lowStockItems->count())
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
            @foreach($lowStockItems as $i)
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
        <div class="mt-3 text-xs text-muted-foreground">Total reorder gap: <span class="font-medium text-foreground">₱{{ number_format(($lowStockValueGap ?? 0), 2) }}</span></div>
      @else
        <div class="text-sm text-muted-foreground">No low-stock items — you're good!</div>
      @endif
    </x-ui-card>

    <x-ui-card>
      <div class="flex items-center justify-between mb-2">
        <h4 class="text-sm font-medium">Stale SKUs (90+ days)</h4>
        <div class="text-xs text-muted-foreground">{{ number_format($staleSkusCount ?? 0) }} items</div>
      </div>
      @if(!empty($staleSkus) && $staleSkus->count())
        <ul class="divide-y">
          @foreach($staleSkus as $s)
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
      @if(!empty($categoryLowCounts) && $categoryLowCounts->count())
        <ul class="text-sm divide-y">
          @foreach($categoryLowCounts as $c)
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
          <div class="text-2xl font-semibold">{{ number_format($totalAssets ?? 0) }}</div>
        </div>
        <div class="text-muted-foreground bg-card p-2 rounded-md">
          <x-ui.icon name="package" class="w-5 h-5" />
        </div>
      </div>
    </x-ui-card>

    <x-ui-card>
      <div class="flex items-center justify-between gap-4">
        <div>
          <div class="text-sm text-muted-foreground">Assets Value</div>
          <div class="text-2xl font-semibold">₱{{ number_format($assetsValue ?? 0, 2) }}</div>
        </div>
        <div class="text-muted-foreground bg-card p-2 rounded-md">
          <x-ui.icon name="credit-card" class="w-5 h-5" />
        </div>
      </div>
    </x-ui-card>

    <x-ui-card>
      <div class="flex items-center justify-between gap-4">
        <div>
          <div class="text-sm text-muted-foreground">Supply SKUs</div>
          <div class="text-2xl font-semibold">{{ number_format($supplySkus ?? 0) }}</div>
        </div>
        <div class="text-muted-foreground bg-card p-2 rounded-md">
          <x-ui.icon name="box" class="w-5 h-5" />
        </div>
      </div>
    </x-ui-card>

    <x-ui-card>
      <div class="flex items-center justify-between gap-4">
        <div>
          <div class="text-sm text-muted-foreground">Supplies On-hand Value</div>
          <div class="text-2xl font-semibold">₱{{ number_format($suppliesValue ?? 0, 2) }}</div>
        </div>
        <div class="text-muted-foreground bg-card p-2 rounded-md">
          <x-ui.icon name="dollar-sign" class="w-5 h-5" />
        </div>
      </div>
    </x-ui-card>
  </div>

  {{-- Charts & summaries (shadcn-like card layout) --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <x-ui-card>
      <div class="flex items-center justify-between mb-2">
        <div>
          <div class="flex items-center gap-2">
            <x-ui.icon name="line-chart" class="w-5 h-5 text-primary" />
            <div class="text-sm font-medium">Assets Created</div>
          </div>
          <div class="text-xs text-muted-foreground">Last 12 months</div>
        </div>
      </div>
      <div class="h-44">
        <canvas id="assetsLineChart" aria-label="Assets created over time"></canvas>
      </div>
    </x-ui-card>

    <x-ui-card>
      <div class="flex items-center justify-between mb-2">
        <div>
          <div class="flex items-center gap-2">
            <x-ui.icon name="bar-chart" class="w-5 h-5 text-amber-500" />
            <div class="text-sm font-medium">Supplies Stock Health</div>
          </div>
          <div class="text-xs text-muted-foreground">Out / Low / Healthy</div>
        </div>
      </div>
      <div class="h-44">
        <canvas id="suppliesBarChart" aria-label="Supplies stock health"></canvas>
      </div>
    </x-ui-card>

    <x-ui-card>
      <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-2">
          <x-ui.icon name="pie-chart" class="w-5 h-5 text-rose-500" />
          <div class="text-sm font-medium">Assets by Status</div>
        </div>
        <div class="flex items-center gap-2">
          <div class="text-xs text-muted-foreground hidden sm:block">Breakdown</div>
          <div id="assetsByStatusToggles" class="flex items-center gap-1">
            @foreach(($assetsByStatus ?? []) as $status => $count)
              <button type="button" data-status="{{ $status }}" aria-pressed="true" class="status-toggle inline-flex items-center gap-2 px-2 py-1 rounded-md border bg-card text-muted-foreground text-xs">
                <span class="inline-block h-2 w-2 rounded-full" style="background:{{ $status === 'active' ? '#16a34a' : ($status === 'condemn' ? '#f59e0b' : '#ef4444') }}"></span>
                <span class="sr-only">{{ ucfirst($status) }}</span>
              </button>
            @endforeach
          </div>
        </div>
      </div>
      <div class="flex flex-col items-center gap-2">
        <div class="flex-shrink-0">
          <!-- Larger donut for better visibility -->
          <canvas id="assetsDonutChart" aria-label="Assets by status" width="204" height="204" style="max-width:204px; max-height:204px;"></canvas>
        </div>
        <div class="w-full mt-2">
          <ul class="grid grid-cols-1 gap-2 text-sm">
            @foreach(($assetsByStatus ?? []) as $status => $count)
              <li class="flex items-center justify-between px-3 py-1 rounded-md border bg-card">
                <div class="flex items-center gap-2">
                  <span class="inline-block h-3 w-3 rounded-full" style="background:{{ $status === 'active' ? '#16a34a' : ($status === 'condemn' ? '#f59e0b' : '#ef4444') }}"></span>
                  <span class="font-medium">{{ ucfirst($status) }}</span>
                </div>
                <div class="font-mono text-xs">{{ $count }}</div>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    </x-ui-card>
  </div>

  {{-- Supplies stock health and recent activity --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <x-ui-card title="Supplies Stock Health">
      <div class="grid grid-cols-3 gap-3 text-center">
        <div>
          <div class="text-sm text-muted-foreground">Out of Stock</div>
          <div class="text-xl font-semibold">{{ number_format($stockOut ?? 0) }}</div>
        </div>
        <div>
          <div class="text-sm text-muted-foreground">Low Stock</div>
          <div class="text-xl font-semibold">{{ number_format($stockLow ?? 0) }}</div>
        </div>
        <div>
          <div class="text-sm text-muted-foreground">Healthy</div>
          <div class="text-xl font-semibold">{{ number_format($stockOk ?? 0) }}</div>
        </div>
      </div>
      @if(!empty($topSupplyCategories))
        <div class="mt-4 text-sm">
          <div class="text-muted-foreground">Top supply categories by value</div>
          <ul class="mt-2">
            @foreach($topSupplyCategories as $c)
              <li class="flex justify-between"><span>{{ $c->name }}</span><span class="font-mono">₱{{ number_format($c->v ?? 0, 2) }}</span></li>
            @endforeach
          </ul>
        </div>
      @endif
    </x-ui-card>

    <x-ui-card title="Recent Activity">
      @if(!empty($recentActivity) && $recentActivity->count())
        <div class="text-sm text-muted-foreground mb-2">Latest activity</div>
        <div class="space-y-2 text-sm">
          @foreach($recentActivity as $a)
            <div class="border rounded p-2">
              <div class="flex justify-between">
                <div class="font-medium">{{ $a->description }}</div>
                <div class="text-xs text-muted-foreground">{{ $a->created_at->diffForHumans() }}</div>
              </div>
              <div class="text-xs text-muted-foreground">{{ $a->action }} on {{ $a->model }} #{{ $a->model_id }}</div>
            </div>
          @endforeach
        </div>
      @else
        <div class="text-sm text-muted-foreground">No recent activity</div>
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
