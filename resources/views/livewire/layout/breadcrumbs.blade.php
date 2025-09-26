@php
    $isAdmin = request()->routeIs('admin.*');
    $crumbs = [
        [
            'label' => 'Dashboard',
            'route' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
        ],
    ];
    if ($isAdmin) {
        $crumbs[] = [
            'label' => 'User Management',
            'route' => route('admin.invitations'),
            'active' => true,
        ];
    }
@endphp

<div class="flex items-center space-x-1 px-4 py-2 text-sm text-muted-foreground">
    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
        <polyline points="9,22 9,12 15,12 15,22"/>
    </svg>
    
    @foreach ($crumbs as $i => $crumb)
        @if ($i > 0)
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m9 18 6-6-6-6"/>
            </svg>
        @endif
        
        @if ($crumb['active'])
            <span class="font-medium text-foreground">{{ $crumb['label'] }}</span>
        @else
            <a href="{{ $crumb['route'] }}" 
               class="font-medium transition-colors hover:text-foreground"
               wire:navigate>
                {{ $crumb['label'] }}
            </a>
        @endif
    @endforeach
</div>