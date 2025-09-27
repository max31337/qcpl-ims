<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Branch;
use App\Models\Division;
use App\Models\Section;
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

    // Export: Excel (fallback to CSV if package issues)
    public function exportAssets()
    {
        $assets = $this->buildQuery()->with(['category','currentBranch','currentDivision','currentSection'])
            ->orderBy('property_number')->get();

        // Try Laravel Excel via response()->streamDownload() compatible CSV
        $filename = 'assets-report-'.now()->format('Ymd-His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $noDataMessage = $this->noDataMessage();

        $callback = function() use ($assets, $noDataMessage) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Property Number','Description','Category','Quantity','Unit Cost','Total Cost','Status','Source','Date Acquired','Branch','Division','Section']);
            if ($assets->isEmpty()) {
                fputcsv($out, [$noDataMessage]);
                fclose($out);
                return;
            }
            foreach ($assets as $a) {
                fputcsv($out, [
                    $a->property_number,
                    $a->description,
                    optional($a->category)->name,
                    $a->quantity,
                    number_format((float)$a->unit_cost, 2, '.', ''),
                    number_format((float)$a->total_cost, 2, '.', ''),
                    $a->status,
                    $a->source,
                    optional($a->date_acquired)?->format('Y-m-d'),
                    optional($a->currentBranch)->name,
                    optional($a->currentDivision)->name,
                    optional($a->currentSection)->name,
                ]);
            }
            fclose($out);
        };

        return response()->streamDownload($callback, $filename, $headers);
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