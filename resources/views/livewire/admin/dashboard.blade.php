<div class="space-y-6">
  {{-- Header + Quick actions --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Admin Dashboard</h1>
      <p class="text-muted-foreground">Overview of assets, supplies, and activity</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('assets.index') }}" wire:navigate>
        <x-ui.button>
          <x-ui.icon name="boxes" class="h-4 w-4" />
          Manage Assets
        </x-ui.button>
      </a>
      <a href="{{ route('supplies.index') }}" wire:navigate>
        <x-ui.button variant="secondary">
          <x-ui.icon name="package" class="h-4 w-4" />
          Manage Supplies
        </x-ui.button>
      </a>
      <a href="{{ route('admin.invitations') }}" wire:navigate>
        <x-ui.button variant="outline">
          <x-ui.icon name="users" class="h-4 w-4" />
          Manage Users
        </x-ui.button>
      </a>
    </div>
  </div>

  {{-- KPI tiles --}}
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <x-ui.card class="p-4">
      <p class="text-sm text-muted-foreground">Total Assets</p>
      <p class="mt-2 text-2xl font-bold">{{ number_format($totalAssets) }}</p>
    </x-ui.card>
    <x-ui.card class="p-4">
      <p class="text-sm text-muted-foreground">Assets Value</p>
      <p class="mt-2 text-2xl font-bold">₱{{ number_format($assetsValue, 2) }}</p>
    </x-ui.card>
    <x-ui.card class="p-4">
      <p class="text-sm text-muted-foreground">Supply SKUs</p>
      <p class="mt-2 text-2xl font-bold">{{ number_format($supplySkus) }}</p>
    </x-ui.card>
    <x-ui.card class="p-4">
      <p class="text-sm text-muted-foreground">Low-stock Supplies</p>
      <p class="mt-2 text-2xl font-bold">{{ number_format($lowStock) }}</p>
    </x-ui.card>
  </div>

  {{-- Charts and tables --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <x-ui.card class="p-4">
      <div class="flex items-center justify-between mb-2">
        <h2 class="text-lg font-semibold">Assets created (last months)</h2>
        <x-ui.icon name="bar-chart" />
      </div>
      <div class="mt-4">
        {{-- Simple bar representation using Tailwind since no JS charts --}}
        @php
          $max = max($monthlyAssets->pluck('c')->all() ?: [1]);
        @endphp
        <div class="space-y-2">
          @foreach($monthlyAssets as $row)
            <div>
              <div class="flex items-center justify-between text-xs text-muted-foreground">
                <span>{{ $row->m }}</span>
                <span>{{ $row->c }}</span>
              </div>
              <div class="h-2 w-full bg-muted rounded">
                <div class="h-2 bg-primary rounded" style="width: {{ $row->c / $max * 100 }}%"></div>
              </div>
            </div>
          @endforeach
          @if($monthlyAssets->isEmpty())
            <p class="text-sm text-muted-foreground">No data</p>
          @endif
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-4">
      <div class="flex items-center justify-between mb-2">
        <h2 class="text-lg font-semibold">Assets by status</h2>
        <x-ui.icon name="info" />
      </div>
      {{-- Donut: build with conic-gradient based on counts --}}
      @php
        $statusMap = [
          'active' => 'ok',
          'condemn' => 'warn',
          'disposed' => 'danger',
        ];
        $colors = [
          'ok' => '#16a34a',
          'warn' => '#f59e0b',
          'danger' => '#ef4444',
        ];
        $totalStatus = max(array_sum($assetsByStatus?->toArray() ?? []), 1);
        $segments = [];
        $acc = 0;
        foreach($assetsByStatus ?? [] as $status => $count){
          $pct = $count / $totalStatus * 100;
          $key = $statusMap[$status] ?? 'ok';
          $segments[] = $colors[$key].' '.$acc.'% '.($acc + $pct).'%';
          $acc += $pct;
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
    </x-ui.card>

    <x-ui.card class="p-4 lg:col-span-2">
      <div class="flex items-center justify-between mb-2">
        <h2 class="text-lg font-semibold">Supply stock health</h2>
        <x-ui.icon name="activity" />
      </div>
      @php
        $sum = max(($stockOut ?? 0) + ($stockLow ?? 0) + ($stockOk ?? 0), 1);
        $pOut = ($stockOut ?? 0) / $sum * 100;
        $pLow = ($stockLow ?? 0) / $sum * 100;
        $pOk  = ($stockOk  ?? 0) / $sum * 100;
      @endphp
      <div class="w-full h-3 rounded bg-muted overflow-hidden">
        <div class="h-3 bg-red-500" style="width: {{ $pOut }}%"></div>
        <div class="h-3 bg-yellow-500" style="width: {{ $pLow }}%"></div>
        <div class="h-3 bg-green-600" style="width: {{ $pOk }}%"></div>
      </div>
      <div class="mt-2 flex gap-4 text-sm text-muted-foreground">
        <div>Out: {{ $stockOut ?? 0 }}</div>
        <div>Low: {{ $stockLow ?? 0 }}</div>
        <div>OK: {{ $stockOk ?? 0 }}</div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-4 lg:col-span-2">
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
            $maxV = max(($topSupplyCategories?->pluck('v')->all() ?? [1]));
          @endphp
          @forelse($topSupplyCategories as $cat)
            <tr class="border-t">
              <td class="px-3 py-2 text-sm">{{ $cat->name }}</td>
              <td class="px-3 py-2 text-sm">
                <div class="h-2 w-full bg-muted rounded">
                  <div class="h-2 bg-primary rounded" style="width: {{ ($cat->v ?? 0) / $maxV * 100 }}%"></div>
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
  </div>

  {{-- Transfers table moved below as a separate card --}}
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
            <td class="px-3 py-2 text-sm">Branch #{{ $r->origin_branch_id }} → Branch #{{ $r->current_branch_id }}</td>
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
