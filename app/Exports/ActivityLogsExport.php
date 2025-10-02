<?php

namespace App\Exports;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ExcelReportService;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ActivityLogsExport
{
    public function __construct(
        protected User $user,
        protected ?string $search = null,
        protected ?string $userFilter = null,
        protected ?string $actionFilter = null,
        protected ?string $modelFilter = null,
        protected ?string $dateFrom = null,
        protected ?string $dateTo = null,
    ) {}

    /**
     * Generate and download the Excel file
     */
    public function download(string $filename = null): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $logs = $this->getLogs();
        $filename = $filename ?: 'activity-logs-export-' . now()->format('Y-m-d-His') . '.xlsx';

        // Define column mapping for the Excel export
        $columns = [
            'id' => 'Log ID',
            'user.name' => 'User',
            'action' => 'Action',
            'model' => 'Model Type',
            'model_id' => 'Model ID',
            'description' => 'Description', 
            'ip_address' => 'IP Address',
            'user_agent' => 'User Agent',
            'created_at' => 'Date & Time'
        ];

        // Create the Excel report service with enhanced formatting
        $excelService = new ExcelReportService(
            $logs,
            $columns,
            'Quezon City Public Library - Activity Logs Report',
            [
                'exported_by' => $this->user->name,
                'exported_at' => now()->format('Y-m-d H:i:s'),
                'total_records' => $logs->count(),
                'filters_applied' => $this->getAppliedFilters(),
            ]
        );

        return Excel::download($excelService, $filename);
    }

    /**
     * Get filtered activity logs
     */
    protected function getLogs()
    {
        $query = ActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('action', 'like', '%' . $this->search . '%')
                  ->orWhere('model', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function ($userQuery) {
                      $userQuery->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Apply user filter
        if ($this->userFilter) {
            $query->whereHas('user', function ($userQuery) {
                $userQuery->where('name', 'like', '%' . $this->userFilter . '%');
            });
        }

        // Apply action filter
        if ($this->actionFilter) {
            $query->where('action', $this->actionFilter);
        }

        // Apply model filter
        if ($this->modelFilter) {
            $query->where('model', $this->modelFilter);
        }

        // Apply date range filter
        if ($this->dateFrom && $this->dateTo) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay(),
            ]);
        }

        return $query->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'user.name' => $log->user->name ?? 'System',
                'action' => ucfirst($log->action),
                'model' => $log->model ?? 'N/A',
                'model_id' => $log->model_id ?? 'N/A',
                'description' => $log->description,
                'ip_address' => $log->ip_address ?? 'N/A',
                'user_agent' => $log->user_agent ? substr($log->user_agent, 0, 100) . '...' : 'N/A',
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    /**
     * Get applied filters description
     */
    protected function getAppliedFilters(): string
    {
        $filters = [];

        if ($this->search) {
            $filters[] = "Search: {$this->search}";
        }

        if ($this->userFilter) {
            $filters[] = "User: {$this->userFilter}";
        }

        if ($this->actionFilter) {
            $filters[] = "Action: {$this->actionFilter}";
        }

        if ($this->modelFilter) {
            $filters[] = "Model: {$this->modelFilter}";
        }

        if ($this->dateFrom && $this->dateTo) {
            $filters[] = "Date Range: {$this->dateFrom} to {$this->dateTo}";
        }

        return $filters ? implode(', ', $filters) : 'No filters applied';
    }
}