<?php

namespace App\Exports;

use App\Models\Supply;
use App\Models\User;
use App\Services\ExcelReportService;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class SuppliesExport
{
    public function __construct(
        protected User $user,
        protected ?int $categoryId = null,
        protected ?string $status = null,
        protected ?string $search = null,
        protected ?string $from = null,
        protected ?string $to = null,
    ) {}

    /**
     * Generate and download the Excel file
     */
    public function download(string $filename = null): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $supplies = $this->getSupplies();
        $filename = $filename ?: 'supplies-export-' . now()->format('Y-m-d-His') . '.xlsx';

        // Define column mapping for the Excel export following the requirements
        $columns = [
            'supply_number' => 'Item Code',
            'description' => 'Item Name',
            'category.name' => 'Category', 
            'current_stock' => 'Quantity',
            'unit' => 'Unit',
            'location' => 'Location',
            'date_acquired' => 'Date Acquired',
            'status' => 'Status'
        ];

        // Create the Excel report service with enhanced formatting
        $excelService = new ExcelReportService(
            $supplies,
            $columns,
            'Quezon City Public Library - Supplies Report',
            [
                'exported_by' => $this->user->name,
                'department' => optional($this->user->currentBranch)->name ?? 'Main Branch',
                'date_exported' => now()->format('F j, Y g:i A'),
                'filters' => $this->getAppliedFilters()
            ]
        );

        return Excel::download($excelService, $filename);
    }

    /**
     * Get supplies based on applied filters
     */
    protected function getSupplies()
    {
        $q = Supply::with(['category'])
            ->forUser($this->user);

        if ($this->categoryId) $q->where('category_id', $this->categoryId);
        if ($this->status) $q->where('status', $this->status);
        if ($this->search) {
            $s = "%{$this->search}%";
            $q->where(function($w) use ($s) {
                $w->where('supply_number','like',$s)
                  ->orWhere('description','like',$s)
                  ->orWhere('sku','like',$s);
            });
        }
        if ($this->from) $q->whereDate('created_at', '>=', Carbon::parse($this->from));
        if ($this->to) $q->whereDate('created_at', '<=', Carbon::parse($this->to));

        return $q->orderBy('supply_number')->get()->map(function ($supply) {
            // Transform the supply data for Excel export
            return [
                'supply_number' => $supply->supply_number,
                'description' => $supply->description . ($supply->sku ? ' (SKU: ' . $supply->sku . ')' : ''),
                'category' => ['name' => optional($supply->category)->name ?? 'Uncategorized'],
                'current_stock' => $supply->current_stock,
                'unit' => $supply->unit ?? 'pcs',
                'location' => $this->getSupplyLocation($supply),
                'date_acquired' => $supply->created_at, // Using created_at as date acquired
                'status' => $this->getSupplyStatusBadge($supply),
            ];
        });
    }

    /**
     * Get formatted location string for supply
     * For supplies, location would typically be the storage location or branch
     */
    protected function getSupplyLocation($supply): string
    {
        // If supply has a specific location field, use it
        if (isset($supply->location) && $supply->location) {
            return $supply->location;
        }
        
        // Otherwise use branch information
        return optional($this->user->currentBranch)->name ?? 'Main Storage';
    }

    /**
     * Get supply status with stock level indicator
     */
    protected function getSupplyStatusBadge($supply): string
    {
        $status = ucfirst($supply->status);
        
        if ($supply->current_stock <= 0) {
            return $status . ' (OUT OF STOCK)';
        } elseif ($supply->current_stock < $supply->min_stock) {
            return $status . ' (LOW STOCK)';
        }
        
        return $status;
    }

    /**
     * Get summary of applied filters
     */
    protected function getAppliedFilters(): string
    {
        $filters = [];
        if ($this->categoryId) $filters[] = "Category: {$this->categoryId}";
        if ($this->status) $filters[] = "Status: {$this->status}";
        if ($this->search) $filters[] = "Search: {$this->search}";
        if ($this->from || $this->to) {
            $dateRange = ($this->from ?: 'earliest') . ' to ' . ($this->to ?: 'latest');
            $filters[] = "Date: {$dateRange}";
        }
        return implode(', ', $filters) ?: 'None';
    }
}