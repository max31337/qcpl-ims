<div class="h-full flex flex-col bg-white border-r border-border">
    <!-- Brand header -->
    <div class="flex h-14 items-center border-b border-border px-4">
        <a href="{{ route('welcome') }}" class="flex items-center gap-3 font-semibold" wire:navigate>
            <img src="{{ asset('Quezon_City_Public_Library_logo.png') }}" alt="QC Public Library" class="h-8 w-8 object-contain">
            <div class="flex flex-col">
                <span class="text-sm font-semibold text-foreground">QCPL-IMS</span>
                <span class="text-xs text-muted-foreground">Inventory Management</span>
            </div>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 space-y-1 p-2">
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground {{ request()->routeIs('dashboard') ? 'bg-accent text-accent-foreground' : 'text-muted-foreground' }}"
           wire:navigate>
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="7" height="9" x="3" y="3" rx="1"/>
                <rect width="7" height="5" x="14" y="3" rx="1"/>
                <rect width="7" height="9" x="14" y="12" rx="1"/>
                <rect width="7" height="5" x="3" y="16" rx="1"/>
            </svg>
            Dashboard
        </a>
        
        <a href="{{ route('assets.index') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground {{ request()->routeIs('assets.*') ? 'bg-accent text-accent-foreground' : 'text-muted-foreground' }}"
           wire:navigate>
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                <line x1="16" x2="16" y1="2" y2="6"/>
                <line x1="8" x2="8" y1="2" y2="6"/>
                <line x1="3" x2="21" y1="10" y2="10"/>
                <path d="M8 14h.01"/>
                <path d="M12 14h.01"/>
                <path d="M16 14h.01"/>
                <path d="M8 18h.01"/>
                <path d="M12 18h.01"/>
                <path d="M16 18h.01"/>
            </svg>
            Assets Management
        </a>
        
        <a href="{{ route('admin.invitations') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground {{ request()->routeIs('admin.*') ? 'bg-accent text-accent-foreground' : 'text-muted-foreground' }}"
           wire:navigate>
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="m22 2-5 10-3-3-2 5"/>
            </svg>
            User Management
        </a>
    </nav>
</div>
