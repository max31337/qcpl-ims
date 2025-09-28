<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetTransferHistory;
use App\Models\Supply;
use App\Models\Branch;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

#[Layout('layouts.app')]
class Analytics extends Component
{
    public $period = 'alltime'; // alltime, yearly, or monthly
    public $selectedYear;

    public function mount(): void
    {
        $this->selectedYear = now()->year;
    }

    public function render()
    {
        $user = Auth::user();
        $driver = DB::connection()->getDriverName();
        $monthExpr = match ($driver) {
            'mysql', 'mariadb' => "DATE_FORMAT(%s, '%Y-%m')",
            'pgsql' => "TO_CHAR(%s, 'YYYY-MM')",
            'sqlite' => "STRFTIME('%Y-%m', %s)",
            'sqlsrv' => "FORMAT(%s, 'yyyy-MM')",
            default => "DATE_FORMAT(%s, '%Y-%m')",
        };

        // Simple scoping - similar to dashboard
        $assets = Asset::query();
        $supplies = Supply::query();
        $transfers = AssetTransferHistory::query();
        
        // Basic user scoping
        if (!($user->isMainBranch() && ($user->isAdmin() || $user->isObserver()))) {
            $assets->where('current_branch_id', $user->branch_id);
            $supplies->where('branch_id', $user->branch_id);
            $transfers->where(function ($q) use ($user) {
                $q->where('origin_branch_id', $user->branch_id)
                  ->orWhere('current_branch_id', $user->branch_id);
            });
        }

        $key = sprintf('admin_analytics_v5:%s:%s', $this->period, $this->selectedYear);
        $data = Cache::remember($key, 600, function () use ($assets, $supplies, $transfers, $monthExpr) {
            // Set date range and data scope based on period
            if ($this->period === 'alltime') {
                // All time data - no date filtering
                $assetsInRange = clone $assets;
                $suppliesInRange = clone $supplies;
                $kpis = [
                    'assetsTotal' => (clone $assetsInRange)->count(),
                    'assetsValue' => (clone $assetsInRange)->sum(DB::raw('COALESCE(assets.total_cost,0)')),
                    'suppliesSkus' => (clone $suppliesInRange)->count(),
                    'suppliesValue' => (clone $supplies)->selectRaw('SUM(current_stock*unit_cost) v')->value('v') ?? 0,
                    'transfersInRange' => (clone $transfers)->count(),
                ];
                // Time series - last 12 months
                $end = Carbon::now()->endOfMonth();
                $start = Carbon::now()->startOfMonth()->subMonths(11);
                $labels = [];
                for ($i = 0; $i < 12; $i++) {
                    $labels[] = (clone $start)->addMonths($i)->format('M Y');
                }
            } elseif ($this->period === 'yearly') {
                // Yearly data for selected year
                $assetsInRange = clone $assets;
                $suppliesInRange = clone $supplies;
                $kpis = [
                    'assetsTotal' => (clone $assetsInRange)->whereYear('assets.created_at', $this->selectedYear)->count(),
                    'assetsValue' => (clone $assetsInRange)->whereYear('assets.created_at', $this->selectedYear)->sum(DB::raw('COALESCE(assets.total_cost,0)')),
                    'suppliesSkus' => (clone $suppliesInRange)->whereYear('supplies.created_at', $this->selectedYear)->count(),
                    'suppliesValue' => (clone $supplies)->selectRaw('SUM(current_stock*unit_cost) v')->value('v') ?? 0,
                    'transfersInRange' => (clone $transfers)->whereYear('transfer_date', $this->selectedYear)->count(),
                ];
                // Time series - 5 years ending at selected year
                $start = Carbon::create($this->selectedYear - 4)->startOfYear();
                $end = Carbon::create($this->selectedYear)->endOfYear();
                $labels = [];
                for ($year = $this->selectedYear - 4; $year <= $this->selectedYear; $year++) {
                    $labels[] = (string) $year;
                }
            } else {
                // Monthly data for selected year
                $assetsInRange = clone $assets;
                $suppliesInRange = clone $supplies;
                $kpis = [
                    'assetsTotal' => (clone $assetsInRange)->whereYear('assets.created_at', $this->selectedYear)->count(),
                    'assetsValue' => (clone $assetsInRange)->whereYear('assets.created_at', $this->selectedYear)->sum(DB::raw('COALESCE(assets.total_cost,0)')),
                    'suppliesSkus' => (clone $suppliesInRange)->whereYear('supplies.created_at', $this->selectedYear)->count(),
                    'suppliesValue' => (clone $supplies)->selectRaw('SUM(current_stock*unit_cost) v')->value('v') ?? 0,
                    'transfersInRange' => (clone $transfers)->whereYear('transfer_date', $this->selectedYear)->count(),
                ];
                // Time series - 12 months of selected year
                $start = Carbon::create($this->selectedYear)->startOfYear();
                $end = Carbon::create($this->selectedYear)->endOfYear();
                $labels = [];
                for ($i = 1; $i <= 12; $i++) {
                    $labels[] = Carbon::create($this->selectedYear, $i)->format('M Y');
                }
            }

            // Generate time series data based on period
            if ($this->period === 'yearly') {
                // Yearly data for 5 years
                $assetsYearlyRaw = (clone $assets)
                    ->selectRaw("YEAR(assets.created_at) as y, COUNT(*) c")
                    ->whereBetween('assets.created_at', [$start->toDateString().' 00:00:00', $end->toDateString().' 23:59:59'])
                    ->groupBy('y')->get()->keyBy('y');
                $suppliesYearlyRaw = (clone $supplies)
                    ->selectRaw("YEAR(supplies.created_at) as y, COUNT(*) c")
                    ->whereBetween('supplies.created_at', [$start->toDateString().' 00:00:00', $end->toDateString().' 23:59:59'])
                    ->groupBy('y')->get()->keyBy('y');
                $transfersYearlyRaw = (clone $transfers)
                    ->selectRaw("YEAR(transfer_date) as y, COUNT(*) c")
                    ->whereBetween('transfer_date', [$start->toDateString(), $end->toDateString()])
                    ->groupBy('y')->get()->keyBy('y');

                $assetsMonthly = [];$suppliesMonthly = [];$transfersMonthly = [];
                for ($year = $this->selectedYear - 4; $year <= $this->selectedYear; $year++) {
                    $assetsMonthly[] = (int)($assetsYearlyRaw[$year]->c ?? 0);
                    $suppliesMonthly[] = (int)($suppliesYearlyRaw[$year]->c ?? 0);
                    $transfersMonthly[] = (int)($transfersYearlyRaw[$year]->c ?? 0);
                }
            } else {
                // Monthly data (for both alltime and monthly period)
                $assetsMonthlyRaw = (clone $assets)
                    ->selectRaw("DATE_FORMAT(assets.created_at, '%Y-%m') as m, COUNT(*) c")
                    ->whereBetween('assets.created_at', [$start->toDateString().' 00:00:00', $end->toDateString().' 23:59:59'])
                    ->groupBy('m')->get()->keyBy('m');
                $suppliesMonthlyRaw = (clone $supplies)
                    ->selectRaw("DATE_FORMAT(supplies.created_at, '%Y-%m') as m, COUNT(*) c")
                    ->whereBetween('supplies.created_at', [$start->toDateString().' 00:00:00', $end->toDateString().' 23:59:59'])
                    ->groupBy('m')->get()->keyBy('m');
                $transfersMonthlyRaw = (clone $transfers)
                    ->selectRaw("DATE_FORMAT(transfer_date, '%Y-%m') as m, COUNT(*) c")
                    ->whereBetween('transfer_date', [$start->toDateString(), $end->toDateString()])
                    ->groupBy('m')->get()->keyBy('m');

                $assetsMonthly = [];$suppliesMonthly = [];$transfersMonthly = [];
                $monthCount = $this->period === 'alltime' ? 12 : 12;
                for ($i = 0; $i < $monthCount; $i++) {
                    $m = (clone $start)->addMonths($i)->format('Y-m');
                    $assetsMonthly[] = (int)($assetsMonthlyRaw[$m]->c ?? 0);
                    $suppliesMonthly[] = (int)($suppliesMonthlyRaw[$m]->c ?? 0);
                    $transfersMonthly[] = (int)($transfersMonthlyRaw[$m]->c ?? 0);
                }
            }

            // Distributions
            $assetsByStatus = (clone $assetsInRange)->selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c','status');
            $assetsValueByCategory = (clone $assetsInRange)
                ->leftJoin('categories','assets.category_id','=','categories.id')
                ->selectRaw("COALESCE(categories.name,'Uncategorized') name, SUM(COALESCE(total_cost,0)) v")
                ->groupBy('categories.name')->orderByDesc('v')->limit(10)->get();
            $suppliesValueByCategory = (clone $suppliesInRange)
                ->leftJoin('categories','supplies.category_id','=','categories.id')
                ->selectRaw("COALESCE(categories.name,'Uncategorized') name, SUM(current_stock*unit_cost) v")
                ->groupBy('categories.name')->orderByDesc('v')->limit(10)->get();
            $assetsCountByBranch = (clone $assetsInRange)
                ->leftJoin('branches','assets.current_branch_id','=','branches.id')
                ->selectRaw("COALESCE(branches.name,'Unassigned') name, COUNT(*) c")
                ->groupBy('branches.name')->orderByDesc('c')->limit(10)->get();
            $assetsValueByBranch = (clone $assetsInRange)
                ->leftJoin('branches','assets.current_branch_id','=','branches.id')
                ->selectRaw("COALESCE(branches.name,'Unassigned') name, SUM(COALESCE(total_cost,0)) v")
                ->groupBy('branches.name')->orderByDesc('v')->limit(10)->get();

            // Stock health is a snapshot; apply branch/category scope, but not date range
            $stockOut = (clone $supplies)->where('current_stock','<=',0)->count();
            $stockLow = (clone $supplies)->where('current_stock','>',0)->whereColumn('current_stock','<','min_stock')->count();
            $stockOk = (clone $supplies)->whereColumn('current_stock','>=','min_stock')->count();

            $topRoutes = (clone $transfers)
                ->leftJoin('branches as ob','asset_transfer_histories.origin_branch_id','=','ob.id')
                ->leftJoin('branches as cb','asset_transfer_histories.current_branch_id','=','cb.id')
                ->selectRaw('asset_transfer_histories.origin_branch_id, asset_transfer_histories.current_branch_id, ob.name as origin_name, cb.name as current_name, COUNT(*) c')
                ->groupBy('asset_transfer_histories.origin_branch_id','asset_transfer_histories.current_branch_id','ob.name','cb.name')
                ->orderByDesc('c')->limit(10)->get();

            return compact(
                'kpis','labels','assetsMonthly','suppliesMonthly','transfersMonthly','assetsByStatus','assetsValueByCategory','suppliesValueByCategory','assetsCountByBranch','assetsValueByBranch','stockOut','stockLow','stockOk','topRoutes'
            );
        });

        return view('livewire.admin.analytics', $data);
    }

    protected function getBranches()
    {
        $user = Auth::user();
        if ($user->isMainBranch() && ($user->isAdmin() || $user->isObserver())) {
            return Branch::orderBy('name')->get(['id','name']);
        }
        return Branch::where('id', $user->branch_id)->get(['id','name']);
    }
}
