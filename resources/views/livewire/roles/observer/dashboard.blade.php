<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Observer Dashboard</h1>
      <p class="text-muted-foreground">Read-only view of system data and reports.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('admin.analytics') }}" class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm hover:bg-accent">
        <x-ui.icon name="bar-chart" class="h-4 w-4" />
        <span>Analytics</span>
      </a>
      <a href="{{ route('admin.activity-logs') }}" class="inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm hover:bg-accent">
        <x-ui.icon name="activity" class="h-4 w-4" />
        <span>Activity Logs</span>
      </a>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Total Assets</p>
          <p class="text-3xl font-bold">{{ number_format($totalAssets ?? 0) }}</p>
          <p class="text-xs text-muted-foreground mt-1">System wide</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
          <x-ui.icon name="box" class="h-6 w-6 text-blue-600" />
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Assets Value</p>
          <p class="text-3xl font-bold text-green-600">₱{{ number_format($assetsValue ?? 0, 2) }}</p>
          <p class="text-xs text-muted-foreground mt-1">Total valuation</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
          <x-ui.icon name="dollar-sign" class="h-6 w-6 text-green-600" />
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Total Supplies</p>
          <p class="text-3xl font-bold">{{ number_format($totalSupplies ?? 0) }}</p>
          <p class="text-xs text-muted-foreground mt-1">Active SKUs</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
          <x-ui.icon name="package" class="h-6 w-6 text-indigo-600" />
        </div>
      </div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Supplies Value</p>
          <p class="text-3xl font-bold text-purple-600">₱{{ number_format($suppliesValue ?? 0, 2) }}</p>
          <p class="text-xs text-muted-foreground mt-1">Inventory value</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
          <x-ui.icon name="dollar-sign" class="h-6 w-6 text-purple-600" />
        </div>
      </div>
    </x-ui.card>
  </div>

  <!-- Quick Access -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <x-ui.card class="p-6">
      <h3 class="text-lg font-medium mb-4">Reports & Analytics</h3>
      <div class="space-y-2">
        <a href="{{ route('assets.reports') }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-accent">
          <div class="flex items-center gap-3">
            <x-ui.icon name="file-text" class="h-5 w-5 text-muted-foreground" />
            <span>Asset Reports</span>
          </div>
          <x-ui.icon name="chevron-right" class="h-4 w-4 text-muted-foreground" />
        </a>
        
        <a href="{{ route('supplies.reports') }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-accent">
          <div class="flex items-center gap-3">
            <x-ui.icon name="file-text" class="h-5 w-5 text-muted-foreground" />
            <span>Supply Reports</span>
          </div>
          <x-ui.icon name="chevron-right" class="h-4 w-4 text-muted-foreground" />
        </a>

        <a href="{{ route('admin.analytics') }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-accent">
          <div class="flex items-center gap-3">
            <x-ui.icon name="bar-chart" class="h-5 w-5 text-muted-foreground" />
            <span>System Analytics</span>
          </div>
          <x-ui.icon name="chevron-right" class="h-4 w-4 text-muted-foreground" />
        </a>

        <a href="{{ route('admin.activity-logs') }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-accent">
          <div class="flex items-center gap-3">
            <x-ui.icon name="activity" class="h-5 w-5 text-muted-foreground" />
            <span>Activity Logs</span>
          </div>
          <x-ui.icon name="chevron-right" class="h-4 w-4 text-muted-foreground" />
        </a>

        <a href="{{ route('admin.transfer-histories') }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-accent">
          <div class="flex items-center gap-3">
            <x-ui.icon name="git-branch" class="h-5 w-5 text-muted-foreground" />
            <span>Transfer Histories</span>
          </div>
          <x-ui.icon name="chevron-right" class="h-4 w-4 text-muted-foreground" />
        </a>
      </div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <h3 class="text-lg font-medium mb-4">Recent Activity</h3>
      @if(!empty($recentActivity) && $recentActivity->count() > 0)
        <div class="space-y-3">
          @foreach($recentActivity->take(5) as $activity)
            <div class="flex items-start gap-3 text-sm">
              <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                <x-ui.icon name="activity" class="h-4 w-4 text-primary" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-medium truncate">{{ $activity->action }}</p>
                <p class="text-xs text-muted-foreground">{{ $activity->description }}</p>
                <p class="text-xs text-muted-foreground mt-1">{{ $activity->created_at->diffForHumans() }}</p>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="text-sm text-muted-foreground">No recent activity</div>
      @endif
    </x-ui.card>
  </div>

  <x-ui.card class="p-4 bg-blue-50 border-blue-200">
    <div class="flex items-start gap-3">
      <x-ui.icon name="info" class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" />
      <div class="text-sm text-blue-900">
        <p class="font-medium mb-1">Observer Role</p>
        <p>You have read-only access to view reports, analytics, and system activity. You cannot make changes to assets, supplies, or system settings.</p>
      </div>
    </div>
  </x-ui.card>
</div>
