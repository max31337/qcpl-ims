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
    public $from;
    public $to;
    public $branchId = null;
    public $categoryId = null;
    public $topN = 10;

    public function mount(): void
    {
        $this->to = now()->toDateString();
        $this->from = now()->subDays(90)->toDateString();
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

        $assets = Asset::query()->forUser($user);
        $supplies = Supply::query()->forUser($user);
        $transfers = AssetTransferHistory::query();
        if (!($user->isMainBranch() && ($user->isAdmin() || $user->isObserver()))) {
            $transfers->where(function ($q) use ($user) {
                $q->where('origin_branch_id', $user->branch_id)
                  ->orWhere('current_branch_id', $user->branch_id);
            });
        }
        if ($this->branchId) {
            $assets->where('current_branch_id', $this->branchId);
            $supplies->where('branch_id', $this->branchId);
            $transfers->where(function ($q) {
                $q->where('origin_branch_id', $this->branchId)
                  ->orWhere('current_branch_id', $this->branchId);
            });
        }
        if ($this->categoryId) {
            $assets->where('category_id', $this->categoryId);
            $supplies->where('category_id', $this->categoryId);
        }

        $key = sprintf('admin_analytics_v1:%s:%s:%s:%s:%s', $this->from, $this->to, $this->branchId ?? 'all', $this->categoryId ?? 'all', $this->topN);
        $data = Cache::remember($key, 600, function () use ($assets, $supplies, $transfers, $monthExpr) {
            $fromTs = Carbon::parse($this->from)->startOfDay();
            $toTs = Carbon::parse($this->to)->endOfDay();

            // KPI summary
            $kpis = [
                'assetsTotal' => (clone $assets)->count(),
                'assetsValue' => (clone $assets)->sum(DB::raw('COALESCE(total_cost,0)')),
                'suppliesSkus' => (clone $supplies)->count(),
                'suppliesValue' => (clone $supplies)->selectRaw('SUM(current_stock*unit_cost) v')->value('v') ?? 0,
                'transfersInRange' => (clone $transfers)->whereBetween('transfer_date', [$this->from, $this->to])->count(),
            ];

            // Time series (12 months ending at selected 'to')
            $end = Carbon::parse($this->to)->endOfMonth();
            $start = (clone $end)->startOfMonth()->subMonths(11);
            $labels = [];
            for ($i=0;$i<12;$i++){ $labels[] = (clone $start)->addMonths($i)->format('M Y'); }

            $assetsMonthlyRaw = (clone $assets)
                ->selectRaw(str_replace('%s', 'created_at', $monthExpr)." as m, COUNT(*) c")
                ->whereBetween('created_at', [$start->toDateString().' 00:00:00', $end->toDateString().' 23:59:59'])
                ->groupBy('m')->get()->keyBy('m');
            $suppliesMonthlyRaw = (clone $supplies)
                ->selectRaw(str_replace('%s', 'created_at', $monthExpr)." as m, COUNT(*) c")
                ->whereBetween('created_at', [$start->toDateString().' 00:00:00', $end->toDateString().' 23:59:59'])
                ->groupBy('m')->get()->keyBy('m');
            $transfersMonthlyRaw = (clone $transfers)
                ->selectRaw(str_replace('%s', 'transfer_date', $monthExpr)." as m, COUNT(*) c")
                ->whereBetween('transfer_date', [$start->toDateString(), $end->toDateString()])
                ->groupBy('m')->get()->keyBy('m');

            $assetsMonthly = [];$suppliesMonthly = [];$transfersMonthly = [];$ymKeys = [];
            for ($i=0;$i<12;$i++){
                $m = (clone $start)->addMonths($i)->format('Y-m');
                $ymKeys[] = $m;
                $assetsMonthly[] = (int)($assetsMonthlyRaw[$m]->c ?? 0);
                $suppliesMonthly[] = (int)($suppliesMonthlyRaw[$m]->c ?? 0);
                $transfersMonthly[] = (int)($transfersMonthlyRaw[$m]->c ?? 0);
            }

            // Distributions
            $assetsByStatus = (clone $assets)->selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c','status');
            $assetsValueByCategory = (clone $assets)
                ->leftJoin('categories','assets.category_id','=','categories.id')
                ->selectRaw("COALESCE(categories.name,'Uncategorized') name, SUM(COALESCE(total_cost,0)) v")
                ->groupBy('categories.name')->orderByDesc('v')->limit($this->topN)->get();
            $assetsCountByBranch = (clone $assets)
                ->leftJoin('branches','assets.current_branch_id','=','branches.id')
                ->selectRaw("COALESCE(branches.name,'Unassigned') name, COUNT(*) c")
                ->groupBy('branches.name')->orderByDesc('c')->limit($this->topN)->get();
            $assetsValueByBranch = (clone $assets)
                ->leftJoin('branches','assets.current_branch_id','=','branches.id')
                ->selectRaw("COALESCE(branches.name,'Unassigned') name, SUM(COALESCE(total_cost,0)) v")
                ->groupBy('branches.name')->orderByDesc('v')->limit($this->topN)->get();

            $stockOut = (clone $supplies)->where('current_stock','<=',0)->count();
            $stockLow = (clone $supplies)->where('current_stock','>',0)->whereColumn('current_stock','<','min_stock')->count();
            $stockOk = (clone $supplies)->whereColumn('current_stock','>=','min_stock')->count();

            $topRoutes = (clone $transfers)
                ->leftJoin('branches as ob','asset_transfer_histories.origin_branch_id','=','ob.id')
                ->leftJoin('branches as cb','asset_transfer_histories.current_branch_id','=','cb.id')
                ->selectRaw('asset_transfer_histories.origin_branch_id, asset_transfer_histories.current_branch_id, ob.name as origin_name, cb.name as current_name, COUNT(*) c')
                ->whereBetween('transfer_date', [$this->from, $this->to])
                ->groupBy('asset_transfer_histories.origin_branch_id','asset_transfer_histories.current_branch_id','ob.name','cb.name')
                ->orderByDesc('c')->limit($this->topN)->get();

            $recentActivity = ActivityLog::orderByDesc('created_at')->limit(15)->get(['id','user_id','action','model','model_id','description','created_at']);

            return compact(
                'kpis','labels','assetsMonthly','suppliesMonthly','transfersMonthly','assetsByStatus','assetsValueByCategory','assetsCountByBranch','assetsValueByBranch','stockOut','stockLow','stockOk','topRoutes','recentActivity'
            );
        });

        return view('livewire.admin.analytics', array_merge($data, [
            'branches' => $this->getBranches(),
            'categories' => Category::orderBy('name')->get(['id','name']),
        ]));
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
