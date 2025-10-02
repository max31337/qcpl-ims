<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Branch;
use App\Models\Division;
use App\Models\Section;
use App\Exports\AssetsExport;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('layouts.app')]
class AssetReports extends Component
{
    public $categoryFilter = '';
    public $statusFilter = '';
    public $branchFilter = '';
    public $divisionFilter = '';
    public $sectionFilter = '';
    public $dateFrom = '';
    public $dateTo = '';

    public function mount()
    {
        $this->dateFrom = now()->startOfYear()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    /**
     * Export assets to Excel with enhanced formatting
     * 
     * This method uses the new ExcelReportService to generate a professionally 
     * formatted Excel file with headers, styling, and metadata
     */
    public function exportAssets()
    {
        $export = new AssetsExport(
            auth()->user(),
            $this->branchFilter ?: null,
            $this->divisionFilter ?: null,
            $this->sectionFilter ?: null,
            $this->categoryFilter ?: null,
            $this->statusFilter ?: null,
            null, // search parameter
            $this->dateFrom ?: null,
            $this->dateTo ?: null
        );

        return $export->download();
    }

    // Export: PDF via DomPDF
    public function exportPdf()
    {
        $assets = $this->buildQuery()->with(['category','currentBranch','currentDivision','currentSection'])
            ->orderBy('property_number')->get();

        $pdf = Pdf::loadView('reports.assets-pdf', [
            'assets' => $assets,
            'from' => $this->dateFrom,
            'to' => $this->dateTo,
            'noDataMessage' => $this->noDataMessage(),
            'filters' => $this->currentFiltersSummary(),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'assets-report-'.now()->format('Ymd-His').'.pdf', [
            'Content-Type' => 'application/pdf'
        ]);
    }

    public function updatedBranchFilter()
    {
        $this->divisionFilter = '';
        $this->sectionFilter = '';
    }

    public function updatedDivisionFilter()
    {
        $this->sectionFilter = '';
    }

    public function getDivisionsProperty()
    {
        if (!$this->branchFilter) return collect();
        return Division::where('branch_id', $this->branchFilter)->orderBy('name')->get();
    }

    public function getSectionsProperty()
    {
        if (!$this->divisionFilter) return collect();
        return Section::where('division_id', $this->divisionFilter)->orderBy('name')->get();
    }

    private function buildQuery()
    {
        $query = Asset::forUser(auth()->user());

        if ($this->categoryFilter) $query->where('category_id', $this->categoryFilter);
        if ($this->statusFilter) $query->where('status', $this->statusFilter);
        if ($this->branchFilter) $query->where('current_branch_id', $this->branchFilter);
        if ($this->divisionFilter) $query->where('current_division_id', $this->divisionFilter);
        if ($this->sectionFilter) $query->where('current_section_id', $this->sectionFilter);
        if ($this->dateFrom) $query->whereDate('date_acquired', '>=', $this->dateFrom);
        if ($this->dateTo) $query->whereDate('date_acquired', '<=', $this->dateTo);

        return $query;
    }

    public function getAssetsSummaryProperty()
    {
        $query = $this->buildQuery();

        return [
            'total_assets' => (clone $query)->count(),
            'total_value' => (clone $query)->sum('total_cost'),
            'by_status' => (clone $query)->selectRaw('status, COUNT(*) as count, SUM(total_cost) as value')
                                        ->groupBy('status')
                                        ->get(),
            'by_category' => (clone $query)->with('category')
                                          ->selectRaw('category_id, COUNT(*) as count, SUM(total_cost) as value')
                                          ->groupBy('category_id')
                                          ->get(),
        ];
    }

    public function getCategoriesProperty()
    {
        return Category::where('type', 'asset')
                      ->where('is_active', true)
                      ->orderBy('name')
                      ->get();
    }

    public function getBranchesProperty()
    {
        $user = auth()->user();
        if ($user->isMainBranch() && ($user->isAdmin() || $user->isObserver())) {
            return Branch::where('is_active', true)->orderBy('name')->get();
        }
        return Branch::where('id', $user->branch_id)->get();
    }

    private function noDataMessage(): string
    {
        $parts = [];
        if ($this->dateFrom || $this->dateTo) {
            $parts[] = 'selected date range';
        }
        if ($this->categoryFilter) $parts[] = 'category';
        if ($this->statusFilter) $parts[] = 'status';
        if ($this->branchFilter) $parts[] = 'branch';
        if ($this->divisionFilter) $parts[] = 'division';
        if ($this->sectionFilter) $parts[] = 'section';
        $suffix = $parts ? (' for the '.implode(', ', $parts)) : '';
        return 'No asset records available'.$suffix.'.';
    }

    private function currentFiltersSummary(): array
    {
        return [
            'date' => [
                'from' => $this->dateFrom,
                'to' => $this->dateTo,
            ],
            'category' => $this->categoryFilter,
            'status' => $this->statusFilter,
            'branch' => $this->branchFilter,
            'division' => $this->divisionFilter,
            'section' => $this->sectionFilter,
        ];
    }

    public function render()
    {
        return view('livewire.assets.asset-reports', [
            'categories' => $this->categories,
            'branches' => $this->branches,
            'divisions' => $this->divisions,
            'sections' => $this->sections,
            'summary' => $this->assetsSummary,
        ]);
    }
}