<div class="space-y-6">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Admin Dashboard</h1>
      <p class="text-muted-foreground">Overview of assets, supplies, and activity</p>
    </div>
  </div>

  {{-- Minimal KPIs (at-a-glance) --}}
  <x-ui.card class="p-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="rounded-md border bg-card p-4">
        <p class="text-xs text-muted-foreground">Assets</p>
        <p class="text-2xl font-semibold">{{ number_format($totalAssets ?? 0) }}</p>
      </div>
      <div class="rounded-md border bg-card p-4">
        <p class="text-xs text-muted-foreground">Asset value</p>
        <p class="text-2xl font-semibold">₱{{ number_format($assetsValue ?? 0, 2) }}</p>
      </div>
      <div class="rounded-md border bg-card p-4">
        <p class="text-xs text-muted-foreground">Supply SKUs</p>
        <p class="text-2xl font-semibold">{{ number_format($supplySkus ?? 0) }}</p>
      </div>
      <div class="rounded-md border bg-card p-4">
        <p class="text-xs text-muted-foreground">Low stock</p>
        <p class="text-2xl font-semibold">{{ number_format($lowStock ?? 0) }}</p>
      </div>
    </div>
  </x-ui.card>

  {{-- Overview charts --}}
  <x-ui.card class="p-4">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold">Overview charts</h2>
      <x-ui.icon name="layout" />
    </div>
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      {{-- Line chart: Assets created per month (last 12 months) --}}
      <div>
        <div class="flex items-center justify-between mb-2">
          <h3 class="font-medium">Assets created (last 12 months)</h3>
          <x-ui.icon name="line-chart" />
        </div>
        @php
          $vals = $monthlyLineValues ?? [];
          $labels = $monthlyLineLabels ?? [];
          $maxV = max($vals ?: [1]);
          $w = 560; $h = 180; $pl = 30; $pr = 10; $pt = 10; $pb = 24;
          $innerW = $w - $pl - $pr; $innerH = $h - $pt - $pb;
          $points = [];
          $n = max(count($vals)-1, 1);
          foreach($vals as $i => $v){
            $x = $pl + ($n ? $innerW * ($i/$n) : 0);
            $y = $pt + ($maxV ? $innerH * (1 - ($v / $maxV)) : $innerH);
            $points[] = $x.','.$y;
          }
        @endphp
        <div class="overflow-x-auto">
          <svg viewBox="0 0 {{ $w }} {{ $h }}" class="w-full max-w-full">
            <rect x="0" y="0" width="{{ $w }}" height="{{ $h }}" fill="transparent" />
            {{-- grid lines --}}
            @for($i=0;$i<=4;$i++)
              @php $gy = $pt + $innerH * ($i/4); @endphp
              <line x1="{{ $pl }}" y1="{{ $gy }}" x2="{{ $w - $pr }}" y2="{{ $gy }}" stroke="#e5e7eb" stroke-width="1" />
            @endfor
            {{-- polyline --}}
            @if(count($points) >= 2)
              <polyline fill="none" stroke="#0ea5e9" stroke-width="2" points="{{ implode(' ', $points) }}" />
            @endif
            {{-- x-axis labels --}}
            @php $tickEvery = max(intval(ceil(count($labels)/6)),1); @endphp
            @foreach($labels as $i => $lab)
              @if($i % $tickEvery === 0)
                @php $x = $pl + ($n ? $innerW * ($i/$n) : 0); @endphp
                <text x="{{ $x }}" y="{{ $h - 6 }}" font-size="10" text-anchor="middle" fill="#6b7280">{{ $lab }}</text>
              @endif
            @endforeach
            {{-- y-axis max label --}}
            <text x="6" y="{{ $pt + 10 }}" font-size="10" fill="#6b7280">max {{ $maxV }}</text>
          </svg>
        </div>
      </div>

      {{-- Supplies added bar chart (last 12 months) --}}
      <div>
        <div class="flex items-center justify-between mb-2">
          <h3 class="font-medium">Supplies added (last 12 months)</h3>
          <x-ui.icon name="bar-chart" />
        </div>
        @php
          $sVals = $suppliesMonthlyValues ?? [];
          $sLabels = $monthlyLineLabels ?? [];
          $sMaxV = max($sVals ?: [1]);
          $bw = 560; $bh = 180; $pl = 30; $pr = 10; $pt = 10; $pb = 24;
          $iW = $bw - $pl - $pr; $iH = $bh - $pt - $pb;
          $count = max(count($sVals), 1);
          $step = $iW / $count;
          $barW = max($step * 0.6, 4);
        @endphp
        <div class="overflow-x-auto">
          <svg viewBox="0 0 {{ $bw }} {{ $bh }}" class="w-full max-w-full">
            <rect x="0" y="0" width="{{ $bw }}" height="{{ $bh }}" fill="transparent" />
            {{-- grid lines --}}
            @for($i=0;$i<=4;$i++)
              @php $gy = $pt + $iH * ($i/4); @endphp
              <line x1="{{ $pl }}" y1="{{ $gy }}" x2="{{ $bw - $pr }}" y2="{{ $gy }}" stroke="#e5e7eb" stroke-width="1" />
            @endfor

            {{-- bars --}}
            @foreach($sVals as $i => $v)
              @php
                $x = $pl + $step * $i + ($step - $barW) / 2;
                $h = $sMaxV ? $iH * ($v / $sMaxV) : 0;
                $y = $pt + ($iH - $h);
              @endphp
              <rect x="{{ $x }}" y="{{ $y }}" width="{{ $barW }}" height="{{ $h }}" fill="#6366f1" rx="2" />
            @endforeach

            {{-- x-axis labels (sparser) --}}
            @php $tickEvery = max(intval(ceil(count($sLabels)/6)),1); @endphp
            @foreach($sLabels as $i => $lab)
              @if($i % $tickEvery === 0)
                @php $tx = $pl + $step * $i + ($step / 2); @endphp
                <text x="{{ $tx }}" y="{{ $bh - 6 }}" font-size="10" text-anchor="middle" fill="#6b7280">{{ $lab }}</text>
              @endif
            @endforeach
          </svg>
        </div>
      </div>

      {{-- Assets by status donut --}}
      <div>
        <div class="flex items-center justify-between mb-2">
          <h3 class="font-medium">Assets by status</h3>
          <x-ui.icon name="info" />
        </div>
        @php
          $statusMap = [ 'active' => 'ok', 'condemn' => 'warn', 'disposed' => 'danger' ];
          $colors = [ 'ok' => '#16a34a', 'warn' => '#f59e0b', 'danger' => '#ef4444' ];
          $totalStatus = max(array_sum($assetsByStatus?->toArray() ?? []), 1);
          $segments = []; $acc = 0;
          foreach($assetsByStatus ?? [] as $status => $count){
            $pct = $count / $totalStatus * 100; $key = $statusMap[$status] ?? 'ok';
            $segments[] = $colors[$key].' '.$acc.'% '.($acc + $pct).'%'; $acc += $pct;
          }
          $bg = $segments ? 'conic-gradient('.implode(', ', $segments).')' : '#e5e7eb';
        @endphp
        <div class="flex items-center gap-4">
          <div class="relative h-36 w-36 rounded-full" style="background: {{ $bg }}">
            <div class="absolute inset-4 bg-white rounded-full border"></div>
          </div>
          <div class="text-sm space-y-1">
            <div class="flex items-center gap-2"><span class="inline-block h-2 w-2 rounded-full" style="background:#16a34a"></span> Active: {{ $assetsByStatus['active'] ?? 0 }}</div>
            <div class="flex items-center gap-2"><span class="inline-block h-2 w-2 rounded-full" style="background:#f59e0b"></span> Condemn: {{ $assetsByStatus['condemn'] ?? 0 }}</div>
            <div class="flex items-center gap-2"><span class="inline-block h-2 w-2 rounded-full" style="background:#ef4444"></span> Disposed: {{ $assetsByStatus['disposed'] ?? 0 }}</div>
          </div>
        </div>
      </div>
    </div>
  </x-ui.card>

  {{-- Recent activity (light overview) --}}
  <x-ui.card class="p-4">
    <div class="flex items-center justify-between mb-2">
      <h2 class="text-lg font-semibold">Recent Activity</h2>
      <x-ui.icon name="refresh-ccw" />
    </div>
    <x-ui.table>
      <thead class="[&_th]:text-left">
        <tr>
          <th class="px-3 py-2">When</th>
          <th class="px-3 py-2">User</th>
          <th class="px-3 py-2">Action</th>
          <th class="px-3 py-2">Model</th>
          <th class="px-3 py-2">Description</th>
        </tr>
      </thead>
      <tbody>
        @forelse($recentActivity as $a)
          <tr class="border-t">
            <td class="px-3 py-2 text-sm">{{ \Carbon\Carbon::parse($a->created_at)->diffForHumans() }}</td>
            <td class="px-3 py-2 text-sm">#{{ $a->user_id }}</td>
            <td class="px-3 py-2 text-sm">{{ $a->action }}</td>
            <td class="px-3 py-2 text-sm">{{ $a->model }} #{{ $a->model_id }}</td>
            <td class="px-3 py-2 text-sm">{{ $a->description }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-3 py-4 text-sm text-muted-foreground">No recent activity</td>
          </tr>
        @endforelse
      </tbody>
    </x-ui.table>
  </x-ui.card>

  {{-- Helpful quick links --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <x-ui.card class="p-4">
      <div class="flex items-center gap-3">
        <x-ui.icon name="line-chart" />
        <div>
          <p class="font-medium">Analytics</p>
          <p class="text-sm text-muted-foreground">Trends and deep insights</p>
        </div>
      </div>
      <div class="mt-3">
        <a href="{{ route('admin.analytics') }}" class="text-sm text-primary hover:underline" wire:navigate>Open analytics</a>
      </div>
    </x-ui.card>
  </div>
</div>
