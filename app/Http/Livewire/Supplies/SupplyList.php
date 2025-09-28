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
    public $categoryFilter = '';
    public $statusFilter = '';

    protected $queryString = ['search','categoryFilter','statusFilter'];

    public function updatingSearch()
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
                  ->orWhere('description','like','%'.$this->search.'%');
            });
        }

        if ($this->categoryFilter) {
            $q->where('category_id', $this->categoryFilter);
        }

        if ($this->statusFilter) {
            $q->where('status', $this->statusFilter);
        }

        $supplies = $q->orderByDesc('last_updated')->paginate(12);
        $categories = Category::where('type','supply')->orderBy('name')->get();

        return view('livewire.supplies.supply-list', compact('supplies','categories'));
    }
}
