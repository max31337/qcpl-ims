<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetTransferHistory;
use App\Models\Supply;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    // No filters - simple overview only
    public function mount(): void
    {
        // Dashboard is now filter-free
    }

    public function render()
    {
        $user = Auth::user();
        // Simple cache key - no filters in dashboard
        $key = 'admin_dash_v4_simple';
        $data = Cache::remember($key, 600, function () use ($user) {
            // Simple queries without complex scoping for dashboard overview
            $assetsQuery = Asset::query();
            $suppliesQuery = Supply::query();
            $transfers = AssetTransferHistory::query();
            
            // Basic user scoping - only restrict if not main branch admin
            if (!($user->isMainBranch() && ($user->isAdmin() || $user->isObserver()))) {
                $assetsQuery->where('current_branch_id', $user->branch_id);
                $suppliesQuery->where('branch_id', $user->branch_id);
                $transfers->where(function ($q) use ($user) {
                    $q->where('origin_branch_id', $user->branch_id)
                      ->orWhere('current_branch_id', $user->branch_id);
                });
            }

            // KPIs
            $totalAssets = (clone $assetsQuery)->count();
            $assetsValue = (clone $assetsQuery)->sum(DB::raw('COALESCE(assets.total_cost, 0)'));
            $supplySkus = (clone $suppliesQuery)->count();
            $lowStock = (clone $suppliesQuery)->whereColumn('current_stock','<','min_stock')->count();
            $suppliesValue = (clone $suppliesQuery)->selectRaw('SUM(current_stock*unit_cost) v')->value('v') ?? 0;

            // Monthly assets created (last 12 months, up to end of selected 'to' month)
            $driver = DB::connection()->getDriverName();
            // Use appropriate date formatting function per driver
            $monthExpr = match ($driver) {
                'mysql', 'mariadb' => "DATE_FORMAT(assets.created_at, '%Y-%m')",
                'pgsql' => "TO_CHAR(assets.created_at, 'YYYY-MM')",
                'sqlite' => "STRFTIME('%Y-%m', assets.created_at)",
                'sqlsrv' => "FORMAT(assets.created_at, 'yyyy-MM')",
                default => "DATE_FORMAT(assets.created_at, '%Y-%m')",
            };
            $yearExpr = match ($driver) {
                'mysql', 'mariadb' => 'YEAR(date_acquired)',
                'pgsql' => 'EXTRACT(YEAR FROM date_acquired)',
                'sqlite' => "STRFTIME('%Y', date_acquired)",
                'sqlsrv' => 'YEAR(date_acquired)',
                default => 'YEAR(date_acquired)',
            };

            // Fixed 12-month window ending now
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

            // Monthly supplies created (last 12 months) for bar chart
            $suppliesMonthExpr = match ($driver) {
                'mysql', 'mariadb' => "DATE_FORMAT(supplies.created_at, '%Y-%m')",
                'pgsql' => "TO_CHAR(supplies.created_at, 'YYYY-MM')",
                'sqlite' => "STRFTIME('%Y-%m', supplies.created_at)",
                'sqlsrv' => "FORMAT(supplies.created_at, 'yyyy-MM')",
                default => "DATE_FORMAT(supplies.created_at, '%Y-%m')",
            };
            $rawMonthlySupplies = (clone $suppliesQuery)
                ->selectRaw("$suppliesMonthExpr as m, COUNT(*) c")
                ->whereBetween('supplies.created_at', [
                    $lineStart->toDateString() . ' 00:00:00',
                    $lineEnd->toDateString() . ' 23:59:59',
                ])
                ->groupBy('m')
                ->get()
                ->keyBy('m');

            $suppliesMonthlyValues = [];
            for ($i = 0; $i < 12; $i++) {
                $m = (clone $lineStart)->addMonths($i);
                $key = $m->format('Y-m');
                $suppliesMonthlyValues[] = (int) ($rawMonthlySupplies[$key]->c ?? 0);
            }

            // Assets by status (for donut chart)
            $assetsByStatus = (clone $assetsQuery)
                ->selectRaw('status, COUNT(*) c')
                ->groupBy('status')
                ->pluck('c', 'status');

            // Assets value by category (top 5)
            $assetsByCategoryValue = (clone $assetsQuery)
                ->leftJoin('categories', 'assets.category_id', '=', 'categories.id')
                ->selectRaw("COALESCE(categories.name, 'Uncategorized') as name, SUM(COALESCE(assets.total_cost, 0)) as v")
                ->groupBy('categories.name')
                ->orderByDesc('v')
                ->limit(5)
                ->get();

            // Assets by branch (top 5 by count)
            $assetsByBranch = (clone $assetsQuery)
                ->leftJoin('branches', 'assets.current_branch_id', '=', 'branches.id')
                ->selectRaw("COALESCE(branches.name, 'Unassigned') as name, COUNT(*) as c")
                ->groupBy('branches.name')
                ->orderByDesc('c')
                ->limit(5)
                ->get();

            // Assets by year acquired (last 6 buckets)
            $assetsByYear = (clone $assetsQuery)
                ->whereNotNull('date_acquired')
                ->selectRaw("$yearExpr as y, COUNT(*) as c")
                ->groupBy('y')
                ->orderBy('y')
                ->limit(6)
                ->get();

            // Supplies stock health buckets
            $stockOut = (clone $suppliesQuery)->where('current_stock', '<=', 0)->count();
            $stockLow = (clone $suppliesQuery)
                ->where('current_stock', '>', 0)
                ->whereColumn('current_stock', '<', 'min_stock')
                ->count();
            $stockOk = (clone $suppliesQuery)
                ->whereColumn('current_stock', '>=', 'min_stock')
                ->count();

            // Top supply categories by value (name + v, matching blade)
            $topSupplyCategories = (clone $suppliesQuery)
                ->leftJoin('categories', 'supplies.category_id', '=', 'categories.id')
                ->selectRaw("COALESCE(categories.name, 'Uncategorized') as name, SUM(current_stock*unit_cost) as v")
                // Group by the underlying column for compatibility across drivers
                ->groupBy('categories.name')
                ->orderByDesc('v')
                ->limit(5)
                ->get();

            // Recent transfers (last 30 days) - no filters
            $topRoutes = (clone $transfers)
                ->leftJoin('branches as ob', 'asset_transfer_histories.origin_branch_id', '=', 'ob.id')
                ->leftJoin('branches as cb', 'asset_transfer_histories.current_branch_id', '=', 'cb.id')
                ->selectRaw('asset_transfer_histories.origin_branch_id, asset_transfer_histories.current_branch_id, ob.name as origin_name, cb.name as current_name, COUNT(*) c')
                ->whereBetween('transfer_date', [now()->subDays(30)->toDateString(), now()->toDateString()])
                ->groupBy('asset_transfer_histories.origin_branch_id','asset_transfer_histories.current_branch_id','ob.name','cb.name')
                ->orderByDesc('c')
                ->limit(5)
                ->get();

            // Recent Activity (overview)
            $recentActivity = ActivityLog::orderByDesc('created_at')
                ->limit(10)
                ->get(['id','user_id','action','model','model_id','description','created_at']);

            return compact(
                'totalAssets',
                'assetsValue',
                'supplySkus',
                'lowStock',
                'suppliesValue',
                'monthlyLineLabels',
                'monthlyLineValues',
                'assetsByStatus',
                'assetsByCategoryValue',
                'assetsByBranch',
                'assetsByYear',
                'stockOut',
                'stockLow',
                'stockOk',
                'topSupplyCategories',
                'topRoutes',
                'recentActivity',
                'suppliesMonthlyValues'
            );
        });

        return view('livewire.admin.dashboard', $data);
    }

    // Branch options for filter UI
    public function getBranchesProperty()
    {
        $user = Auth::user();
        if ($user->isMainBranch() && ($user->isAdmin() || $user->isObserver())) {
            return Branch::where('is_active', true)->orderBy('name')->get(['id','name']);
        }
        return Branch::where('id', $user->branch_id)->get(['id','name']);
    }
}
