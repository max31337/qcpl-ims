<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
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
    protected $listeners = ['refreshAnalytics'];

    public function render()
    {
        $user = Auth::user();
        $driver = DB::connection()->getDriverName();

        // Simple scoping
        $assets = Asset::query();
        if (!($user->isMainBranch() && ($user->isAdmin() || $user->isObserver()))) {
            $assets->where('current_branch_id', $user->branch_id);
        }

        $key = sprintf('assets_analytics_v1:user_%d:branch_%d', $user->id, $user->branch_id);
        $data = Cache::remember($key, 600, function () use ($assets) {
            $end = Carbon::now()->endOfMonth();
            $start = Carbon::now()->startOfMonth()->subMonths(11);

            // Labels
            $labels = [];
            for ($i = 0; $i < 12; $i++) {
                $labels[] = (clone $start)->addMonths($i)->format('M Y');
            }

            $assetsMonthlyRaw = (clone $assets)
                ->selectRaw("DATE_FORMAT(assets.created_at, '%Y-%m') as m, COUNT(*) c")
                ->whereBetween('assets.created_at', [$start->toDateString().' 00:00:00', $end->toDateString().' 23:59:59'])
                ->groupBy('m')->get()->keyBy('m');

            $assetsMonthly = [];
            for ($i = 0; $i < 12; $i++) {
                $m = (clone $start)->addMonths($i)->format('Y-m');
                $assetsMonthly[] = (int)($assetsMonthlyRaw[$m]->c ?? 0);
            }

            $assetsByStatus = (clone $assets)->selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c','status');

            $assetsValueByCategory = (clone $assets)
                ->leftJoin('categories','assets.category_id','=','categories.id')
                ->selectRaw("COALESCE(categories.name,'Uncategorized') name, SUM(COALESCE(total_cost,0)) v")
                ->groupBy('categories.name')->orderByDesc('v')->limit(10)->get();

            $kpis = [
                'totalAssets' => (clone $assets)->count(),
                'assetsValue' => (clone $assets)->sum(DB::raw('COALESCE(assets.total_cost,0)')),
                'activeAssets' => (clone $assets)->where('status','active')->count(),
                'condemnedAssets' => (clone $assets)->where('status','condemned')->count(),
            ];

            return compact('kpis','labels','assetsMonthly','assetsByStatus','assetsValueByCategory');
        });

        if (is_array($data)) {
            return view('livewire.assets.analytics', array_merge([
                'kpis' => $data['kpis'] ?? [],
                'labels' => $data['labels'] ?? [],
            ], $data));
        }

        return view('livewire.assets.analytics');
    }

    public function refreshAnalytics(): void
    {
        $user = Auth::user();
        $key = sprintf('assets_analytics_v1:user_%d:branch_%d', $user->id, $user->branch_id);
        Cache::forget($key);
    }
}
