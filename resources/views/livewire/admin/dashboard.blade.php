
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
    </x-ui-card>
  </div>
  <x-ui.card class="p-4 mt-4">
    <div class="text-sm text-gray-600">For full analytics, go to <a href='{{ route('supplies.analytics') }}' class='text-primary hover:underline'>Supply Analytics</a>.</div>
  </x-ui.card>
  @php
    $___labels = !empty($monthlyLineLabels ?? $labels ?? []) ? ($monthlyLineLabels ?? $labels) : collect(range(11,0))->map(fn($i)=> now()->subMonths($i)->format('M Y'))->all();
    $___adds = !empty($suppliesMonthly ?? []) ? $suppliesMonthly : array_fill(0, is_array($___labels) ? count($___labels) : 12, 0);
    $___cats = ($topSupplyCategories ?? collect())->pluck('name')->values()->all();
    $___catVals = ($topSupplyCategories ?? collect())->pluck('v')->map(fn($x)=> (float) $x)->values()->all();
  @endphp
  <script>
    // Supply officer overview payload sourced from Admin Dashboard aggregates
    (function(){
      try {
        const payload = {
          categories: {!! json_encode($___cats) !!},
          categoryValues: {!! json_encode($___catVals) !!},
          monthlyLabels: {!! json_encode($___labels) !!},
          monthlyAdds: {!! json_encode($___adds) !!},
          stockHealth: { ok: {{ (int)($stockOk ?? 0) }}, low: {{ (int)($stockLow ?? 0) }}, out: {{ (int)($stockOut ?? 0) }} }
        };
        // Store for late-loading modules and dispatch event
        window.__supply_dashboard_payload = payload;
        window.dispatchEvent(new CustomEvent('supplyDashboard:update', { detail: payload }));
      } catch(e) { /* no-op */ }
    })();
  </script>
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
