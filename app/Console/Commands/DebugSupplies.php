<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Supply;
use App\Models\User;

class DebugSupplies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:supplies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug supply data for low stock and out of stock items';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::find(5); // Test User 5 (supply_officer) specifically

        $this->info("=== Supply Debug Info ===");
        $this->info("User ID: " . $user->id);
        $this->info("User role: " . $user->role);
        $this->info("User branch ID: " . $user->branch_id);
        $this->info("Is admin: " . ($user->isAdmin() ? 'Yes' : 'No'));
        $this->info("Is main branch: " . ($user->isMainBranch() ? 'Yes' : 'No'));
        $this->info("");

        // Test the scoped queries
        $suppliesQuery = Supply::forUser($user);
        $this->info("Total scoped supplies: " . $suppliesQuery->count());

        $lowStockQuery = Supply::forUser($user)->whereColumn('current_stock', '<', 'min_stock');
        $this->info("Scoped low stock supplies: " . $lowStockQuery->count());

        $outOfStockQuery = Supply::forUser($user)->where('current_stock', '<=', 0);
        $this->info("Scoped out of stock supplies: " . $outOfStockQuery->count());

        $this->info("");
        $this->info("=== Sample Low Stock Items ===");
        $lowStockItems = Supply::forUser($user)
            ->whereColumn('current_stock', '<', 'min_stock')
            ->select('supply_number', 'description', 'current_stock', 'min_stock', 'branch_id')
            ->limit(5)
            ->get();

        foreach ($lowStockItems as $item) {
            $this->info("- {$item->supply_number}: {$item->description} (Stock: {$item->current_stock}/{$item->min_stock}) [Branch: {$item->branch_id}]");
        }

        $this->info("");
        $this->info("=== Sample Out of Stock Items ===");
        $outOfStockItems = Supply::forUser($user)
            ->where('current_stock', '<=', 0)
            ->select('supply_number', 'description', 'current_stock', 'min_stock', 'branch_id')
            ->limit(5)
            ->get();

        foreach ($outOfStockItems as $item) {
            $this->info("- {$item->supply_number}: {$item->description} (Stock: {$item->current_stock}/{$item->min_stock}) [Branch: {$item->branch_id}]");
        }

        // Test the exact query from Dashboard
        $this->info("");
        $this->info("=== Dashboard Query Test ===");
        $lowStockItems = Supply::forUser($user)
            ->whereColumn('current_stock', '<', 'min_stock')
            ->select(
                'id', 'supply_number', 'description', 'current_stock', 'min_stock', 'unit_cost',
                \DB::raw('(min_stock - current_stock) as deficit'),
                \DB::raw('(min_stock - current_stock) * unit_cost as reorder_value')
            )
            ->orderByDesc(\DB::raw('(min_stock - current_stock) * unit_cost'))
            ->limit(8)
            ->get();

        $this->info("Dashboard low stock items count: " . $lowStockItems->count());
        foreach ($lowStockItems as $item) {
            $this->info("- {$item->supply_number}: {$item->description} (Deficit: {$item->deficit}, Reorder Value: ₱{$item->reorder_value})");
        }

        // Test Dashboard component directly
        $this->info("");
        $this->info("=== Dashboard Component Test ===");
        
        $dashboard = new \App\Http\Livewire\Admin\Dashboard();
        $dashboard->mount();
        
        // Simulate the auth user
        \Auth::login($user);
        
        $data = $dashboard->render()->getData();
        
        $this->info("Dashboard data keys: " . implode(', ', array_keys($data)));
        
        if (isset($data['lowStockItems'])) {
            $lowStockFromDashboard = $data['lowStockItems'];
            $this->info("Dashboard component lowStockItems count: " . $lowStockFromDashboard->count());
            
            foreach ($lowStockFromDashboard as $item) {
                $this->info("- Dashboard: {$item->supply_number}: {$item->description}");
            }
        } else {
            $this->error("lowStockItems not found in dashboard data!");
        }
    }
}
