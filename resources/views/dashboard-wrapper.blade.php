<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-5xl mx-auto sm:px-4 lg:px-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-4 lg:p-6">
            @auth
                @php
                    $user = auth()->user();
                @endphp

                {{-- Render a role-specific Livewire dashboard. We check common helper methods first, then role names. --}}
                @if(method_exists($user, 'isAdmin') && $user->isAdmin())
                    @livewire('admin.dashboard')
                @elseif(method_exists($user, 'isSupplyOfficer') && $user->isSupplyOfficer())
                    @livewire('roles.supply-officer.dashboard')
                @elseif(method_exists($user, 'isPropertyOfficer') && $user->isPropertyOfficer())
                    @livewire('roles.property-officer.dashboard')
                @elseif(method_exists($user, 'isObserver') && $user->isObserver())
                    @livewire('roles.observer.dashboard')
                @elseif(method_exists($user, 'isStaff') && $user->isStaff())
                    @livewire('roles.staff.dashboard')
                @else
                    {{-- Fallback: try to use the DashboardRouter Livewire class which performs the original redirection logic. --}}
                    @livewire('\App\\Livewire\\DashboardRouter')
                @endif
            @endauth
            </div>
        </div>
    </div>
</x-app-layout>
