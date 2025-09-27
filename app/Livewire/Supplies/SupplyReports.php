<?php

namespace App\Livewire\Supplies;

use App\Models\Supply;
use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SupplyReports extends Component
{
    public string $categoryFilter = '';
    public string $statusFilter = '';

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
