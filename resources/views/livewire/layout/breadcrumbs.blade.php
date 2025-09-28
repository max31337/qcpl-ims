@php
    $isAdmin = request()->routeIs('admin.*');
    $isAssets = request()->routeIs('assets.*');
    $isSupplies = request()->routeIs('supplies.*');
    
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
        } elseif (request()->routeIs('assets.transfer-histories')) {
            $crumbs[] = [
                'label' => 'Transfer Histories',
                'route' => '#',
                'active' => true,
            ];
        }
    } elseif ($isSupplies) {
        $crumbs[] = [
            'label' => 'Supplies Management',
            'route' => route('supplies.index'),
            'active' => request()->routeIs('supplies.index'),
        ];
        
        if (request()->routeIs('supplies.form')) {
            $crumbs[] = [
                'label' => request()->route('supplyId') ? 'Edit Supply' : 'Add Supply',
                'route' => '#',
                'active' => true,
            ];
        } elseif (request()->routeIs('supplies.adjustment')) {
            $crumbs[] = [
                'label' => 'Stock Adjustment',
                'route' => '#',
                'active' => true,
            ];
        } elseif (request()->routeIs('supplies.reports')) {
            $crumbs[] = [
                'label' => 'Reports',
                'route' => '#',
                'active' => true,
            ];
        }
    } elseif ($isAdmin) {
        // Admin section breadcrumbs
        if (request()->routeIs('admin.analytics')) {
            $crumbs[] = [
                'label' => 'Analytics',
                'route' => '#',
                'active' => true,
            ];
        } elseif (request()->routeIs('admin.transfer-histories')) {
            $crumbs[] = [
                'label' => 'Transfer Histories',
                'route' => '#',
                'active' => true,
            ];
        } elseif (request()->routeIs('admin.invitations')) {
            $crumbs[] = [
                'label' => 'User Management',
                'route' => '#',
                'active' => true,
            ];
        } elseif (request()->routeIs('admin.assets.reports')) {
            $crumbs[] = [
                'label' => 'Assets Management',
                'route' => route('assets.index'),
                'active' => false,
            ];
            $crumbs[] = [
                'label' => 'Asset Reports',
                'route' => '#',
                'active' => true,
            ];
        } elseif (request()->routeIs('admin.branches')) {
            $crumbs[] = [
                'label' => 'Branch Management',
                'route' => '#',
                'active' => true,
            ];
        } elseif (request()->routeIs('admin.categories')) {
            $crumbs[] = [
                'label' => 'Category Management',
                'route' => '#',
                'active' => true,
            ];
        } elseif (request()->routeIs('admin.activity-logs')) {
            $crumbs[] = [
                'label' => 'Activity Logs',
                'route' => '#',
                'active' => true,
            ];
        } elseif (request()->routeIs('admin.users')) {
            $crumbs[] = [
                'label' => 'User Management',
                'route' => '#',
                'active' => true,
            ];
        } elseif (request()->routeIs('admin.settings')) {
            $crumbs[] = [
                'label' => 'System Settings',
                'route' => '#',
                'active' => true,
            ];
        }
    } elseif (request()->routeIs('profile')) {
        $crumbs[] = [
            'label' => 'Profile',
            'route' => '#',
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