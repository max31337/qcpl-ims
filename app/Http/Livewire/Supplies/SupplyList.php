<?php

namespace App\Http\Livewire\Supplies;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Supply;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class SupplyList extends Component
{
    use WithPagination;

    public $search = '';
    public $category = '';
    public $status = '';

    protected $queryString = [
        'search' => ['except' => '', 'as' => 'q'],
        'category' => ['except' => ''],
        'status' => ['except' => '']
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $q = Supply::forUser($user)->with('category');

        if ($this->search) {
            $q->where(function($r){
                $r->where('supply_number','like','%'.$this->search.'%')
                  ->orWhere('description','like','%'.$this->search.'%')
                  ->orWhere('sku','like','%'.$this->search.'%');
            });
        }

        if ($this->category) {
            $q->where('category_id', $this->category);
        }

        if ($this->status) {
            $q->where('status', $this->status);
        }

        $supplies = $q->orderByDesc('last_updated')->paginate(12);
        $categories = Category::where('type','supply')->orderBy('name')->get();

        return view('livewire.supplies.supply-list', compact('supplies','categories'));
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->category = '';
        $this->status = '';
        $this->resetPage();
    }
}
