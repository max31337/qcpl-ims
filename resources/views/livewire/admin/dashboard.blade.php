<div class="space-y-6">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Admin Dashboard</h1>
      <p class="text-muted-foreground">Overview of assets, supplies, and activity</p>
    </div>
  </div>

  {{-- Overview charts --}}
  <x-ui.card class="p-4">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold">Overview charts</h2>
      <x-ui.icon name="layout" />
    </div>
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      {{-- Line chart: Assets created per month (last 12 months) --}}
      <div class="xl:col-span-2">
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

      {{-- Supply stock health donut --}}
      <div>
        <div class="flex items-center justify-between mb-2">
          <h3 class="font-medium">Supply stock health</h3>
          <x-ui.icon name="activity" />
        </div>
        @php
          $sum = max(($stockOut ?? 0) + ($stockLow ?? 0) + ($stockOk ?? 0), 1);
          $pOut = ($stockOut ?? 0) / $sum * 100;
          $pLow = ($stockLow ?? 0) / $sum * 100;
          $pOk  = ($stockOk  ?? 0) / $sum * 100;
          $acc = 0; $segments = [];
          if ($pOut > 0) { $segments[] = '#ef4444 '.$acc.'% '.($acc += $pOut).'%'; }
          if ($pLow > 0) { $segments[] = '#f59e0b '.$acc.'% '.($acc += $pLow).'%'; }
          if ($pOk  > 0) { $segments[] = '#16a34a '.$acc.'% '.($acc += $pOk).'%'; }
          $bg = $segments ? 'conic-gradient('.implode(', ', $segments).')' : '#e5e7eb';
        @endphp
        <div class="flex items-center gap-4">
          <div class="relative h-36 w-36 rounded-full" style="background: {{ $bg }}">
            <div class="absolute inset-4 bg-white rounded-full border"></div>
          </div>
          <div class="text-sm space-y-1">
            <div class="flex items-center gap-2"><span class="inline-block h-2 w-2 rounded-full" style="background:#ef4444"></span> Out: {{ $stockOut ?? 0 }}</div>
            <div class="flex items-center gap-2"><span class="inline-block h-2 w-2 rounded-full" style="background:#f59e0b"></span> Low: {{ $stockLow ?? 0 }}</div>
            <div class="flex items-center gap-2"><span class="inline-block h-2 w-2 rounded-full" style="background:#16a34a"></span> OK: {{ $stockOk ?? 0 }}</div>
          </div>
        </div>
      </div>

      {{-- Assets by category value --}}
      <div>
        <div class="flex items-center justify-between mb-2">
          <h3 class="font-medium">Assets by category value</h3>
          <x-ui.icon name="pie-chart" />
        </div>
        <x-ui.table>
          <thead class="[&_th]:text-left">
            <tr>
              <th class="px-3 py-2">Category</th>
              <th class="px-3 py-2 w-48">Value bar</th>
              <th class="px-3 py-2 w-24 text-right">Value</th>
            </tr>
          </thead>
          <tbody>
            @php
              $valsCat = $assetsByCategoryValue?->pluck('v')->filter(fn($x) => $x > 0)->all() ?? [];
              $maxCat = max($valsCat ?: [1]);
            @endphp
            @forelse($assetsByCategoryValue as $row)
              <tr class="border-t">
                <td class="px-3 py-2 text-sm">{{ $row->name }}</td>
                <td class="px-3 py-2 text-sm">
                  <div class="h-2 w-full bg-muted rounded">
                    <div class="h-2 bg-primary rounded" style="width: {{ $maxCat > 0 ? (($row->v ?? 0) / $maxCat * 100) : 0 }}%"></div>
                  </div>
                </td>
                <td class="px-3 py-2 text-sm text-right">₱{{ number_format($row->v ?? 0, 2) }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="px-3 py-4 text-sm text-muted-foreground">No data</td>
              </tr>
            @endforelse
          </tbody>
        </x-ui.table>
      </div>

      {{-- Assets by branch --}}
      <div>
        <div class="flex items-center justify-between mb-2">
          <h3 class="font-medium">Assets by branch</h3>
          <x-ui.icon name="building-2" />
        </div>
        <x-ui.table>
          <thead class="[&_th]:text-left">
            <tr>
              <th class="px-3 py-2">Branch</th>
              <th class="px-3 py-2 w-48">Count bar</th>
              <th class="px-3 py-2 w-20 text-right">Count</th>
            </tr>
          </thead>
          <tbody>
            @php
              $valsBr = $assetsByBranch?->pluck('c')->filter(fn($x) => $x > 0)->all() ?? [];
              $maxBr = max($valsBr ?: [1]);
            @endphp
            @forelse($assetsByBranch as $row)
              <tr class="border-t">
                <td class="px-3 py-2 text-sm">{{ $row->name }}</td>
                <td class="px-3 py-2 text-sm">
                  <div class="h-2 w-full bg-muted rounded">
                    <div class="h-2 bg-primary rounded" style="width: {{ $maxBr > 0 ? (($row->c ?? 0) / $maxBr * 100) : 0 }}%"></div>
                  </div>
                </td>
                <td class="px-3 py-2 text-sm text-right">{{ number_format($row->c ?? 0) }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="px-3 py-4 text-sm text-muted-foreground">No data</td>
              </tr>
            @endforelse
          </tbody>
        </x-ui.table>
      </div>

      {{-- Assets acquired by year --}}
      <div>
        <div class="flex items-center justify-between mb-2">
          <h3 class="font-medium">Assets acquired by year</h3>
          <x-ui.icon name="calendar" />
        </div>
        @php $maxY = max(($assetsByYear?->pluck('c')->all() ?? [1]) ?: [1]); @endphp
        <div class="space-y-2">
          @forelse($assetsByYear as $row)
            <div>
              <div class="flex items-center justify-between text-xs text-muted-foreground">
                <span>{{ $row->y }}</span>
                <span>{{ $row->c }}</span>
              </div>
              <div class="h-2 w-full bg-muted rounded">
                <div class="h-2 bg-primary rounded" style="width: {{ $row->c / ($maxY ?: 1) * 100 }}%"></div>
              </div>
            </div>
          @empty
            <p class="text-sm text-muted-foreground">No data</p>
          @endforelse
        </div>
      </div>
    </div>
  </x-ui.card>

  {{-- Top supply categories by value --}}
  <x-ui.card class="p-4">
    <div class="flex items-center justify-between mb-2">
      <h2 class="text-lg font-semibold">Top supply categories by value</h2>
      <x-ui.icon name="chevron-right" />
    </div>
    <x-ui.table>
      <thead class="[&_th]:text-left">
        <tr>
          <th class="px-3 py-2">Category</th>
          <th class="px-3 py-2 w-48">Value bar</th>
          <th class="px-3 py-2 w-24 text-right">Value</th>
        </tr>
      </thead>
      <tbody>
        @php
          $vals = $topSupplyCategories?->pluck('v')->filter(fn($x) => $x > 0)->all() ?? [];
          $maxV = max($vals ?: [1]);
        @endphp
        @forelse($topSupplyCategories as $cat)
          <tr class="border-t">
            <td class="px-3 py-2 text-sm">{{ $cat->name }}</td>
            <td class="px-3 py-2 text-sm">
              <div class="h-2 w-full bg-muted rounded">
                <div class="h-2 bg-primary rounded" style="width: {{ $maxV > 0 ? (($cat->v ?? 0) / $maxV * 100) : 0 }}%"></div>
              </div>
            </td>
            <td class="px-3 py-2 text-sm text-right">₱{{ number_format($cat->v ?? 0, 2) }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="px-3 py-4 text-sm text-muted-foreground">No data</td>
          </tr>
        @endforelse
      </tbody>
    </x-ui.table>
  </x-ui.card>

  {{-- Top transfer routes --}}
  <x-ui.card class="p-4">
    <div class="flex items-center justify-between mb-2">
      <h2 class="text-lg font-semibold">Top transfer routes</h2>
      <x-ui.icon name="chevron-right" />
    </div>
    <x-ui.table>
      <thead class="[&_th]:text-left">
        <tr>
          <th class="px-3 py-2">From → To</th>
          <th class="px-3 py-2 w-24 text-right">Count</th>
        </tr>
      </thead>
      <tbody>
        @forelse($topRoutes as $r)
          <tr class="border-t">
            <td class="px-3 py-2 text-sm">{{ $r->origin_name ?? ('Branch #'.$r->origin_branch_id) }} → {{ $r->current_name ?? ('Branch #'.$r->current_branch_id) }}</td>
            <td class="px-3 py-2 text-sm text-right">{{ $r->c }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="2" class="px-3 py-4 text-sm text-muted-foreground">No transfers in range</td>
          </tr>
        @endforelse
      </tbody>
    </x-ui.table>
  </x-ui.card>

  {{-- Recent activity --}}
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
        <x-ui.icon name="file-spreadsheet" />
        <div>
          <p class="font-medium">Asset Reports</p>
          <p class="text-sm text-muted-foreground">Export, print, and analyze</p>
        </div>
      </div>
      <div class="mt-3">
        <a href="{{ route('assets.reports') }}" class="text-sm text-primary hover:underline" wire:navigate>Go to reports</a>
      </div>
    </x-ui.card>

    <x-ui.card class="p-4">
      <div class="flex items-center gap-3">
        <x-ui.icon name="download" />
        <div>
          <p class="font-medium">Supply Reports</p>
          <p class="text-sm text-muted-foreground">CSV exports and summaries</p>
        </div>
      </div>
      <div class="mt-3">
        <a href="{{ route('supplies.reports') }}" class="text-sm text-primary hover:underline" wire:navigate>Go to reports</a>
      </div>
    </x-ui.card>

    <x-ui.card class="p-4">
      <div class="flex items-center gap-3">
        <x-ui.icon name="users" />
        <div>
          <p class="font-medium">Invite / Manage Users</p>
          <p class="text-sm text-muted-foreground">Add staff and set roles</p>
        </div>
      </div>
      <div class="mt-3">
        <a href="{{ route('admin.invitations') }}" class="text-sm text-primary hover:underline" wire:navigate>Open user management</a>
      </div>
    </x-ui.card>
  </div>
</div>
