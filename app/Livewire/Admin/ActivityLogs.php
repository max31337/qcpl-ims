<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\ActivityLog;
use App\Models\User;
use App\Exports\ActivityLogsExport;
use Carbon\Carbon;

#[Layout('layouts.app')]
class ActivityLogs extends Component
{
    use WithPagination;

    public $search = '';
    public $userFilter = '';
    public $actionFilter = '';
    public $modelFilter = '';
    public $dateFromFilter = '';
    public $dateToFilter = '';
    public $perPage = 25;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'userFilter' => ['except' => ''],
        'actionFilter' => ['except' => ''],
        'modelFilter' => ['except' => ''],
        'dateFromFilter' => ['except' => ''],
        'dateToFilter' => ['except' => ''],
        'page' => ['except' => 1],
    ];
    
    // Modal properties
    public $showModal = false;
    public $selectedLog = null;
    public $showRawDetails = null; // ID of log for which to show raw details

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
        // Set default date range to last 30 days
        $this->dateToFilter = now()->format('Y-m-d');
        $this->dateFromFilter = now()->subDays(30)->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingUserFilter()
    {
        $this->resetPage();
    }

    public function updatingActionFilter()
    {
        $this->resetPage();
    }

    public function updatingModelFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFromFilter()
    {
        $this->resetPage();
    }

    public function updatingDateToFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->userFilter = '';
        $this->actionFilter = '';
        $this->modelFilter = '';
        $this->dateFromFilter = now()->subDays(30)->format('Y-m-d');
        $this->dateToFilter = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function exportLogs()
    {
        try {
            // Create the export with current filters
            $export = new ActivityLogsExport(
                user: auth()->user(),
                search: $this->search,
                userFilter: $this->userFilter,
                actionFilter: $this->actionFilter,
                modelFilter: $this->modelFilter,
                dateFrom: $this->dateFromFilter,
                dateTo: $this->dateToFilter,
            );

            // Log the export action
            ActivityLog::log('export', null, [], [], 'Exported activity logs with filters applied');

            // Generate and download the file
            return $export->download();

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to export activity logs: ' . $e->getMessage());
            return null;
        }
    }

    public function showDetails($logId)
    {
        $this->selectedLog = ActivityLog::with('user')->find($logId);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedLog = null;
        $this->showRawDetails = null;
    }

    public function toggleRawDetails($logId)
    {
        $this->showRawDetails = $this->showRawDetails === $logId ? null : $logId;
    }

    public function render()
    {
        $query = ActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($this->search) {
            $query->where(function($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function($userQuery) {
                      $userQuery->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->userFilter) {
            $query->where('user_id', $this->userFilter);
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
        
        // Get users for filter dropdown
        $users = User::orderBy('name')->get();

        // Get statistics
        $stats = $this->getActivityStats();

        return view('livewire.admin.activity-logs', [
            'logs' => $logs,
            'users' => $users,
            'stats' => $stats,
            'availableActions' => $this->availableActions,
            'availableModels' => $this->availableModels,
        ]);
    }

    private function getActivityStats()
    {
        $baseQuery = ActivityLog::query();

        // Apply date filters to stats
        if ($this->dateFromFilter) {
            $baseQuery->whereDate('created_at', '>=', $this->dateFromFilter);
        }
        if ($this->dateToFilter) {
            $baseQuery->whereDate('created_at', '<=', $this->dateToFilter);
        }

        return [
            'total_activities' => (clone $baseQuery)->count(),
            'unique_users' => (clone $baseQuery)->distinct('user_id')->count(),
            'recent_logins' => (clone $baseQuery)->where('action', 'login')->count(),
            'data_changes' => (clone $baseQuery)->whereIn('action', ['created', 'updated', 'deleted'])->count(),
        ];
    }
}
