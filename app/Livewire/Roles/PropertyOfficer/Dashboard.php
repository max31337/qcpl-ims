<?php

namespace App\Livewire\Roles\PropertyOfficer;

use App\Models\Asset;
use App\Models\AssetTransferHistory;
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
        
        $key = 'property_officer_dash_v1_user_' . $user->id . '_branch_' . $user->branch_id;
        $data = Cache::remember($key, 600, function () use ($user) {
            $assetsQuery = Asset::query();
            $transfers = AssetTransferHistory::query();
            
            // Basic user scoping - only restrict if not main branch admin
            if (!$user->isMainBranch()) {
                $assetsQuery->where('current_branch_id', $user->branch_id);
                $transfers->where(function ($q) use ($user) {
                    $q->where('origin_branch_id', $user->branch_id)
                      ->orWhere('current_branch_id', $user->branch_id);
                });
            }

            // KPIs
            $totalAssets = (clone $assetsQuery)->count();
            $assetsValue = (clone $assetsQuery)->sum(DB::raw('COALESCE(assets.total_cost, 0)'));
            $activeAssets = (clone $assetsQuery)->where('status', 'active')->count();
            $condemnedAssets = (clone $assetsQuery)->where('status', 'condemned')->count();

            // Monthly assets created (last 12 months)
            $driver = DB::connection()->getDriverName();
            $monthExpr = match ($driver) {
                'mysql', 'mariadb' => "DATE_FORMAT(assets.created_at, '%Y-%m')",
                'pgsql' => "TO_CHAR(assets.created_at, 'YYYY-MM')",
                'sqlite' => "STRFTIME('%Y-%m', assets.created_at)",
                'sqlsrv' => "FORMAT(assets.created_at, 'yyyy-MM')",
                default => "DATE_FORMAT(assets.created_at, '%Y-%m')",
            };

            $lineEnd = now()->endOfMonth();
            $lineStart = (clone $lineEnd)->startOfMonth()->subMonths(11);
            
            $rawMonthly = (clone $assetsQuery)
                ->selectRaw("$monthExpr as m, COUNT(*) c")
                ->whereBetween('assets.created_at', [
                    $lineStart->toDateString() . ' 00:00:00',
                    $lineEnd->toDateString() . ' 23:59:59',
                ])
                ->groupBy('m')
                ->get()
                ->keyBy('m');

            $monthlyLineLabels = [];
            $monthlyLineValues = [];
            for ($i = 0; $i < 12; $i++) {
                $m = (clone $lineStart)->addMonths($i);
                $key = $m->format('Y-m');
                $monthlyLineLabels[] = $m->format('M Y');
                $monthlyLineValues[] = (int) ($rawMonthly[$key]->c ?? 0);
            }

            // Assets by status
            $assetsByStatus = (clone $assetsQuery)
                ->selectRaw('status, COUNT(*) c')
                ->groupBy('status')
                ->pluck('c', 'status');

            // Assets by category value
            $assetsByCategoryValue = (clone $assetsQuery)
                ->leftJoin('categories', 'assets.category_id', '=', 'categories.id')
                ->selectRaw("COALESCE(categories.name, 'Uncategorized') as name, SUM(COALESCE(assets.total_cost, 0)) as v")
                ->groupBy('categories.name')
                ->orderByDesc('v')
                ->limit(5)
                ->get();

            // Recent transfers (last 30 days)
            $recentTransfers = (clone $transfers)
                ->with(['asset', 'originBranch', 'currentBranch', 'transferredBy'])
                ->whereBetween('transfer_date', [now()->subDays(30)->toDateString(), now()->toDateString()])
                ->orderByDesc('transfer_date')
                ->limit(10)
                ->get();

            // Transfer routes (top 5)
            $topRoutes = (clone $transfers)
                ->leftJoin('branches as ob', 'asset_transfer_histories.origin_branch_id', '=', 'ob.id')
                ->leftJoin('branches as cb', 'asset_transfer_histories.current_branch_id', '=', 'cb.id')
                ->selectRaw('asset_transfer_histories.origin_branch_id, asset_transfer_histories.current_branch_id, ob.name as origin_name, cb.name as current_name, COUNT(*) c')
                ->whereBetween('transfer_date', [now()->subDays(30)->toDateString(), now()->toDateString()])
                ->groupBy('asset_transfer_histories.origin_branch_id', 'asset_transfer_histories.current_branch_id', 'ob.name', 'cb.name')
                ->orderByDesc('c')
                ->limit(5)
                ->get();

            return compact(
                'totalAssets',
                'assetsValue',
                'activeAssets',
                'condemnedAssets',
                'monthlyLineLabels',
                'monthlyLineValues',
                'assetsByStatus',
                'assetsByCategoryValue',
                'recentTransfers',
                'topRoutes'
            );
        });

        return view('livewire.roles.property-officer.dashboard', $data);
    }
}
