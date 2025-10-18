<?php

namespace App\Livewire\Supplies;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\SupplyRequest;
use App\Models\Supply;

class MyRequestForm extends Component
{
    public $items = [];
    public $quantities = [];
    public $availableSupplies = [];
    public $remarks = '';
    public $supplyDescriptions = [];

    public function mount()
    {
    $this->availableSupplies = Supply::all();
    $this->supplyDescriptions = Supply::pluck('description', 'id');
    }

    public function submit()
    {
        $this->validate([
            'items' => 'required|array|min:1',
            'quantities' => 'required|array',
            'remarks' => 'nullable|string',
        ]);

        $requestItems = [];
        foreach ($this->items as $supplyId) {
            $qty = $this->quantities[$supplyId] ?? 1;
            $requestItems[] = [
                'supply_id' => $supplyId,
                'quantity' => $qty,
            ];
        }

        SupplyRequest::create([
            'user_id' => Auth::id(),
            'items' => json_encode($requestItems),
            'status' => 'pending',
            'remarks' => $this->remarks,
        ]);

        session()->flash('success', 'Supply request submitted!');
        $this->items = [];
        $this->quantities = [];
        $this->remarks = '';
    }

    public function render()
    {
        return view('livewire.supplies.my-request-form', [
            'availableSupplies' => $this->availableSupplies,
            'supplyDescriptions' => $this->supplyDescriptions,
        ]);
    }
}
