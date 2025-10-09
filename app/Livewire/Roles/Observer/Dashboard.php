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
    public bool $showMainLibraryOnly = false;

    public function toggleScope()
    {
        $this->showMainLibraryOnly = !$this->showMainLibraryOnly;
        
        // Clear the cache for both scope states to force refresh
        $user = Auth::user();
        $mainKey = 'observer_dash_v2_user_' . $user->id . '_branch_' . $user->branch_id . '_scope_main';
        $allKey = 'observer_dash_v2_user_' . $user->id . '_branch_' . $user->branch_id . '_scope_all';
        
        Cache::forget($mainKey);
        Cache::forget($allKey);
    }

    public function render()
    {
        $user = Auth::user();
        
        $scope = $this->showMainLibraryOnly ? 'main' : 'all';
        $showMainLibraryOnly = $this->showMainLibraryOnly; // Capture for closure
        $key = 'observer_dash_v2_user_' . $user->id . '_branch_' . $user->branch_id . '_scope_' . $scope;
        $data = Cache::remember($key, 600, function () use ($user, $showMainLibraryOnly) {
            // Apply scoping based on toggle and user branch
            if (!$user->isMainBranch() || $showMainLibraryOnly) {
                // Branch-specific data
                $totalAssets = Asset::where('current_branch_id', $user->branch_id)->count();
                $assetsValue = Asset::where('current_branch_id', $user->branch_id)
                    ->sum(DB::raw('COALESCE(total_cost, 0)'));
                $totalSupplies = Supply::where('branch_id', $user->branch_id)->count();
                $suppliesValue = Supply::where('branch_id', $user->branch_id)
                    ->selectRaw('SUM(current_stock*unit_cost) v')
                    ->value('v') ?? 0;
            } else {
                // Global data for main library observers when toggle is off
                $totalAssets = Asset::count();
                $assetsValue = Asset::sum(DB::raw('COALESCE(total_cost, 0)'));
                $totalSupplies = Supply::count();
                $suppliesValue = Supply::selectRaw('SUM(current_stock*unit_cost) v')->value('v') ?? 0;
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

        return view('livewire.roles.observer.dashboard', array_merge($data, [
            'showMainLibraryOnly' => $this->showMainLibraryOnly,
            'isMainBranch' => $user->isMainBranch(),
        ]));
    }
}
