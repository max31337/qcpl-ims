<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Branch;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('layouts.app')]
class AssetReports extends Component
{
    public $categoryFilter = '';
    public $statusFilter = '';
    public $branchFilter = '';
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

        $callback = function() use ($assets) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Property Number','Description','Category','Quantity','Unit Cost','Total Cost','Status','Source','Date Acquired','Branch','Division','Section']);
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
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'assets-report-'.now()->format('Ymd-His').'.pdf', [
            'Content-Type' => 'application/pdf'
        ]);
    }

    private function buildQuery()
    {
        $query = Asset::forUser(auth()->user());

        if ($this->categoryFilter) $query->where('category_id', $this->categoryFilter);
        if ($this->statusFilter) $query->where('status', $this->statusFilter);
        if ($this->branchFilter) $query->where('current_branch_id', $this->branchFilter);
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

    public function render()
    {
        return view('livewire.assets.asset-reports', [
            'categories' => $this->categories,
            'branches' => $this->branches,
            'summary' => $this->assetsSummary,
        ]);
    }
}