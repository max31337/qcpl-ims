<?php

namespace App\Http\Livewire\Supplies;

use Livewire\Component;
use App\Models\Supply;
use Illuminate\Support\Facades\DB;

class SupplyAnalyticsLite extends Component
{
    public function mount(): void
    {
        $user = auth()->user();

        // Supplies by category (counts and values) — align to SupplyAnalytics
        $byCategory = Supply::forUser($user)
            ->select('category_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(unit_cost * current_stock) as value'))
            ->groupBy('category_id')
            ->with('category:id,name')
            ->get();
        $categories = $byCategory->map(fn($r) => $r->category->name ?? 'Uncategorized')->values()->all();
        $categoryCounts = $byCategory->map(fn($r) => (int) $r->count)->values()->all();
        $categoryValues = $byCategory->map(fn($r) => (float) $r->value)->values()->all();

        // Monthly additions (last 12 months) — same as SupplyAnalytics
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $dt = now()->subMonths($i);
            $months->push($dt->format('Y-m'));
        }
        $monthly = Supply::forUser($user)
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"), DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [now()->subMonths(11)->startOfMonth(), now()->endOfMonth()])
            ->groupBy('ym')
            ->pluck('total', 'ym')
            ->all();
        $monthlyAdds = $months->map(fn($m) => (int) ($monthly[$m] ?? 0))->all();

        // Stock health — match SupplyAnalytics buckets
        $ok = Supply::forUser($user)->whereColumn('current_stock', '>=', 'min_stock')->count();
        $low = Supply::forUser($user)
            ->where('current_stock', '>', 0)
            ->whereColumn('current_stock', '<', 'min_stock')
            ->count();
        $out = Supply::forUser($user)->where('current_stock', '<=', 0)->count();
        $stockHealth = [ 'ok' => (int) $ok, 'low' => (int) $low, 'out' => (int) $out ];

        // Emit the same event used by analytics and the supplies charts module
        $this->dispatch('supplyAnalytics:update',
            categories: $categories,
            categoryCounts: $categoryCounts,
            categoryValues: $categoryValues,
            monthlyLabels: $months->map(fn($m) => date('M Y', strtotime($m.'-01')))->all(),
            monthlyAdds: $monthlyAdds,
            stockHealth: $stockHealth,
        );
    }

    public function render()
    {
        return view('livewire.supplies.supply-analytics-lite');
    }
}
