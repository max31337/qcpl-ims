<?php

namespace App\Http\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetTransferHistory;
use App\Models\Supply;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public $from;
    public $to;
    public $branchId = null; // optional filter

    public function mount(): void
    {
        $this->to = now()->toDateString();
        $this->from = now()->subDays(30)->toDateString();
    }

    public function render()
    {
        $user = Auth::user();
        // Cache key includes filters
    // Bump cache key version when payload structure changes
    $key = sprintf('admin_dash_v2:%s:%s:%s', $this->from, $this->to, $this->branchId ?? 'all');
        $data = Cache::remember($key, 600, function () use ($user) {
            $assetsQuery = Asset::query();
            $suppliesQuery = Supply::query();
            $transfers = AssetTransferHistory::query();

            if (!$user->isMainBranch()) {
                // Non-main admins should be branch-scoped just in case
                $assetsQuery->where('current_branch_id', $user->branch_id);
                $suppliesQuery->where('branch_id', $user->branch_id);
                $transfers->where(function ($q) use ($user) {
                    $q->where('origin_branch_id', $user->branch_id)
                      ->orWhere('current_branch_id', $user->branch_id);
                });
            } elseif ($this->branchId) {
                $assetsQuery->where('current_branch_id', $this->branchId);
                $suppliesQuery->where('branch_id', $this->branchId);
                $transfers->where(function ($q) {
                    $q->where('origin_branch_id', $this->branchId)
                      ->orWhere('current_branch_id', $this->branchId);
                });
            }

            // KPIs
            $totalAssets = (clone $assetsQuery)->count();
            $assetsValue = (clone $assetsQuery)->sum(DB::raw('COALESCE(total_cost, 0)'));
            $supplySkus = (clone $suppliesQuery)->count();
            $lowStock = (clone $suppliesQuery)->whereColumn('current_stock','<','min_stock')->count();
            $suppliesValue = (clone $suppliesQuery)->selectRaw('SUM(current_stock*unit_cost) v')->value('v') ?? 0;

            // Monthly assets created (last 6 months)
            $driver = DB::connection()->getDriverName();
            // Use appropriate date formatting function per driver
            $monthExpr = match ($driver) {
                'mysql', 'mariadb' => "DATE_FORMAT(created_at, '%Y-%m')",
                'pgsql' => "TO_CHAR(created_at, 'YYYY-MM')",
                'sqlite' => "STRFTIME('%Y-%m', created_at)",
                'sqlsrv' => "FORMAT(created_at, 'yyyy-MM')",
                default => "DATE_FORMAT(created_at, '%Y-%m')",
            };

            $monthlyAssets = (clone $assetsQuery)
                ->selectRaw("$monthExpr as m, COUNT(*) c")
                ->whereBetween('created_at', [
                    $this->from . ' 00:00:00',
                    $this->to . ' 23:59:59',
                ])
                ->groupBy('m')
                ->orderBy('m')
                ->limit(6)
                ->get();

            // Assets by status (for donut chart)
            $assetsByStatus = (clone $assetsQuery)
                ->selectRaw('status, COUNT(*) c')
                ->groupBy('status')
                ->pluck('c', 'status');

            // Supplies stock health buckets
            $stockOut = (clone $suppliesQuery)->where('current_stock', '<=', 0)->count();
            $stockLow = (clone $suppliesQuery)
                ->where('current_stock', '>', 0)
                ->whereColumn('current_stock', '<', 'min_stock')
                ->count();
            $stockOk = (clone $suppliesQuery)
                ->whereColumn('current_stock', '>=', 'min_stock')
                ->count();

                // Top supply categories by value
                $topSupplyCategories = (clone $suppliesQuery)
                    ->selectRaw('category_id, SUM(current_stock*unit_cost) as value')
                    ->groupBy('category_id')
                    ->orderByDesc('value')
                    ->limit(5)
                    ->get();

            // Top transfer routes
            $topRoutes = (clone $transfers)
                ->selectRaw('origin_branch_id,current_branch_id,COUNT(*) c')
                ->whereBetween('transfer_date', [$this->from, $this->to])
                ->groupBy('origin_branch_id','current_branch_id')
                ->orderByDesc('c')
                ->limit(5)
                ->get();

            // Recent Activity
            $recentActivity = ActivityLog::orderByDesc('created_at')
                ->limit(10)
                ->get(['id','user_id','action','model','model_id','description','created_at']);

            return compact(
                'totalAssets',
                'assetsValue',
                'supplySkus',
                'lowStock',
                'suppliesValue',
                'monthlyAssets',
                'assetsByStatus',
                'stockOut',
                'stockLow',
                'stockOk',
                'topSupplyCategories',
                'topRoutes',
                'recentActivity'
            );
        });

        return view('livewire.admin.dashboard', $data);
    }
}
