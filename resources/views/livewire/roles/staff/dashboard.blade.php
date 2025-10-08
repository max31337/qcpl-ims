<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Staff Dashboard</h1>
      <p class="text-muted-foreground">Overview of branch assets and supplies.</p>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <x-ui.card class="p-6">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-muted-foreground">Branch Assets</p>
          <p class="text-3xl font-bold">{{ number_format($totalAssets ?? 0) }}</p>
          <p class="text-xs text-muted-foreground mt-1">Total items</p>
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
          <p class="text-sm text-muted-foreground">Branch Supplies</p>
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
          <p class="text-sm text-muted-foreground">Low Stock Items</p>
          <p class="text-3xl font-bold text-amber-600">{{ number_format($lowStock ?? 0) }}</p>
          <p class="text-xs text-muted-foreground mt-1">Need attention</p>
        </div>
        <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center">
          <x-ui.icon name="alert-triangle" class="h-6 w-6 text-amber-600" />
        </div>
      </div>
    </x-ui.card>
  </div>

  <!-- Quick Links -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <x-ui.card class="p-6">
      <h3 class="text-lg font-medium mb-4">Quick Actions</h3>
      <div class="space-y-2">
        <a href="{{ route('supplies.index') }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-accent">
          <div class="flex items-center gap-3">
            <x-ui.icon name="package" class="h-5 w-5 text-muted-foreground" />
            <span>View Supplies</span>
          </div>
          <x-ui.icon name="chevron-right" class="h-4 w-4 text-muted-foreground" />
        </a>
        
        <a href="{{ route('supplies.reports') }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-accent">
          <div class="flex items-center gap-3">
            <x-ui.icon name="bar-chart" class="h-5 w-5 text-muted-foreground" />
            <span>Supply Reports</span>
          </div>
          <x-ui.icon name="chevron-right" class="h-4 w-4 text-muted-foreground" />
        </a>

        <a href="{{ route('activity.me') }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-accent">
          <div class="flex items-center gap-3">
            <x-ui.icon name="activity" class="h-5 w-5 text-muted-foreground" />
            <span>My Activity</span>
          </div>
          <x-ui.icon name="chevron-right" class="h-4 w-4 text-muted-foreground" />
        </a>
      </div>
    </x-ui.card>

    <x-ui.card class="p-6">
      <h3 class="text-lg font-medium mb-4">Branch Information</h3>
      <div class="space-y-3">
        <div>
          <p class="text-sm text-muted-foreground">Your Branch</p>
          <p class="text-lg font-medium">{{ auth()->user()->branch->name ?? 'N/A' }}</p>
        </div>
        <div>
          <p class="text-sm text-muted-foreground">Division</p>
          <p class="text-lg font-medium">{{ auth()->user()->division->name ?? 'N/A' }}</p>
        </div>
        <div>
          <p class="text-sm text-muted-foreground">Section</p>
          <p class="text-lg font-medium">{{ auth()->user()->section->name ?? 'N/A' }}</p>
        </div>
      </div>
    </x-ui.card>
  </div>
</div>
