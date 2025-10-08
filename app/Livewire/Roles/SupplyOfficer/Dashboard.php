<?php

namespace App\Livewire\Roles\SupplyOfficer;

use App\Models\Supply;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public bool $showMainLibraryOnly = false;

    public function toggleScope()
    {
        $this->showMainLibraryOnly = !$this->showMainLibraryOnly;
        
        // Clear the cache for both scope states to force refresh
        $user = Auth::user();
        $mainKey = 'supply_officer_dash_v2_user_' . $user->id . '_branch_' . $user->branch_id . '_scope_main';
        $allKey = 'supply_officer_dash_v2_user_' . $user->id . '_branch_' . $user->branch_id . '_scope_all';
        
        Cache::forget($mainKey);
        Cache::forget($allKey);
    }

    public function render()
    {
        $user = Auth::user();
        
        $scope = $this->showMainLibraryOnly ? 'main' : 'all';
        $showMainLibraryOnly = $this->showMainLibraryOnly; // Capture for closure
        $key = 'supply_officer_dash_v2_user_' . $user->id . '_branch_' . $user->branch_id . '_scope_' . $scope;
        $data = Cache::remember($key, 600, function () use ($user, $showMainLibraryOnly) {
            // Create custom query based on toggle state
            $baseQuery = function() use ($user, $showMainLibraryOnly) {
                $query = Supply::query();
                if (!$user->isMainBranch() || $showMainLibraryOnly) {
                    return $query->where('branch_id', $user->branch_id);
                }
                return $query; // Show all branches for main library users when toggle is off
            };

            $supplySkus = $baseQuery()->count();
            
            $lowStock = $baseQuery()
                ->where('current_stock', '>', 0)
                ->whereColumn('current_stock', '<', 'min_stock')
                ->count();
                
            $suppliesValue = $baseQuery()
                ->selectRaw('SUM(current_stock*unit_cost) v')
                ->value('v') ?? 0;
                
            $stockOut = $baseQuery()
                ->where('current_stock', '<=', 0)
                ->count();
                
            $stockLow = $baseQuery()
                ->where('current_stock', '>', 0)
                ->whereColumn('current_stock', '<', 'min_stock')
                ->count();
                
            $stockOk = $baseQuery()
                ->whereColumn('current_stock', '>=', 'min_stock')
                ->count();

            // Top supply categories by value
            $topSupplyCategories = $baseQuery()
                ->leftJoin('categories', 'supplies.category_id', '=', 'categories.id')
                ->selectRaw("COALESCE(categories.name, 'Uncategorized') as name, SUM(current_stock*unit_cost) as v")
                ->groupBy('categories.name')
                ->orderByDesc('v')
                ->limit(5)
                ->get();

            // Monthly supplies added (last 12 months)
            $driver = DB::connection()->getDriverName();
            $suppliesMonthExpr = match ($driver) {
                'mysql', 'mariadb' => "DATE_FORMAT(supplies.created_at, '%Y-%m')",
                'pgsql' => "TO_CHAR(supplies.created_at, 'YYYY-MM')",
                'sqlite' => "STRFTIME('%Y-%m', supplies.created_at)",
                'sqlsrv' => "FORMAT(supplies.created_at, 'yyyy-MM')",
                default => "DATE_FORMAT(supplies.created_at, '%Y-%m')",
            };

            $lineEnd = now()->endOfMonth();
            $lineStart = (clone $lineEnd)->startOfMonth()->subMonths(11);
            
            $rawMonthlySupplies = $baseQuery()
                ->selectRaw("$suppliesMonthExpr as m, COUNT(*) c")
                ->whereBetween('supplies.created_at', [
                    $lineStart->toDateString() . ' 00:00:00',
                    $lineEnd->toDateString() . ' 23:59:59',
                ])
                ->groupBy('m')
                ->get()
                ->keyBy('m');

            $suppliesMonthlyValues = [];
            for ($i = 0; $i < 12; $i++) {
                $m = (clone $lineStart)->addMonths($i);
                $key = $m->format('Y-m');
                $suppliesMonthlyValues[] = (int) ($rawMonthlySupplies[$key]->c ?? 0);
            }

            // Top on-hand supplies by value
            $topOnHandSupplies = $baseQuery()
                ->where('current_stock', '>', 0)
                ->selectRaw('id, supply_number, description, current_stock, unit_cost, (current_stock * unit_cost) as on_hand_value')
                ->orderByDesc('on_hand_value')
                ->limit(5)
                ->get();

            return compact(
                'supplySkus',
                'lowStock',
                'suppliesValue',
                'stockOut',
                'stockLow',
                'stockOk',
                'topSupplyCategories',
                'suppliesMonthlyValues',
                'topOnHandSupplies'
            );
        });

        $viewData = array_merge($data, [
            'showMainLibraryOnly' => $this->showMainLibraryOnly,
            'isMainBranch' => $user->isMainBranch(),
        ]);

        // Dispatch chart data update for JavaScript
        $this->dispatch('updateSupplyChartData', [
            'categories' => ($data['topSupplyCategories'] ?? collect())->pluck('name')->toArray(),
            'categoryCounts' => ($data['topSupplyCategories'] ?? collect())->pluck('v')->toArray(),
            'categoryValues' => ($data['topSupplyCategories'] ?? collect())->pluck('v')->toArray(),
            'monthlyLabels' => collect(range(0, 11))->map(fn($i) => now()->subMonths(11-$i)->format('M Y'))->toArray(),
            'monthlyAdds' => $data['suppliesMonthlyValues'] ?? array_fill(0, 12, 0),
            'stockHealth' => [
                'ok' => $data['stockOk'] ?? 0,
                'low' => $data['stockLow'] ?? 0,
                'out' => $data['stockOut'] ?? 0
            ]
        ]);

        return view('livewire.roles.supply-officer.dashboard', $viewData);
    }
}
