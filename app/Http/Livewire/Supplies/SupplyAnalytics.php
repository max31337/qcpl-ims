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
    // Extended analytics
    public $lowVsOutByCategory = [];
    public $topOnHandSkus = [];
    public $agingBuckets = [];

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

    // Low vs Out by category (top 6 by risk)
    $lowByCat = Supply::forUser($user)
        ->select('category_id', DB::raw('COUNT(*) as low_count'))
        ->whereColumn('current_stock', '<', 'min_stock')
        ->groupBy('category_id')
        ->pluck('low_count', 'category_id');
    $outByCat = Supply::forUser($user)
        ->select('category_id', DB::raw('COUNT(*) as out_count'))
        ->where('current_stock', '<=', 0)
        ->groupBy('category_id')
        ->pluck('out_count', 'category_id');
    // Derive names for categories present in either set
    $catIds = collect($lowByCat->keys())->merge($outByCat->keys())->unique()->values();
    $catNames = Supply::forUser($user)
        ->whereIn('category_id', $catIds)
        ->with('category:id,name')
        ->get(['category_id'])
        ->pluck('category.name', 'category_id');
    $riskCats = $catIds->map(function($id) use ($lowByCat, $outByCat, $catNames) {
        return [
            'id' => $id,
            'name' => $catNames[$id] ?? 'Uncategorized',
            'low' => (int) ($lowByCat[$id] ?? 0),
            'out' => (int) ($outByCat[$id] ?? 0),
            'risk' => (int) ($lowByCat[$id] ?? 0) + (int) ($outByCat[$id] ?? 0),
        ];
    })->sortByDesc('risk')->values()->take(6)->all();
    $this->lowVsOutByCategory = $riskCats;

    // Top SKUs by on-hand value
    $this->topOnHandSkus = Supply::forUser($user)
        ->select('id','supply_number','description','current_stock','unit_cost', DB::raw('(current_stock * unit_cost) as on_hand_value'))
        ->orderByDesc('on_hand_value')
        ->limit(10)
        ->get()
        ->map(fn($r) => [
            'label' => $r->description ?? $r->supply_number,
            'value' => (float) $r->on_hand_value,
        ])->values()->all();

    // Aging buckets: <=30, 31-60, 61-90, >90 days since last update
    $now = now();
    $this->agingBuckets = [
        'labels' => ['≤30d','31-60d','61-90d','>90d'],
        'counts' => [0,0,0,0],
    ];
    // Using a single pass query for performance
    $allAges = Supply::forUser($user)
        ->select(DB::raw('DATEDIFF(?, COALESCE(last_updated, updated_at)) as days_diff'))
        ->addBinding($now->toDateString())
        ->get()
        ->pluck('days_diff');
    foreach ($allAges as $d) {
        $d = (int) $d;
        if ($d <= 30) $this->agingBuckets['counts'][0]++;
        elseif ($d <= 60) $this->agingBuckets['counts'][1]++;
        elseif ($d <= 90) $this->agingBuckets['counts'][2]++;
        else $this->agingBuckets['counts'][3]++;
    }

    // Emit Livewire v3 browser event with named detail keys
    $this->dispatch('supplyAnalytics:update',
        categories: array_column($this->suppliesByCategory, 'category'),
        categoryCounts: array_column($this->suppliesByCategory, 'count'),
        categoryValues: array_column($this->suppliesByCategory, 'value'),
        monthlyLabels: $months->map(fn($m) => date('M Y', strtotime($m.'-01')))->all(),
        monthlyAdds: $this->monthlyAdds,
        stockHealth: $this->stockHealth,
        // Extended payload
        lowVsOutCategories: array_map(fn($r) => $r['name'], $this->lowVsOutByCategory),
        lowSeries: array_map(fn($r) => $r['low'], $this->lowVsOutByCategory),
        outSeries: array_map(fn($r) => $r['out'], $this->lowVsOutByCategory),
        topSkuLabels: array_map(fn($r) => $r['label'], $this->topOnHandSkus),
        topSkuValues: array_map(fn($r) => $r['value'], $this->topOnHandSkus),
        agingLabels: $this->agingBuckets['labels'],
        agingCounts: $this->agingBuckets['counts'],
    );
    }

    public function render()
    {
        return view('livewire.supplies.supply-analytics');
    }
}
