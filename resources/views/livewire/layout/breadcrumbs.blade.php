@php
    $isAdmin = request()->routeIs('admin.*');
    $isAssets = request()->routeIs('assets.*');
    
    $crumbs = [
        [
            'label' => 'Dashboard',
            'route' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
        ],
    ];
    
    if ($isAssets) {
        $crumbs[] = [
            'label' => 'Assets Management',
            'route' => route('assets.index'),
            'active' => request()->routeIs('assets.index'),
        ];
        
        if (request()->routeIs('assets.form')) {
            $crumbs[] = [
                'label' => request()->route('assetId') ? 'Edit Asset' : 'Add Asset',
                'route' => '#',
                'active' => true,
            ];
        } elseif (request()->routeIs('assets.transfer')) {
            $crumbs[] = [
                'label' => 'Transfer Asset',
                'route' => '#',
                'active' => true,
            ];
        } elseif (request()->routeIs('assets.history')) {
            $crumbs[] = [
                'label' => 'Asset History',
                'route' => '#',
                'active' => true,
            ];
        } elseif (request()->routeIs('assets.reports')) {
            $crumbs[] = [
                'label' => 'Reports',
                'route' => '#',
                'active' => true,
            ];
        }
    } elseif ($isAdmin) {
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