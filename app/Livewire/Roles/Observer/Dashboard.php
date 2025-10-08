<?php

namespace App\Livewire\Roles\Observer;

use App\Models\Asset;
use App\Models\Supply;
use App\Models\ActivityLog;
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
        
        $key = 'observer_dash_v1_user_' . $user->id . '_branch_' . $user->branch_id;
        $data = Cache::remember($key, 600, function () use ($user) {
            // Observers can see all if main branch, otherwise branch-specific
            if ($user->isMainBranch()) {
                $totalAssets = Asset::count();
                $assetsValue = Asset::sum(DB::raw('COALESCE(total_cost, 0)'));
                $totalSupplies = Supply::count();
                $suppliesValue = Supply::selectRaw('SUM(current_stock*unit_cost) v')->value('v') ?? 0;
            } else {
                $totalAssets = Asset::where('current_branch_id', $user->branch_id)->count();
                $assetsValue = Asset::where('current_branch_id', $user->branch_id)
                    ->sum(DB::raw('COALESCE(total_cost, 0)'));
                $totalSupplies = Supply::forUser($user)->count();
                $suppliesValue = Supply::forUser($user)
                    ->selectRaw('SUM(current_stock*unit_cost) v')
                    ->value('v') ?? 0;
            }

            // Recent activity
            $recentActivity = ActivityLog::orderByDesc('created_at')
                ->limit(10)
                ->get(['id', 'user_id', 'action', 'model', 'model_id', 'description', 'created_at']);

            return compact(
                'totalAssets',
                'assetsValue',
                'totalSupplies',
                'suppliesValue',
                'recentActivity'
            );
        });

        return view('livewire.roles.observer.dashboard', $data);
    }
}
