<?php

namespace App\Livewire\Supplies;

use App\Models\Supply;
use App\Models\ActivityLog;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class StockAdjustment extends Component
{
    public int $supplyId;
    public int $quantity = 0; // positive to add, negative to deduct
    public string $remarks = '';

    public function mount($id)
    {
        $this->supplyId = (int) $id;
    }

    public function adjust()
    {
        $this->validate([
            'quantity' => ['required','integer','not_in:0','between:-100000,100000'],
            'remarks' => ['nullable','string','max:500'],
        ]);

        $user = auth()->user();
        $supply = Supply::forUser($user)->findOrFail($this->supplyId);

        $old = $supply->current_stock;
        $new = $old + $this->quantity;
        if ($new < 0) {
            $this->addError('quantity','Adjustment would make stock negative.');
            return;
        }

        $supply->current_stock = $new;
        $supply->last_updated = now();
        $supply->save();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'stock_adjustment',
            'model' => Supply::class,
            'model_id' => $supply->id,
            'old_values' => ['current_stock' => $old],
            'new_values' => ['current_stock' => $new, 'delta' => $this->quantity],
            'description' => trim('Stock adjusted. '.$this->remarks),
            'created_at' => now(),
        ]);

        session()->flash('success','Stock adjusted successfully.');
        return redirect()->route('supplies.index');
    }

    public function render()
    {
        $user = auth()->user();
        $supply = Supply::forUser($user)->with('category:id,name')->findOrFail($this->supplyId);
        return view('livewire.supplies.stock-adjustment', [
            'supply' => $supply,
        ]);
    }
}
