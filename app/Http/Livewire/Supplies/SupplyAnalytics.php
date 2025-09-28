<?php

namespace App\Http\Livewire\Supplies;

use Livewire\Component;
use App\Models\Supply;

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
        $query = Supply::forUser($user);

        $this->lowStock = $query->whereColumn('quantity', '<=', 'reorder_level')->count();
        $this->outOfStock = $query->where('quantity', '<=', 0)->count();
        $this->totalSkus = $query->count();
        $this->onHandValue = number_format($query->sum('unit_cost * quantity'), 2);
        $this->recent = $query->latest('updated_at')->take(5)->get();
    }

    public function render()
    {
        return view('livewire.supplies.supply-analytics');
    }
}
