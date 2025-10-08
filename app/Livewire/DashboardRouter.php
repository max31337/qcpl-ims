<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardRouter extends Component
{
    public function mount()
    {
        $user = Auth::user();
        
        // Route user to their appropriate dashboard based on primary role
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isSupplyOfficer()) {
            return redirect()->route('roles.supply-officer.dashboard');
        } elseif ($user->isPropertyOfficer()) {
            return redirect()->route('roles.property-officer.dashboard');
        } elseif ($user->isObserver()) {
            return redirect()->route('roles.observer.dashboard');
        } elseif ($user->isStaff()) {
            return redirect()->route('roles.staff.dashboard');
        }
        
        // Fallback to profile if no specific role matched
        return redirect()->route('profile');
    }

    public function render()
    {
        return view('livewire.dashboard-router');
    }
}
