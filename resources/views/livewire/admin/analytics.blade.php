<div class="space-y-6">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Admin Analytics</h1>
      <p class="text-muted-foreground">Deep dive into assets, supplies, and transfer insights across all branches</p>
    </div>
    <div class="flex items-center gap-2">
      <button class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm hover:bg-accent">
        <x-ui.icon name="refresh-ccw" class="h-4 w-4" />
        <span>Refresh</span>
      </button>
      <button class="inline-flex items-center gap-2 rounded-md bg-primary text-primary-foreground px-3 py-2 text-sm hover:bg-primary/90">
        <x-ui.icon name="download" class="h-4 w-4" />
        <span>Export</span>
      </button>
    </div>
  </div>

  {{-- Enhanced Filters --}}
  <x-ui.card class="p-8">
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <div class="h-12 w-12 rounded-full bg-gradient-to-r from-indigo-100 to-purple-100 flex items-center justify-center">
          <x-ui.icon name="filter" class="w-6 h-6 text-indigo-600" />
        </div>
        <div>
          <h4 class="text-xl font-semibold">Analytics Filters</h4>
          <p class="text-sm text-muted-foreground">Customize your analytics view</p>
        </div>
      </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div>
        <x-ui.label class="text-base font-semibold mb-3 flex items-center gap-2">
          <x-ui.icon name="calendar" class="w-4 h-4 text-blue-600" />
          View Period
        </x-ui.label>
        <select class="mt-2 flex h-12 w-full rounded-lg border-2 border-input bg-background px-4 py-3 text-base ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 transition-all" wire:model.live="period">
          <option value="alltime">📊 All Time</option>
          <option value="monthly">📅 Monthly View</option>
          <option value="yearly">🗓️ Yearly View</option>
        </select>
      </div>
      <div x-show="$wire.period !== 'alltime'">
        <x-ui.label class="text-base font-semibold mb-3 flex items-center gap-2">
          <x-ui.icon name="calendar-days" class="w-4 h-4 text-green-600" />
          Year
        </x-ui.label>
        <select class="mt-2 flex h-12 w-full rounded-lg border-2 border-input bg-background px-4 py-3 text-base ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2 transition-all" wire:model.live="selectedYear">
          @for($year = now()->year; $year >= now()->year - 5; $year--)
            <option value="{{ $year }}">{{ $year }}</option>
          @endfor
        </select>
      </div>
    </div>
  </x-ui.card>

  {{-- Enhanced KPI Cards --}}
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Total Assets</p>
          <p class="text-3xl font-bold text-blue-600">{{ number_format($kpis['assetsTotal'] ?? 0) }}</p>
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
          <p class="text-sm text-muted-foreground">Asset Value</p>
          <p class="text-3xl font-bold text-green-600">₱{{ number_format($kpis['assetsValue'] ?? 0,2) }}</p>
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
          <p class="text-3xl font-bold text-purple-600">{{ number_format($kpis['suppliesSkus'] ?? 0) }}</p>
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
          <p class="text-3xl font-bold text-emerald-600">₱{{ number_format($kpis['suppliesValue'] ?? 0,2) }}</p>
          <p class="text-xs text-muted-foreground mt-1">Current stock valuation</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center">
          <x-ui.icon name="dollar-sign" class="h-6 w-6 text-emerald-600" />
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Asset Transfers</p>
          <p class="text-3xl font-bold text-orange-600">{{ number_format($kpis['transfersInRange'] ?? 0) }}</p>
          <p class="text-xs text-muted-foreground mt-1">Location movements</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-orange-100 flex items-center justify-center">
          <x-ui.icon name="shuffle" class="h-6 w-6 text-orange-600" />
        </div>
      </div>
    </x-ui.card>
  </div>

  {{-- Enhanced Analytics Charts Section --}}
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold">Monthly Activity Trends</h2>
      <span class="text-sm text-muted-foreground">Last 12 months performance</span>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <x-ui-card class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h4 class="text-lg font-medium">Assets Created</h4>
          <x-ui.icon name="line-chart" class="h-5 w-5 text-muted-foreground" />
        </div>
        <div id="assetsAnalyticsLine" style="min-height: 250px;"></div>
        <p class="text-xs text-muted-foreground mt-2">New assets registered over time</p>
      </x-ui-card>

      <x-ui-card class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h4 class="text-lg font-medium">Supplies Added</h4>
          <x-ui.icon name="bar-chart" class="h-5 w-5 text-muted-foreground" />
        </div>
        <div id="suppliesAnalyticsBar" style="min-height: 250px;"></div>
        <p class="text-xs text-muted-foreground mt-2">New supplies added to inventory</p>
      </x-ui-card>

      <x-ui-card class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h4 class="text-lg font-medium">Asset Transfers</h4>
          <x-ui.icon name="shuffle" class="h-5 w-5 text-muted-foreground" />
        </div>
        <div id="transfersAnalyticsBar" style="min-height: 250px;"></div>
        <p class="text-xs text-muted-foreground mt-2">Asset location movements</p>
      </x-ui-card>
    </div>
  </div>

  {{-- Beautiful Distribution Charts --}}
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold">Distribution Analysis</h2>
      <span class="text-sm text-muted-foreground">Current status overview</span>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <x-ui-card class="p-6">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-100">
              <x-ui.icon name="pie-chart" class="w-5 h-5 text-green-600" />
            </div>
            <h3 class="text-lg font-semibold">Assets by Status</h3>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
          <div class="flex justify-center">
            <div id="assetsStatusDonut" class="w-52 h-52"></div>
          </div>
          
          <div class="space-y-4">
            <div class="flex items-center justify-between p-3 rounded-lg bg-green-50 border border-green-200">
              <div class="flex items-center gap-3">
                <span class="inline-block h-4 w-4 rounded-full bg-green-500"></span>
                <span class="font-medium">Active</span>
              </div>
              <span class="text-lg font-bold text-green-700">{{ number_format($assetsByStatus['active'] ?? 0) }}</span>
            </div>
            <div class="flex items-center justify-between p-3 rounded-lg bg-amber-50 border border-amber-200">
              <div class="flex items-center gap-3">
                <span class="inline-block h-4 w-4 rounded-full bg-amber-500"></span>
                <span class="font-medium">Condemn</span>
              </div>
              <span class="text-lg font-bold text-amber-700">{{ number_format($assetsByStatus['condemn'] ?? 0) }}</span>
            </div>
            <div class="flex items-center justify-between p-3 rounded-lg bg-red-50 border border-red-200">
              <div class="flex items-center gap-3">
                <span class="inline-block h-4 w-4 rounded-full bg-red-500"></span>
                <span class="font-medium">Disposed</span>
              </div>
              <span class="text-lg font-bold text-red-700">{{ number_format($assetsByStatus['disposed'] ?? 0) }}</span>
            </div>
          </div>
        </div>
      </x-ui-card>

      <x-ui-card class="p-6">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-100">
              <x-ui.icon name="package" class="w-5 h-5 text-blue-600" />
            </div>
            <h3 class="text-lg font-semibold">Supply Stock Health</h3>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
          <div class="flex justify-center">
            <div id="stockHealthDonut" class="w-52 h-52"></div>
          </div>
          
          <div class="space-y-4">
            <div class="flex items-center justify-between p-3 rounded-lg bg-red-50 border border-red-200">
              <div class="flex items-center gap-3">
                <span class="inline-block h-4 w-4 rounded-full bg-red-500"></span>
                <span class="font-medium">Out of Stock</span>
              </div>
              <span class="text-lg font-bold text-red-700">{{ number_format($stockOut ?? 0) }}</span>
            </div>
            <div class="flex items-center justify-between p-3 rounded-lg bg-amber-50 border border-amber-200">
              <div class="flex items-center gap-3">
                <span class="inline-block h-4 w-4 rounded-full bg-amber-500"></span>
                <span class="font-medium">Low Stock</span>
              </div>
              <span class="text-lg font-bold text-amber-700">{{ number_format($stockLow ?? 0) }}</span>
            </div>
            <div class="flex items-center justify-between p-3 rounded-lg bg-green-50 border border-green-200">
              <div class="flex items-center gap-3">
                <span class="inline-block h-4 w-4 rounded-full bg-green-500"></span>
                <span class="font-medium">Healthy</span>
              </div>
              <span class="text-lg font-bold text-green-700">{{ number_format($stockOk ?? 0) }}</span>
            </div>
          </div>
        </div>
      </x-ui-card>
    </div>
  </div>

    {{-- Beautiful Branch Rankings --}}
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold">Branch Performance</h2>
        <span class="text-sm text-muted-foreground">Asset distribution across branches</span>
      </div>
      
      <x-ui-card class="p-6">
        <div class="flex items-center gap-3 mb-6">
          <div class="flex items-center justify-center w-10 h-10 rounded-full bg-purple-100">
            <x-ui.icon name="building" class="w-5 h-5 text-purple-600" />
          </div>
          <h3 class="text-lg font-semibold">Top Branches by Asset Count</h3>
        </div>
        
        <div class="space-y-4">
          @php $vals=$assetsCountByBranch?->pluck('c')->all() ?? []; $mx=max($vals ?: [1]); @endphp
          @forelse($assetsCountByBranch as $index => $r)
            <div class="p-4 rounded-lg border {{ $index === 0 ? 'bg-gradient-to-r from-purple-50 to-blue-50 border-purple-200' : ($index === 1 ? 'bg-gradient-to-r from-blue-50 to-green-50 border-blue-200' : ($index === 2 ? 'bg-gradient-to-r from-green-50 to-emerald-50 border-green-200' : 'bg-gray-50 border-gray-200')) }}">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-4 flex-1">
                  <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $index === 0 ? 'bg-purple-600 text-white' : ($index === 1 ? 'bg-blue-600 text-white' : ($index === 2 ? 'bg-green-600 text-white' : 'bg-gray-600 text-white')) }} text-sm font-bold">
                    {{ $index + 1 }}
                  </div>
                  <div class="flex-1">
                    <div class="font-semibold {{ $index === 0 ? 'text-purple-900' : ($index === 1 ? 'text-blue-900' : ($index === 2 ? 'text-green-900' : 'text-gray-900')) }}">{{ $r->name }}</div>
                    <div class="w-full bg-white/70 rounded-full h-3 mt-2 overflow-hidden">
                      <div class="h-3 {{ $index === 0 ? 'bg-gradient-to-r from-purple-500 to-blue-500' : ($index === 1 ? 'bg-gradient-to-r from-blue-500 to-green-500' : ($index === 2 ? 'bg-gradient-to-r from-green-500 to-emerald-500' : 'bg-gray-500')) }} rounded-full transition-all duration-500" style="width: {{ $mx>0 ? (($r->c ?? 0)/$mx*100) : 0 }}%"></div>
                    </div>
                  </div>
                </div>
                <div class="text-right ml-4">
                  <div class="text-2xl font-bold {{ $index === 0 ? 'text-purple-700' : ($index === 1 ? 'text-blue-700' : ($index === 2 ? 'text-green-700' : 'text-gray-700')) }}">{{ number_format($r->c ?? 0) }}</div>
                  <div class="text-xs text-muted-foreground uppercase tracking-wide">assets</div>
                </div>
              </div>
            </div>
          @empty
            <div class="text-center py-16">
              <div class="flex items-center justify-center w-16 h-16 rounded-full bg-purple-100 mx-auto mb-4">
                <x-ui.icon name="building" class="w-8 h-8 text-purple-400" />
              </div>
              <p class="text-muted-foreground">No branch data available</p>
            </div>
          @endforelse
        </div>
      </x-ui-card>
    </div>

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

    {{-- Beautiful Category Distribution Charts --}}
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold">Category Analysis</h2>
        <span class="text-sm text-muted-foreground">Value distribution by category</span>
      </div>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-ui-card class="p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-100">
                <x-ui.icon name="pie-chart" class="w-5 h-5 text-blue-600" />
              </div>
              <h3 class="text-lg font-semibold">Assets by Category</h3>
            </div>
          </div>
          
          <div class="space-y-3">
            <div class="flex justify-center">
              <div id="assetsCategoryPie" class="w-48 h-48" style="min-height: 192px; max-height: 192px;"></div>
            </div>
            
            <div>
              <h4 class="font-medium text-sm text-muted-foreground uppercase tracking-wide mb-3">Value Breakdown</h4>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @php $palette=['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#84cc16','#f97316','#14b8a6','#eab308']; @endphp
                @foreach($assetsValueByCategory as $i=>$r)
                  @php $color=$palette[$i % count($palette)]; @endphp
                  <div class="flex items-center justify-between p-3 rounded-lg border" style="background-color: {{ $color }}15; border-color: {{ $color }}40;">
                    <div class="flex items-center gap-3">
                      <span class="inline-block h-4 w-4 rounded-full flex-shrink-0" style="background: {{ $color }}"></span>
                      <span class="font-medium text-sm">{{ $r->name }}</span>
                    </div>
                    <span class="text-sm font-bold" style="color: {{ $color }}">₱{{ number_format($r->v ?? 0,2) }}</span>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        </x-ui-card>

        <x-ui-card class="p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="flex items-center justify-center w-10 h-10 rounded-full bg-emerald-100">
                <x-ui.icon name="pie-chart" class="w-5 h-5 text-emerald-600" />
              </div>
              <h3 class="text-lg font-semibold">Supplies by Category</h3>
            </div>
          </div>
          
          <div class="space-y-3">
            <div class="flex justify-center">
              <div id="suppliesCategoryPie" class="w-48 h-48" style="min-height: 192px; max-height: 192px;"></div>
            </div>
            
            <div>
              <h4 class="font-medium text-sm text-muted-foreground uppercase tracking-wide mb-3">Value Breakdown</h4>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @php $sPalette=['#10b981','#3b82f6','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#84cc16','#f97316','#14b8a6','#eab308']; @endphp
                @foreach($suppliesValueByCategory as $i=>$r)
                  @php $color=$sPalette[$i % count($sPalette)]; @endphp
                  <div class="flex items-center justify-between p-3 rounded-lg border" style="background-color: {{ $color }}15; border-color: {{ $color }}40;">
                    <div class="flex items-center gap-3">
                      <span class="inline-block h-4 w-4 rounded-full flex-shrink-0" style="background: {{ $color }}"></span>
                      <span class="font-medium text-sm">{{ $r->name }}</span>
                    </div>
                    <span class="text-sm font-bold" style="color: {{ $color }}">₱{{ number_format($r->v ?? 0,2) }}</span>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        </x-ui-card>
      </div>
    </div>

    {{-- Beautiful Analytics Tables --}}
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold">Detailed Analytics</h2>
        <span class="text-sm text-muted-foreground">Additional insights and trends</span>
      </div>
      
      <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <x-ui-card class="p-6">
          <div class="flex items-center gap-3 mb-6">
            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100">
              <x-ui.icon name="bar-chart" class="w-5 h-5 text-indigo-600" />
            </div>
            <h3 class="text-lg font-semibold">Asset Value by Category</h3>
          </div>
          
          <div class="space-y-4">
            @php $vals=$assetsValueByCategory?->pluck('v')->all() ?? []; $mx=max($vals ?: [1]); @endphp
            @forelse($assetsValueByCategory as $index => $r)
              <div class="p-4 rounded-lg border {{ $index % 3 === 0 ? 'bg-indigo-50 border-indigo-200' : ($index % 3 === 1 ? 'bg-purple-50 border-purple-200' : 'bg-blue-50 border-blue-200') }}">
                <div class="flex items-center justify-between mb-3">
                  <div class="font-semibold {{ $index % 3 === 0 ? 'text-indigo-900' : ($index % 3 === 1 ? 'text-purple-900' : 'text-blue-900') }}">{{ $r->name }}</div>
                  <div class="text-xl font-bold {{ $index % 3 === 0 ? 'text-indigo-700' : ($index % 3 === 1 ? 'text-purple-700' : 'text-blue-700') }}">₱{{ number_format($r->v ?? 0, 2) }}</div>
                </div>
                <div class="w-full bg-white/70 rounded-full h-3 overflow-hidden">
                  <div class="h-3 {{ $index % 3 === 0 ? 'bg-gradient-to-r from-indigo-500 to-indigo-600' : ($index % 3 === 1 ? 'bg-gradient-to-r from-purple-500 to-purple-600' : 'bg-gradient-to-r from-blue-500 to-blue-600') }} rounded-full transition-all duration-500" style="width: {{ $mx>0 ? (($r->v ?? 0)/$mx*100) : 0 }}%"></div>
                </div>
              </div>
            @empty
              <div class="text-center py-16">
                <div class="flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 mx-auto mb-4">
                  <x-ui.icon name="bar-chart" class="w-8 h-8 text-indigo-400" />
                </div>
                <p class="text-muted-foreground">No category data available</p>
              </div>
            @endforelse
          </div>
        </x-ui-card>

        <x-ui-card class="p-6">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
              <div class="flex items-center justify-center w-10 h-10 rounded-full bg-cyan-100">
                <x-ui.icon name="shuffle" class="w-5 h-5 text-cyan-600" />
              </div>
              <h3 class="text-lg font-semibold">Top Transfer Routes</h3>
            </div>
            <a href="{{ route('admin.transfer-histories') }}" 
               class="inline-flex items-center gap-2 px-3 py-1.5 text-sm bg-cyan-50 text-cyan-700 border border-cyan-200 rounded-lg hover:bg-cyan-100 transition-colors"
               title="View all transfer histories">
              <span>View All</span>
              <x-ui.icon name="arrow-right" class="h-4 w-4" />
            </a>
          </div>
          
          <div class="space-y-4">
            @forelse($topRoutes as $index => $r)
              <div class="p-4 rounded-lg border {{ $index % 3 === 0 ? 'bg-cyan-50 border-cyan-200' : ($index % 3 === 1 ? 'bg-teal-50 border-teal-200' : 'bg-blue-50 border-blue-200') }}">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3 flex-1">
                    <div class="flex items-center justify-center w-6 h-6 rounded-full {{ $index % 3 === 0 ? 'bg-cyan-600' : ($index % 3 === 1 ? 'bg-teal-600' : 'bg-blue-600') }} text-white text-xs font-bold">
                      {{ $index + 1 }}
                    </div>
                    <div class="flex items-center gap-2 font-medium {{ $index % 3 === 0 ? 'text-cyan-900' : ($index % 3 === 1 ? 'text-teal-900' : 'text-blue-900') }}">
                      <span class="truncate">{{ $r->origin_name ?? ('Branch #'.$r->origin_branch_id) }}</span>
                      <x-ui.icon name="arrow-right" class="w-4 h-4 text-muted-foreground flex-shrink-0" />
                      <span class="truncate">{{ $r->current_name ?? ('Branch #'.$r->current_branch_id) }}</span>
                    </div>
                  </div>
                  <div class="text-right ml-4">
                    <div class="text-xl font-bold {{ $index % 3 === 0 ? 'text-cyan-700' : ($index % 3 === 1 ? 'text-teal-700' : 'text-blue-700') }}">{{ $r->c }}</div>
                    <div class="text-xs text-muted-foreground uppercase tracking-wide">transfers</div>
                  </div>
                </div>
              </div>
            @empty
              <div class="text-center py-16">
                <div class="flex items-center justify-center w-16 h-16 rounded-full bg-cyan-100 mx-auto mb-4">
                  <x-ui.icon name="shuffle" class="w-8 h-8 text-cyan-400" />
                </div>
                <p class="text-muted-foreground">No transfer data available</p>
              </div>
            @endforelse
          </div>
        </x-ui-card>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>
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
      return ['name' => $item->name, 'v' => floatval($item->v ?? 0)];
    })->toArray(),
    'suppliesValueByCategory' => ($suppliesValueByCategory ?? collect())->map(function($item) {
      return ['name' => $item->name, 'v' => floatval($item->v ?? 0)];
    })->toArray(),
  ], JSON_UNESCAPED_UNICODE) !!};

  let chartInstances = {};

  // Pie chart function (unchanged)
  function createPieChart(elementId, data, colors) {
    const element = document.getElementById(elementId);
    if (!element || !data || data.length === 0) return null;
    if (chartInstances[elementId]) {
      chartInstances[elementId].destroy();
      delete chartInstances[elementId];
    }
    element.innerHTML = '';
    const labels = data.map(item => item.name);
    const values = data.map(item => item.v);
    const options = {
      chart: { type: 'pie', height: '100%', width: '100%', toolbar: { show: false }, animations: { enabled: true, easing: 'easeinout', speed: 800 } },
      series: values,
      labels: labels,
      colors: colors,
      legend: { show: false },
      dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 'bold', colors: ['#fff'] }, formatter: function(val, opts) { return opts.w.config.labels[opts.seriesIndex]; } },
      tooltip: { enabled: true, theme: 'dark', y: { formatter: function(value) { return '₱' + new Intl.NumberFormat().format(value); } } },
      stroke: { width: 2, colors: ['#ffffff'] },
      plotOptions: { pie: { donut: { size: '0%' }, expandOnClick: false } },
      responsive: [{ breakpoint: 480, options: { chart: { width: '100%' } } }]
    };
    chartInstances[elementId] = new ApexCharts(element, options);
    chartInstances[elementId].render();
    return chartInstances[elementId];
  }

  // Bar/line chart initialization (improved)
  function initializeBarLineCharts() {
    // Assets Created (Line)
    const lineEl = document.getElementById('assetsAnalyticsLine');
    let assetData = Array.isArray(window.__analytics_payload.assetsMonthly) ? window.__analytics_payload.assetsMonthly : [];
    let assetLabels = Array.isArray(window.__analytics_payload.labels) ? window.__analytics_payload.labels : [];
    if (lineEl) {
      if (chartInstances['assetsAnalyticsLine']) {
        chartInstances['assetsAnalyticsLine'].destroy();
        delete chartInstances['assetsAnalyticsLine'];
      }
      // Fallback: if no data, show zeroes for each label
      if (!assetData || assetData.length === 0) {
        assetData = assetLabels.map(() => 0);
      }
      chartInstances['assetsAnalyticsLine'] = new ApexCharts(lineEl, {
        chart: { type: 'line', height: 250, toolbar: { show: false } },
        series: [{ name: 'Assets Created', data: assetData }],
        xaxis: { categories: assetLabels },
        colors: ['#3b82f6'],
        stroke: { width: 3 },
        dataLabels: { enabled: false },
        grid: { borderColor: '#e5e7eb' },
        tooltip: { y: { formatter: v => v } }
      });
      chartInstances['assetsAnalyticsLine'].render();
    }
    // Supplies Added (Bar)
    const suppliesEl = document.getElementById('suppliesAnalyticsBar');
    let suppliesData = Array.isArray(window.__analytics_payload.suppliesMonthly) ? window.__analytics_payload.suppliesMonthly : [];
    if (suppliesEl) {
      if (chartInstances['suppliesAnalyticsBar']) {
        chartInstances['suppliesAnalyticsBar'].destroy();
        delete chartInstances['suppliesAnalyticsBar'];
      }
      if (!suppliesData || suppliesData.length === 0) {
        suppliesData = assetLabels.map(() => 0);
      }
      chartInstances['suppliesAnalyticsBar'] = new ApexCharts(suppliesEl, {
        chart: { type: 'bar', height: 250, toolbar: { show: false } },
        series: [{ name: 'Supplies Added', data: suppliesData }],
        xaxis: { categories: assetLabels },
        colors: ['#a21caf'],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
        dataLabels: { enabled: false },
        grid: { borderColor: '#e5e7eb' },
        tooltip: { y: { formatter: v => v } }
      });
      chartInstances['suppliesAnalyticsBar'].render();
    }
    // Asset Transfers (Bar)
    const transfersEl = document.getElementById('transfersAnalyticsBar');
    let transfersData = Array.isArray(window.__analytics_payload.transfersMonthly) ? window.__analytics_payload.transfersMonthly : [];
    if (transfersEl) {
      if (chartInstances['transfersAnalyticsBar']) {
        chartInstances['transfersAnalyticsBar'].destroy();
        delete chartInstances['transfersAnalyticsBar'];
      }
      if (!transfersData || transfersData.length === 0) {
        transfersData = assetLabels.map(() => 0);
      }
      chartInstances['transfersAnalyticsBar'] = new ApexCharts(transfersEl, {
        chart: { type: 'bar', height: 250, toolbar: { show: false } },
        series: [{ name: 'Asset Transfers', data: transfersData }],
        xaxis: { categories: assetLabels },
        colors: ['#f59e42'],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
        dataLabels: { enabled: false },
        grid: { borderColor: '#e5e7eb' },
        tooltip: { y: { formatter: v => v } }
      });
      chartInstances['transfersAnalyticsBar'].render();
    }
  }

  // Listen for Livewire event to force chart refresh after asset creation
  if (window.Livewire) {
    window.Livewire.on && window.Livewire.on('assetCreated', () => {
      setTimeout(initializeAnalyticsCharts, 200);
    });
  }

  // Pie chart initialization (unchanged)
  function initializePieCharts() {
    // Assets Category Pie Chart
    const assetsData = window.__analytics_payload.assetsValueByCategory;
    const assetsColors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#84cc16','#f97316','#14b8a6','#eab308'];
    if (assetsData && assetsData.length > 0) createPieChart('assetsCategoryPie', assetsData, assetsColors);
    // Supplies Category Pie Chart
    const suppliesData = window.__analytics_payload.suppliesValueByCategory;
    const suppliesColors = ['#10b981','#3b82f6','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#84cc16','#f97316','#14b8a6','#eab308'];
    if (suppliesData && suppliesData.length > 0) createPieChart('suppliesCategoryPie', suppliesData, suppliesColors);
  }

  // Main chart initialization (calls all)
  function initializeAnalyticsCharts(retries = 5) {
    // Wait for all chart containers
    const containers = [
      'assetsAnalyticsLine',
      'suppliesAnalyticsBar',
      'transfersAnalyticsBar',
      'assetsCategoryPie',
      'suppliesCategoryPie'
    ];
    let allReady = containers.every(id => document.getElementById(id));
    if (!allReady) {
      if (retries > 0) {
        setTimeout(() => initializeAnalyticsCharts(retries - 1), 200);
        return;
      } else {
        return;
      }
    }
    setTimeout(() => {
      initializeBarLineCharts();
      initializePieCharts();
    }, 100);
  }

  // Always initialize after Livewire navigation (SPA), DOM ready, and Livewire updates
  window.addEventListener('livewire:navigated', () => { setTimeout(initializeAnalyticsCharts, 100); });
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => { setTimeout(initializeAnalyticsCharts, 100); });
  } else {
    setTimeout(initializeAnalyticsCharts, 100);
  }
  if (window.Livewire) {
    window.Livewire.hook('message.processed', () => { setTimeout(initializeAnalyticsCharts, 100); });
  }

  // Debug: Log payload on load
  console.log('Analytics payload loaded:', window.__analytics_payload);
</script>
