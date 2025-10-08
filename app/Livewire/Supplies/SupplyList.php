<?php

namespace App\Livewire\Supplies;

use App\Models\Supply;
use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SupplyList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';
    #[Url]
    public string $status = '';
    #[Url]
    public string $category = '';
    #[Url]
    public string $branchFilter = '';
    #[Url]
    public bool $showMainLibraryOnly = false;

    public function updating($name, $value)
    {
        if (in_array($name, ['search','status','category','branchFilter','showMainLibraryOnly'])) {
            $this->resetPage();
        }
    }

    public function updatingShowMainLibraryOnly()
    {
        $this->resetPage();
    }

    public function toggleScope()
    {
        $this->showMainLibraryOnly = !$this->showMainLibraryOnly;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->status = '';
        $this->category = '';
        $this->branchFilter = '';
        $this->showMainLibraryOnly = false;
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();

        $q = Supply::query()
            ->with('category:id,name')
            ->when($this->search, fn($qq) => $qq->where(function($w){
                $w->where('supply_number','like','%'.$this->search.'%')
                  ->orWhere('description','like','%'.$this->search.'%');
            }))
            ->when($this->status !== '', fn($qq) => $qq->where('status', $this->status))
            ->when($this->category !== '', fn($qq) => $qq->where('category_id', $this->category))
            ->when($this->branchFilter !== '', fn($qq) => $qq->where('branch_id', $this->branchFilter));

        // Handle branch scoping based on user role and toggle
        if ($user->isSupplyOfficer() && $user->isMainBranch() && $this->showMainLibraryOnly) {
            $q->where('branch_id', $user->branch_id);
        } elseif (!$user->isMainBranch() || !($user->isAdmin() || $user->isObserver() || $user->isSupplyOfficer())) {
            // Non-main branch users or users without global permissions see only their branch
            $q->where('branch_id', $user->branch_id);
        }

        $q->orderByDesc('last_updated');

        return view('livewire.supplies.supply-list', [
            'supplies' => $q->paginate(12),
            'categories' => Category::where('type','supply')->orderBy('name')->get(['id','name']),
            'branches' => $this->getBranches(),
        ]);
    }

    public function getBranches()
    {
        $user = auth()->user();
        if ($user->isMainBranch() && ($user->isAdmin() || $user->isObserver() || $user->isSupplyOfficer())) {
            return \App\Models\Branch::where('is_active', true)->orderBy('name')->get();
        }
        return \App\Models\Branch::where('id', $user->branch_id)->get();
    }
}
