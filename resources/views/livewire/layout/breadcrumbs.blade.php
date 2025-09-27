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
    <x-ui.icon name="layout-dashboard" />
    
    @foreach ($crumbs as $i => $crumb)
        @if ($i > 0)
            <x-ui.icon name="chevron-right" />
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