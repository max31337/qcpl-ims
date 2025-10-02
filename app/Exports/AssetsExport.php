<?php

namespace App\Exports;

use App\Models\Asset;
use App\Models\User;
use App\Services\ExcelReportService;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class AssetsExport
{
    public function __construct(
        protected User $user,
        protected ?int $branchId = null,
        protected ?int $divisionId = null,
        protected ?int $sectionId = null,
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
        $assets = $this->getAssets();
        $filename = $filename ?: 'assets-export-' . now()->format('Y-m-d-His') . '.xlsx';

        // Define column mapping for the Excel export
        $columns = [
            'property_number' => 'Item Code',
            'description' => 'Item Name', 
            'category.name' => 'Category',
            'quantity' => 'Quantity',
            'unit' => 'Unit',
            'location' => 'Location',
            'date_acquired' => 'Date Acquired',
            'status' => 'Status'
        ];

        // Create the Excel report service with enhanced formatting
        $excelService = new ExcelReportService(
            $assets,
            $columns,
            'Quezon City Public Library - Assets Report',
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
     * Get assets based on applied filters
     */
    protected function getAssets()
    {
        $q = Asset::with(['category','currentBranch','currentDivision','currentSection'])
            ->forUser($this->user);

        if ($this->branchId) $q->where('current_branch_id', $this->branchId);
        if ($this->divisionId) $q->where('current_division_id', $this->divisionId);
        if ($this->sectionId) $q->where('current_section_id', $this->sectionId);
        if ($this->categoryId) $q->where('category_id', $this->categoryId);
        if ($this->status) $q->where('status', $this->status);
        if ($this->search) {
            $s = "%{$this->search}%";
            $q->where(function($w) use ($s) {
                $w->where('property_number','like',$s)
                  ->orWhere('description','like',$s);
            });
        }
        if ($this->from) $q->whereDate('date_acquired', '>=', Carbon::parse($this->from));
        if ($this->to) $q->whereDate('date_acquired', '<=', Carbon::parse($this->to));

        return $q->orderBy('property_number')->get()->map(function ($asset) {
            // Transform the asset data for Excel export
            return [
                'property_number' => $asset->property_number,
                'description' => $asset->description,
                'category' => ['name' => optional($asset->category)->name ?? 'Uncategorized'],
                'quantity' => $asset->quantity,
                'unit' => $asset->unit ?? 'pcs',
                'location' => $this->getAssetLocation($asset),
                'date_acquired' => $asset->date_acquired,
                'status' => ucfirst($asset->status),
            ];
        });
    }

    /**
     * Get formatted location string for asset
     */
    protected function getAssetLocation($asset): string
    {
        $parts = array_filter([
            optional($asset->currentBranch)->name,
            optional($asset->currentDivision)->name,
            optional($asset->currentSection)->name,
        ]);
        return implode(' > ', $parts) ?: 'Not Assigned';
    }

    /**
     * Get summary of applied filters
     */
    protected function getAppliedFilters(): string
    {
        $filters = [];
        if ($this->branchId) $filters[] = "Branch: {$this->branchId}";
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
