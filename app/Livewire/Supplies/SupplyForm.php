<?php

namespace App\Livewire\Supplies;

use App\Models\Supply;
use App\Models\Category;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SupplyForm extends Component
{
    public ?int $supplyId = null;
    public string $supply_number = '';
    public string $description = '';
    public ?int $category_id = null;
    public int $current_stock = 0;
    public int $min_stock = 0;
    public float $unit_cost = 0.0;
    public string $status = 'active';
    public ?int $branch_id = null;

    protected function rules()
    {
        return [
            'supply_number' => ['required', 'string', Rule::unique('supplies','supply_number')->ignore($this->supplyId)],
            'description' => ['required','string','max:500'],
            'category_id' => ['required','exists:categories,id'],
            'current_stock' => ['required','integer','min:0'],
            'min_stock' => ['required','integer','min:0'],
            'unit_cost' => ['required','numeric','min:0'],
            'status' => ['required', Rule::in(['active','inactive'])],
            'branch_id' => ['required','exists:branches,id'],
        ];
    }

    public function mount($id = null)
    {
        $user = auth()->user();
        if ($id) {
            $s = Supply::forUser($user)->findOrFail($id);
            $this->supplyId = $s->id;
            $this->supply_number = $s->supply_number;
            $this->description = $s->description;
            $this->category_id = $s->category_id;
            $this->current_stock = $s->current_stock;
            $this->min_stock = $s->min_stock;
            $this->unit_cost = (float) $s->unit_cost;
            $this->status = $s->status;
            $this->branch_id = $s->branch_id;
        } else {
            $this->supply_number = Supply::generateSupplyNumber();
            $this->branch_id = $user->branch_id;
            $this->status = 'active';
        }
    }

    public function save()
    {
        $user = auth()->user();
        $data = $this->validate();

        DB::transaction(function () use ($user, $data) {
            if ($this->supplyId) {
                $supply = Supply::forUser($user)->findOrFail($this->supplyId);
                $supply->fill($data);
                $supply->last_updated = now();
                $supply->save();
            } else {
                $data['created_by'] = $user->id;
                $data['last_updated'] = now();
                Supply::create($data);
                // Prepare new number for next create
                $this->supply_number = Supply::generateSupplyNumber();
                $this->reset(['description','category_id','current_stock','min_stock','unit_cost']);
                $this->status = 'active';
            }
        });

        session()->flash('success','Supply saved successfully.');
        return redirect()->route('supplies.index');
    }

    public function render()
    {
        return view('livewire.supplies.supply-form', [
            'categories' => Category::where('type','supply')->orderBy('name')->get(['id','name']),
            'branches' => Branch::orderBy('name')->get(['id','name']),
        ]);
    }
}
