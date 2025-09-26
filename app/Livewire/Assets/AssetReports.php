<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Branch;
use Livewire\Component;
use Livewire\Attributes\Layout;
// use Maatwebsite\Excel\Facades\Excel;
// use App\Exports\AssetsExport;

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

    public function exportAssets()
    {
        // TODO: Install maatwebsite/excel package first
        // composer require maatwebsite/laravel-excel
        
        session()->flash('info', 'Excel export functionality coming soon. Please install maatwebsite/laravel-excel package.');
    }

    public function getAssetsSummaryProperty()
    {
        $query = Asset::forUser(auth()->user());

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->branchFilter) {
            $query->where('current_branch_id', $this->branchFilter);
        }

        if ($this->dateFrom) {
            $query->where('date_acquired', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->where('date_acquired', '<=', $this->dateTo);
        }

        return [
            'total_assets' => $query->count(),
            'total_value' => $query->sum('total_cost'),
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