<?php

namespace App\Http\Livewire\Supplies;

use Livewire\Component;
use App\Models\Supply;

class StockAdjustment extends Component
{
    public $supplyId;
    public $supply;
    public $quantity;
    public $remarks;

    public function mount($id)
    {
        $this->supplyId = $id;
        $this->supply = Supply::find($id);
    }

    public function render()
    {
        return view('livewire.supplies.stock-adjustment');
    }
}
