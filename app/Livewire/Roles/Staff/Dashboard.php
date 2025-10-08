<?php

namespace App\Livewire\Roles\Staff;

use App\Models\Asset;
use App\Models\Supply;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        
        $key = 'staff_dash_v1_user_' . $user->id . '_branch_' . $user->branch_id;
        $data = Cache::remember($key, 600, function () use ($user) {
            // Branch-specific data only
            $totalAssets = Asset::where('current_branch_id', $user->branch_id)->count();
            $assetsValue = Asset::where('current_branch_id', $user->branch_id)
                ->sum(DB::raw('COALESCE(total_cost, 0)'));
            
            $totalSupplies = Supply::forUser($user)->count();
            $suppliesValue = Supply::forUser($user)
                ->selectRaw('SUM(current_stock*unit_cost) v')
                ->value('v') ?? 0;
                
            $lowStock = Supply::forUser($user)
                ->where('current_stock', '>', 0)
                ->whereColumn('current_stock', '<', 'min_stock')
                ->count();

            return compact(
                'totalAssets',
                'assetsValue',
                'totalSupplies',
                'suppliesValue',
                'lowStock'
            );
        });

        return view('livewire.roles.staff.dashboard', $data);
    }
}
