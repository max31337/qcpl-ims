
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
  <x-ui.card class="p-4 mt-4">
    <div class="text-sm text-gray-600">For full analytics, go to <a href='{{ route('supplies.analytics') }}' class='text-primary hover:underline'>Supply Analytics</a>.</div>
  </x-ui.card>
</div>
@else
<div class="space-y-6">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Admin Dashboard</h1>
      <p class="text-muted-foreground">Overview of assets, supplies, and activity</p>
    </div>
  </div>

  {{-- KPI cards --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <x-ui-card>
      <div class="text-sm text-muted-foreground">Total Assets</div>
      <div class="text-2xl font-semibold">{{ number_format($totalAssets ?? 0) }}</div>
    </x-ui-card>

    <x-ui-card>
      <div class="text-sm text-muted-foreground">Assets Value</div>
      <div class="text-2xl font-semibold">₱{{ number_format($assetsValue ?? 0, 2) }}</div>
    </x-ui-card>

    <x-ui-card>
      <div class="text-sm text-muted-foreground">Supply SKUs</div>
      <div class="text-2xl font-semibold">{{ number_format($supplySkus ?? 0) }}</div>
    </x-ui-card>

    <x-ui-card>
      <div class="text-sm text-muted-foreground">Supplies On-hand Value</div>
      <div class="text-2xl font-semibold">₱{{ number_format($suppliesValue ?? 0, 2) }}</div>
    </x-ui-card>
  </div>

  {{-- Charts & summaries (simple fallback if JS charts not present) --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <x-ui-card title="Assets Created (last 12 months)">
      @if(!empty($monthlyLineLabels) && !empty($monthlyLineValues))
        <div class="text-sm text-muted-foreground mb-2">Monthly assets created</div>
        <div class="text-xs text-muted-foreground">
          {{-- Basic textual representation when charts are not available --}}
          @foreach($monthlyLineLabels as $i => $label)
            <div class="flex justify-between border-b py-1">
              <div>{{ $label }}</div>
              <div class="font-mono">{{ $monthlyLineValues[$i] ?? 0 }}</div>
            </div>
          @endforeach
        </div>
      @else
        <div class="text-sm text-muted-foreground">No data</div>
      @endif
    </x-ui-card>

    <x-ui-card title="Supplies Monthly Adds">
      @if(!empty($suppliesMonthlyValues))
        <div class="text-sm text-muted-foreground">Supplies added per month</div>
        <div class="mt-2 text-xs">
          @foreach($suppliesMonthlyValues as $i => $val)
            <div class="flex justify-between border-b py-1"><div>{{ $monthlyLineLabels[$i] ?? '—' }}</div><div class="font-mono">{{ $val }}</div></div>
          @endforeach
        </div>
      @else
        <div class="text-sm text-muted-foreground">No data</div>
      @endif
    </x-ui-card>

    <x-ui-card title="Assets by Status">
      @if(!empty($assetsByStatus))
        <div class="text-sm text-muted-foreground">Status breakdown</div>
        <ul class="mt-2 text-sm">
          @foreach($assetsByStatus as $status => $count)
            <li class="flex justify-between"><span>{{ ucfirst($status) }}</span><span class="font-mono">{{ $count }}</span></li>
          @endforeach
        </ul>
      @else
        <div class="text-sm text-muted-foreground">No data</div>
      @endif
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
