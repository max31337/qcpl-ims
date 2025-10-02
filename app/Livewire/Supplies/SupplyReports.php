<?php

namespace App\Livewire\Supplies;

use App\Models\Supply;
use App\Models\Category;
use App\Exports\SuppliesExport;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('layouts.app')]
class SupplyReports extends Component
{
    public string $categoryFilter = '';
    public string $statusFilter = '';

    /**
     * Export supplies to Excel with enhanced formatting
     * 
     * This method uses the new ExcelReportService to generate a professionally 
     * formatted Excel file with headers, styling, and metadata
     */
    public function exportExcel()
    {
        $export = new SuppliesExport(
            auth()->user(),
            $this->categoryFilter ?: null,
            $this->statusFilter ?: null,
            null, // search parameter
            null, // from date
            null  // to date
        );

        return $export->download();
    }

    /**
     * Legacy CSV export method for backwards compatibility
     */
    public function exportCsv()
    {
        $user = auth()->user();
        $rows = $this->buildQuery($user)->with('category')
            ->orderBy('supply_number')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="supplies-'.now()->format('Ymd-His').'.csv"',
        ];

        $callback = function() use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Supply #','Description','Category','Current Stock','Min Stock','Unit Cost','Status']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->supply_number,
                    $r->description,
                    optional($r->category)->name,
                    $r->current_stock,
                    $r->min_stock,
                    number_format((float)$r->unit_cost, 2, '.', ''),
                    $r->status,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf()
    {
        $user = auth()->user();
        $supplies = $this->buildQuery($user)->with('category')
            ->orderBy('supply_number')->get();

        // Calculate summary data
        $summary = [
            'total_items' => $supplies->count(),
            'on_hand_units' => $supplies->sum('current_stock'),
            'on_hand_value' => $supplies->sum(function($s) { return $s->current_stock * $s->unit_cost; }),
            'low_stock_items' => $supplies->filter(function($s) { return $s->current_stock < $s->min_stock; })->count(),
            'by_category' => $supplies->groupBy('category_id')->map(function($group) {
                $category = $group->first()->category;
                return (object)[
                    'category' => $category,
                    'count' => $group->count(),
                    'total_value' => $group->sum(function($s) { return $s->current_stock * $s->unit_cost; }),
                    'total_stock' => $group->sum('current_stock')
                ];
            })->values()
        ];

        $data = [
            'supplies' => $supplies,
            'summary' => $summary,
            'user' => $user,
            'branch' => $user->branch,
            'generated_at' => now(),
            'filters' => [
                'category' => $this->categoryFilter ? Category::find($this->categoryFilter)?->name : 'All Categories',
                'status' => $this->statusFilter ?: 'All Status'
            ],
            // Add debug info
            'total_query_items' => $supplies->count(),
            'has_supplies' => $supplies->isNotEmpty()
        ];

        $pdf = Pdf::loadView('exports.supplies-report', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'debugKeepTemp' => false
            ]);

        $filename = 'supplies-report-' . now()->format('Ymd-His') . '.pdf';
        
        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    protected function buildQuery($user)
    {
        return Supply::forUser($user)
            ->when($this->categoryFilter !== '', fn($q) => $q->where('category_id', $this->categoryFilter))
            ->when($this->statusFilter !== '', fn($q) => $q->where('status', $this->statusFilter));
    }

    public function render()
    {
        $user = auth()->user();
        $q = $this->buildQuery($user);
        $summary = [
            'total_items' => (clone $q)->count(),
            'on_hand_units' => (clone $q)->sum('current_stock'),
            'on_hand_value' => (clone $q)->selectRaw('SUM(current_stock*unit_cost) v')->value('v') ?? 0,
            'by_category' => (clone $q)->selectRaw('category_id, COUNT(*) c, SUM(current_stock*unit_cost) v')
                ->groupBy('category_id')->with('category:id,name')->get(),
        ];

        return view('livewire.supplies.supply-reports', [
            'categories' => Category::where('type','supply')->orderBy('name')->get(['id','name']),
            'summary' => $summary,
        ]);
    }
}
