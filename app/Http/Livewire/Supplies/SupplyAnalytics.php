<?php

namespace App\Http\Livewire\Supplies;

use Livewire\Component;
use App\Models\Supply;
use Illuminate\Support\Facades\DB;

class SupplyAnalytics extends Component
{
    public $lowStock;
    public $outOfStock;
    public $totalSkus;
    public $onHandValue;
    public $recent;
    // Chart datasets
    public $suppliesByCategory = [];
    public $monthlyAdds = [];
    public $stockHealth = [];

    public function mount()
    {
        $user = auth()->user();
    // Run each metric on a fresh builder to avoid leaking where/order/limit clauses.
    $this->lowStock = (int) Supply::forUser($user)->whereColumn('current_stock', '<=', 'min_stock')->count();

    $this->outOfStock = (int) Supply::forUser($user)->where('current_stock', '<=', 0)->count();

    $this->totalSkus = (int) Supply::forUser($user)->count();

    $this->onHandValue = number_format((float) (Supply::forUser($user)->select(DB::raw('SUM(unit_cost * current_stock) as value'))->value('value') ?? 0), 2);

    $this->recent = Supply::forUser($user)->latest('updated_at')->take(5)->get();

    // Supplies by category: count and total value per category
    $this->suppliesByCategory = Supply::forUser($user)
        ->select('category_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(unit_cost * current_stock) as value'))
        ->groupBy('category_id')
        ->with('category')
        ->get()
        ->map(function($r){
            return [
                'category' => $r->category->name ?? 'Uncategorized',
                'count' => (int) $r->count,
                'value' => (float) $r->value,
            ];
        })->values()->all();

    // Monthly additions (last 12 months)
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

    $this->monthlyAdds = $months->map(function($m) use ($monthly){
        return (int) ($monthly[$m] ?? 0);
    })->all();

    // Stock health: ok / low / out
    $ok = Supply::forUser($user)->whereColumn('current_stock', '>', 'min_stock')->count();
    $low = $this->lowStock;
    $out = $this->outOfStock;
    $this->stockHealth = [
        'ok' => (int) $ok,
        'low' => (int) $low,
        'out' => (int) $out,
    ];

    // Emit browser event with payload for charts
    $payload = [
        'categories' => array_column($this->suppliesByCategory, 'category'),
        'categoryCounts' => array_column($this->suppliesByCategory, 'count'),
        'categoryValues' => array_column($this->suppliesByCategory, 'value'),
        'monthlyLabels' => $months->map(fn($m) => 
            
            date('M Y', strtotime($m.'-01'))
        )->all(),
        'monthlyAdds' => $this->monthlyAdds,
        'stockHealth' => $this->stockHealth,
    ];

    $this->dispatchBrowserEvent('supplyAnalytics:update', $payload);
    }

    public function render()
    {
        return view('livewire.supplies.supply-analytics');
    }
}
