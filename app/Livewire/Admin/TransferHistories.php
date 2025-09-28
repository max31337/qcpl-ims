<?php

namespace App\Livewire\Admin;

use App\Models\AssetTransferHistory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

#[Layout('layouts.app')]
class TransferHistories extends Component
{
    use WithPagination;

    public $search = '';
    public $fromDate = '';
    public $toDate = '';
    public $sortBy = 'transfer_date';
    public $sortDirection = 'desc';
    public $perPage = 25;

    public function mount()
    {
        // Default to last 30 days
        $this->toDate = now()->format('Y-m-d');
        $this->fromDate = now()->subDays(30)->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFromDate()
    {
        $this->resetPage();
    }

    public function updatingToDate()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        
        $query = AssetTransferHistory::with([
            'asset:id,property_number,description',
            'originBranch:id,name',
            'currentBranch:id,name',
            'transferredBy:id,name'
        ]);

        // Apply user scoping
        if (!($user->isMainBranch() && ($user->isAdmin() || $user->isObserver()))) {
            $query->where(function ($q) use ($user) {
                $q->where('origin_branch_id', $user->branch_id)
                  ->orWhere('current_branch_id', $user->branch_id);
            });
        }

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('asset', function ($assetQuery) {
                    $assetQuery->where('property_number', 'like', '%' . $this->search . '%')
                              ->orWhere('description', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('originBranch', function ($branchQuery) {
                    $branchQuery->where('name', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('currentBranch', function ($branchQuery) {
                    $branchQuery->where('name', 'like', '%' . $this->search . '%');
                })
                ->orWhere('remarks', 'like', '%' . $this->search . '%');
            });
        }

        // Apply date filters
        if ($this->fromDate) {
            $query->whereDate('transfer_date', '>=', $this->fromDate);
        }
        if ($this->toDate) {
            $query->whereDate('transfer_date', '<=', $this->toDate);
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        $transfers = $query->paginate($this->perPage);

        return view('livewire.admin.transfer-histories', compact('transfers'));
    }
}