<?php

namespace App\Livewire\Activity;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\ActivityLog;

#[Layout('layouts.app')]
class MyActivity extends Component
{
    use WithPagination;

    public $search = '';
    public $actionFilter = '';
    public $modelFilter = '';
    public $dateFromFilter = '';
    public $dateToFilter = '';
    public $perPage = 25;

    public $showModal = false;
    public $selectedLog = null;

    // Available filter options
    public $availableActions = [
        'created' => 'Created',
        'updated' => 'Updated', 
        'deleted' => 'Deleted',
        'transferred' => 'Transferred',
        'login' => 'Login',
        'logout' => 'Logout',
        'password_changed' => 'Password Changed',
        'mfa_enabled' => 'MFA Enabled',
        'mfa_disabled' => 'MFA Disabled',
        'profile_updated' => 'Profile Updated',
        'export' => 'Export',
        'import' => 'Import',
    ];

    public $availableModels = [
        'Asset' => 'Assets',
        'Supply' => 'Supplies',
        'User' => 'Users',
        'Branch' => 'Branches',
        'Category' => 'Categories',
        'AssetTransferHistory' => 'Asset Transfers',
    ];

    public function mount()
    {
        $this->dateToFilter = now()->format('Y-m-d');
        $this->dateFromFilter = now()->subDays(30)->format('Y-m-d');
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingActionFilter() { $this->resetPage(); }
    public function updatingModelFilter() { $this->resetPage(); }
    public function updatingDateFromFilter() { $this->resetPage(); }
    public function updatingDateToFilter() { $this->resetPage(); }

    public function clearFilters()
    {
        $this->search = '';
        $this->actionFilter = '';
        $this->modelFilter = '';
        $this->dateFromFilter = now()->subDays(30)->format('Y-m-d');
        $this->dateToFilter = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function showDetails($id)
    {
        $this->selectedLog = ActivityLog::with('user')->find($id);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedLog = null;
    }

    public function render()
    {
        $userId = auth()->id();

        $query = ActivityLog::with('user')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where('description', 'like', '%' . $this->search . '%');
        }

        if ($this->actionFilter) {
            $query->where('action', $this->actionFilter);
        }

        if ($this->modelFilter) {
            $query->where('model', $this->modelFilter);
        }

        if ($this->dateFromFilter) {
            $query->whereDate('created_at', '>=', $this->dateFromFilter);
        }

        if ($this->dateToFilter) {
            $query->whereDate('created_at', '<=', $this->dateToFilter);
        }

        $logs = $query->paginate($this->perPage);

        return view('livewire.activity.my-activity', [
            'logs' => $logs,
            'availableActions' => $this->availableActions,
            'availableModels' => $this->availableModels,
        ]);
    }
}
