<div class="h-full flex flex-col bg-white border-r border-border">
    <!-- Brand header -->
    <div class="flex h-16 items-center border-b border-border px-4">
        <a href="{{ route('welcome') }}" class="flex items-center gap-3 font-semibold" wire:navigate>
            <img src="{{ asset('Quezon_City_Public_Library_logo.png') }}" alt="QC Public Library" class="h-12 w-12 object-contain">
            <div class="flex flex-col">
                <span class="text-base font-semibold text-foreground">QCPL-IMS</span>
                <span class="text-sm text-muted-foreground">Inventory Management</span>
            </div>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 space-y-1 p-2">
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground {{ request()->routeIs('dashboard') ? 'bg-accent text-accent-foreground' : 'text-muted-foreground' }}"
           wire:navigate>
            <x-ui.icon name="layout-dashboard" />
            Dashboard
        </a>
        
        <a href="{{ route('admin.analytics') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground {{ request()->routeIs('admin.analytics') ? 'bg-accent text-accent-foreground' : 'text-muted-foreground' }}"
           wire:navigate>
            <x-ui.icon name="line-chart" />
            Analytics
        </a>
        
        <a href="{{ route('assets.index') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground {{ request()->routeIs('assets.*') && !request()->routeIs('assets.transfer-histories') ? 'bg-accent text-accent-foreground' : 'text-muted-foreground' }}"
           wire:navigate>
            <x-ui.icon name="boxes" />
            Assets Management
        </a>
        
        <a href="{{ route('assets.transfer-histories') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground ml-3 {{ request()->routeIs('assets.transfer-histories') || request()->routeIs('admin.transfer-histories') ? 'bg-accent text-accent-foreground' : 'text-muted-foreground' }}"
           wire:navigate>
            <x-ui.icon name="history" />
            Transfer Histories
        </a>
        
        <a href="{{ route('supplies.index') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground {{ request()->routeIs('supplies.*') ? 'bg-accent text-accent-foreground' : 'text-muted-foreground' }}"
           wire:navigate>
            <x-ui.icon name="package" />
                    Supply Management
        </a>
        
        <a href="{{ route('admin.invitations') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground {{ request()->routeIs('admin.invitations') ? 'bg-accent text-accent-foreground' : 'text-muted-foreground' }}"
           wire:navigate>
            <x-ui.icon name="users" />
            User Management
        </a>
        
        <a href="{{ route('admin.activity-logs') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground {{ request()->routeIs('admin.activity-logs') ? 'bg-accent text-accent-foreground' : 'text-muted-foreground' }}"
           wire:navigate>
            <x-ui.icon name="activity" />
            Activity Logs
        </a>
    </nav>
</div>
