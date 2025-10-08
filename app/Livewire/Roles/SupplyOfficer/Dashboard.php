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
    public function render()
    {
        $user = Auth::user();
        
        $key = 'supply_officer_dash_v1_user_' . $user->id . '_branch_' . $user->branch_id;
        $data = Cache::remember($key, 600, function () use ($user) {
            // Use proper forUser scope for accurate data
            $supplySkus = Supply::forUser($user)->count();
            
            $lowStock = Supply::forUser($user)
                ->where('current_stock', '>', 0)
                ->whereColumn('current_stock', '<', 'min_stock')
                ->count();
                
            $suppliesValue = Supply::forUser($user)
                ->selectRaw('SUM(current_stock*unit_cost) v')
                ->value('v') ?? 0;
                
            $stockOut = Supply::forUser($user)
                ->where('current_stock', '<=', 0)
                ->count();
                
            $stockLow = Supply::forUser($user)
                ->where('current_stock', '>', 0)
                ->whereColumn('current_stock', '<', 'min_stock')
                ->count();
                
            $stockOk = Supply::forUser($user)
                ->whereColumn('current_stock', '>=', 'min_stock')
                ->count();

            // Top supply categories by value
            $topSupplyCategories = Supply::forUser($user)
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
            
            $rawMonthlySupplies = Supply::forUser($user)
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
            $topOnHandSupplies = Supply::forUser($user)
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

        return view('livewire.roles.supply-officer.dashboard', $data);
    }
}
