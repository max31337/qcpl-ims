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

    public function mount()
    {
        $user = auth()->user();
    // Run each metric on a fresh builder to avoid leaking where/order/limit clauses.
    $this->lowStock = (int) Supply::forUser($user)->whereColumn('current_stock', '<=', 'min_stock')->count();

    $this->outOfStock = (int) Supply::forUser($user)->where('current_stock', '<=', 0)->count();

    $this->totalSkus = (int) Supply::forUser($user)->count();

    $this->onHandValue = number_format((float) (Supply::forUser($user)->select(DB::raw('SUM(unit_cost * current_stock) as value'))->value('value') ?? 0), 2);

    $this->recent = Supply::forUser($user)->latest('updated_at')->take(5)->get();
    }

    public function render()
    {
        return view('livewire.supplies.supply-analytics');
    }
}
