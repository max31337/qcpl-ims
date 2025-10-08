<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetTransferHistory;
use App\Models\Supply;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        
        $key = 'admin_dash_v5_user_' . $user->id . '_branch_' . $user->branch_id;
        $data = Cache::remember($key, 600, function () use ($user) {
            $assetsQuery = Asset::query();
            $suppliesQuery = Supply::query();
            $transfers = AssetTransferHistory::query();
            
            // Admin sees all data (no branch restriction)
            // KPIs
            $totalAssets = (clone $assetsQuery)->count();
            $assetsValue = (clone $assetsQuery)->sum(DB::raw('COALESCE(assets.total_cost, 0)'));
            $supplySkus = (clone $suppliesQuery)->count();
            $lowStock = Supply::where('current_stock', '>', 0)
                ->whereColumn('current_stock', '<', 'min_stock')
                ->count();
            $suppliesValue = (clone $suppliesQuery)->selectRaw('SUM(current_stock*unit_cost) v')->value('v') ?? 0;

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

            // Monthly supplies created
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

            // Assets by status
            $assetsByStatus = (clone $assetsQuery)
                ->selectRaw('status, COUNT(*) c')
                ->groupBy('status')
                ->pluck('c', 'status');

            // Top supply categories
            $topSupplyCategories = (clone $suppliesQuery)
                ->leftJoin('categories', 'supplies.category_id', '=', 'categories.id')
                ->selectRaw("COALESCE(categories.name, 'Uncategorized') as name, SUM(current_stock*unit_cost) as v")
                ->groupBy('categories.name')
                ->orderByDesc('v')
                ->limit(5)
                ->get();

            // Recent activity
            $recentActivity = ActivityLog::orderByDesc('created_at')
                ->limit(10)
                ->get(['id', 'user_id', 'action', 'model', 'model_id', 'description', 'created_at']);

            // User stats
            $totalUsers = User::count();
            $activeUsers = User::where('is_active', true)->count();
            $pendingApprovals = User::where('approval_status', 'pending')->count();

            // Branch stats
            $totalBranches = Branch::count();
            $activeBranches = Branch::where('is_active', true)->count();

            return compact(
                'totalAssets',
                'assetsValue',
                'supplySkus',
                'lowStock',
                'suppliesValue',
                'monthlyLineLabels',
                'monthlyLineValues',
                'assetsByStatus',
                'topSupplyCategories',
                'recentActivity',
                'suppliesMonthlyValues',
                'totalUsers',
                'activeUsers',
                'pendingApprovals',
                'totalBranches',
                'activeBranches'
            );
        });

        return view('livewire.admin.dashboard', $data);
    }
}
