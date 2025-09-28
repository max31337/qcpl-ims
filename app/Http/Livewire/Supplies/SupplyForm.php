<?php

namespace App\Http\Livewire\Supplies;

use Livewire\Component;
use App\Models\Supply;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class SupplyForm extends Component
{
    public $supplyId;
    public $supply;

    public function mount($id = null)
    {
        $this->supplyId = $id;
        $this->supply = $id ? Supply::find($id) : new Supply();
    }

    public function render()
    {
        $categories = Category::where('type','supply')->get();
        return view('livewire.supplies.supply-form', compact('categories'));
    }
}
