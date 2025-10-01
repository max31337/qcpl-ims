<?php

namespace App\Http\Livewire\Admin;

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

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public $from;
    public $to;
    public $branchId = null; // optional filter

    public function mount(): void
    {
        $user = Auth::user();
        // Note: Previously redirected supply officers to supplies.analytics.
        // Keep them on /dashboard to show the lightweight overview with quick actions.

        $this->to = now()->toDateString();
        $this->from = now()->subDays(30)->toDateString();
    }

    public function render()
    {
        $user = Auth::user();
        // Use scopeForUser for strict isolation and consistent counts
        $baseSupplies = Supply::query()->forUser($user);
        // Version cache by last supply update so stock health stays fresh
        $supVer = (clone $baseSupplies)->max('last_updated') ?? (clone $baseSupplies)->max('updated_at') ?? null;
        if ($supVer instanceof \Carbon\CarbonInterface) {
            $supVerStr = $supVer->toDateTimeString();
        } else {
            $supVerStr = $supVer ? (string)$supVer : '0';
        }
        // Cache key includes date filters, selected branch (if any), user id, and supplies version
        $key = sprintf('admin_dash_v3:%s:%s:%s:u%s:s%s', $this->from, $this->to, $this->branchId ?? 'all', $user->id, $supVerStr);

        // Short TTL to avoid stale discrepancies (1 minute)
        $data = Cache::remember($key, 60, function () use ($user) {
            $assetsQuery = Asset::query()->forUser($user);
            $suppliesQuery = Supply::query()->forUser($user);
            $transfers = AssetTransferHistory::query();

            // If admin/observer filtering by branch, apply explicit filter
            if (($user->isAdmin() || $user->isObserver() || $user->isMainBranch()) && $this->branchId) {
                $assetsQuery->where('current_branch_id', $this->branchId);
                $suppliesQuery->where('branch_id', $this->branchId);
                $transfers->where(function ($q) {
                    $q->where('origin_branch_id', $this->branchId)
                      ->orWhere('current_branch_id', $this->branchId);
                });
            } else {
                // Ensure transfers reflect the same visibility as supplies/assets
                if (!$user->isAdmin() && !$user->isObserver() && !$user->isMainBranch()) {
                    $transfers->where(function ($q) use ($user) {
                        $q->where('origin_branch_id', $user->branch_id)
                          ->orWhere('current_branch_id', $user->branch_id);
                    });
                }
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
            $yearExpr = match ($driver) {
                'mysql', 'mariadb' => 'YEAR(date_acquired)',
                'pgsql' => 'EXTRACT(YEAR FROM date_acquired)',
                'sqlite' => "STRFTIME('%Y', date_acquired)",
                'sqlsrv' => 'YEAR(date_acquired)',
                default => 'YEAR(date_acquired)',
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

            // Supplies monthly aggregates aligned to the months above
            $suppliesMonthly = [];
            $transfersMonthly = [];
            if (!empty($monthlyAssets) && $monthlyAssets->count()) {
                $months = $monthlyAssets->pluck('m')->all();

                $suppliesGrouped = (clone $suppliesQuery)
                    ->selectRaw("$monthExpr as m, COUNT(*) c")
                    ->whereBetween('created_at', [
                        $this->from . ' 00:00:00',
                        $this->to . ' 23:59:59',
                    ])
                    ->groupBy('m')
                    ->orderBy('m')
                    ->get()
                    ->pluck('c', 'm')
                    ->all();

                // For transfers, replace created_at reference with transfer_date
                $monthExprForTransfer = str_replace('created_at', 'transfer_date', $monthExpr);
                $transfersGrouped = (clone $transfers)
                    ->selectRaw("$monthExprForTransfer as m, COUNT(*) c")
                    ->whereBetween('transfer_date', [$this->from, $this->to])
                    ->groupBy('m')
                    ->orderBy('m')
                    ->get()
                    ->pluck('c', 'm')
                    ->all();

                foreach ($months as $m) {
                    $suppliesMonthly[] = isset($suppliesGrouped[$m]) ? (int) $suppliesGrouped[$m] : 0;
                    $transfersMonthly[] = isset($transfersGrouped[$m]) ? (int) $transfersGrouped[$m] : 0;
                }
            }

            // Assets by status (for donut chart)
            $assetsByStatus = (clone $assetsQuery)
                ->selectRaw('status, COUNT(*) c')
                ->groupBy('status')
                ->pluck('c', 'status');

            // Assets value by category (top 5)
            $assetsByCategoryValue = (clone $assetsQuery)
                ->leftJoin('categories', 'assets.category_id', '=', 'categories.id')
                ->selectRaw("COALESCE(categories.name, 'Uncategorized') as name, SUM(COALESCE(total_cost, 0)) as v")
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

            // Decision support datasets for supply officers
            // 1) Critical low-stock items (top by reorder value)
            $lowStockItems = (clone $suppliesQuery)
                ->whereColumn('current_stock', '<', 'min_stock')
                ->select(
                    'id', 'supply_number', 'description', 'current_stock', 'min_stock', 'unit_cost',
                    DB::raw('(min_stock - current_stock) as deficit'),
                    DB::raw('(min_stock - current_stock) * unit_cost as reorder_value')
                )
                ->orderByDesc(DB::raw('(min_stock - current_stock) * unit_cost'))
                ->limit(8)
                ->get();

            // 2) Total reorder value gap for all low-stock SKUs
            $lowStockValueGap = (clone $suppliesQuery)
                ->whereColumn('current_stock', '<', 'min_stock')
                ->selectRaw('SUM( (min_stock - current_stock) * unit_cost ) as gap')
                ->value('gap') ?? 0;

            // 3) Stale SKUs (no movement in 90+ days), using COALESCE(last_updated, updated_at)
            $staleThreshold = now()->subDays(90);
            $staleSkus = (clone $suppliesQuery)
                ->where('current_stock', '>', 0)
                ->whereRaw('COALESCE(last_updated, updated_at) < ?', [$staleThreshold])
                ->select(
                    'id', 'supply_number', 'description', 'current_stock', 'unit_cost', 'updated_at', 'last_updated',
                    DB::raw('(current_stock * unit_cost) as on_hand_value')
                )
                ->orderByDesc('on_hand_value')
                ->limit(8)
                ->get();
            $staleSkusCount = (clone $suppliesQuery)
                ->where('current_stock', '>', 0)
                ->whereRaw('COALESCE(last_updated, updated_at) < ?', [$staleThreshold])
                ->count();

            // 4) Category risk: categories with most low/out-of-stock SKUs
            $categoryLowCounts = (clone $suppliesQuery)
                ->leftJoin('categories', 'supplies.category_id', '=', 'categories.id')
                ->whereColumn('current_stock', '<', 'min_stock')
                ->selectRaw("COALESCE(categories.name, 'Uncategorized') as name, COUNT(*) as c")
                ->groupBy('categories.name')
                ->orderByDesc('c')
                ->limit(5)
                ->get();

            // 5) Top on-hand value SKUs
            $topOnHandSupplies = (clone $suppliesQuery)
                ->select(
                    'id', 'supply_number', 'description', 'current_stock', 'unit_cost',
                    DB::raw('(current_stock * unit_cost) as on_hand_value')
                )
                ->orderByDesc('on_hand_value')
                ->limit(8)
                ->get();

            // Top transfer routes
            $topRoutes = (clone $transfers)
                ->leftJoin('branches as ob', 'asset_transfer_histories.origin_branch_id', '=', 'ob.id')
                ->leftJoin('branches as cb', 'asset_transfer_histories.current_branch_id', '=', 'cb.id')
                ->selectRaw('asset_transfer_histories.origin_branch_id, asset_transfer_histories.current_branch_id, ob.name as origin_name, cb.name as current_name, COUNT(*) c')
                ->whereBetween('transfer_date', [$this->from, $this->to])
                ->groupBy('asset_transfer_histories.origin_branch_id','asset_transfer_histories.current_branch_id','ob.name','cb.name')
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
                'suppliesMonthly',
                'transfersMonthly',
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
                'lowStockItems',
                'lowStockValueGap',
                'staleSkus',
                'staleSkusCount',
                'categoryLowCounts',
                'topOnHandSupplies'
            );
        });

        // Prepare simple arrays and labels expected by blade views (backwards-compatible)
        $labels = [];
        $assetsMonthly = [];
        $monthlyLineLabels = [];
        $monthlyLineValues = [];
        if (!empty($data['monthlyAssets'])) {
            // monthlyAssets contains objects with m and c where m is YYYY-MM
            $labels = $data['monthlyAssets']->pluck('m')->all();
            $assetsMonthly = $data['monthlyAssets']->pluck('c')->all();
            $monthlyLineLabels = $labels;
            $monthlyLineValues = $assetsMonthly;
        }

        // Use the monthly aggregates returned from the cached payload (computed inside closure)
        $suppliesMonthly = $data['suppliesMonthly'] ?? [];
        $transfersMonthly = $data['transfersMonthly'] ?? [];

        // Assets count and value by branch for analytics blades expecting different variable names
        $assetsCountByBranch = $data['assetsByBranch'] ?? collect();
        $assetsValueByBranch = $data['assetsByBranch'] ?? collect();

        // Alias category/value lists to names used in analytics view
        $assetsValueByCategory = $data['assetsByCategoryValue'] ?? collect();
        $suppliesValueByCategory = $data['topSupplyCategories'] ?? collect();

    // Merge prepared values into view data
        $viewData = array_merge($data, [
            'labels' => $labels,
            'assetsMonthly' => $assetsMonthly,
            'monthlyLineLabels' => $monthlyLineLabels,
            'monthlyLineValues' => $monthlyLineValues,
            'suppliesMonthly' => $suppliesMonthly,
            'transfersMonthly' => $transfersMonthly,
            'assetsCountByBranch' => $assetsCountByBranch,
            'assetsValueByBranch' => $assetsValueByBranch,
            'assetsValueByCategory' => $assetsValueByCategory,
            'suppliesValueByCategory' => $suppliesValueByCategory,
        ]);

        // Dispatch a lightweight browser event so the frontend can initialize or update charts
        // Convert collections to arrays where appropriate to ensure payload is JSON-serializable
        $payload = [
            'labels' => $labels,
            'assetsValues' => $monthlyLineValues,
            'suppliesMonthly' => $suppliesMonthly,
            'transfersMonthly' => $transfersMonthly,
            'stockOut' => (int) ($data['stockOut'] ?? 0),
            'stockLow' => (int) ($data['stockLow'] ?? 0),
            'stockOk' => (int) ($data['stockOk'] ?? 0),
            'assetsByStatus' => is_object($data['assetsByStatus']) ? $data['assetsByStatus']->toArray() : ($data['assetsByStatus'] ?? []),
        ];

        $this->dispatchBrowserEvent('dashboard:update', $payload);

        return view('livewire.admin.dashboard', $viewData);
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
