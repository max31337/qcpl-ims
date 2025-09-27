<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Supply;
use App\Models\Category;
use App\Models\User;
use App\Models\Branch;

class SupplyDemoSeeder extends Seeder
{
    public function run(): void
    {
    $adminId = User::where('role','admin')->value('id') ?? 1;
    $mainBranchId = Branch::where('is_main', true)->value('id');
    $otherBranchIds = Branch::where('is_main', false)->pluck('id')->all();

        $catalog = [
            // Printing & Paper
            ['desc' => 'Bond Paper A4 80gsm (ream)',        'cat' => 'Printing & Paper', 'min' => 10, 'unit_cost' => 260.00],
            ['desc' => 'Bathroom Tissue 2-ply (12 rolls)',  'cat' => 'Printing & Paper', 'min' => 12, 'unit_cost' => 180.00],
            ['desc' => 'Paper Towels (JRT) 200 pulls',      'cat' => 'Printing & Paper', 'min' => 10, 'unit_cost' => 95.00],

            // Janitorial (cleaning, toiletries, liners)
            ['desc' => 'Liquid Hand Soap 500ml',            'cat' => 'Janitorial', 'min' => 8, 'unit_cost' => 75.00],
            ['desc' => 'Alcohol 70% 1L',                    'cat' => 'Janitorial', 'min' => 10, 'unit_cost' => 120.00],
            ['desc' => 'Toilet Bowl Cleaner 500ml',         'cat' => 'Janitorial', 'min' => 6, 'unit_cost' => 110.00],
            ['desc' => 'All-Purpose Detergent 1kg',         'cat' => 'Janitorial', 'min' => 8, 'unit_cost' => 140.00],
            ['desc' => 'Glass Cleaner 500ml',               'cat' => 'Janitorial', 'min' => 6, 'unit_cost' => 85.00],
            ['desc' => 'Multi-Purpose Cleaner 1L',          'cat' => 'Janitorial', 'min' => 6, 'unit_cost' => 150.00],
            ['desc' => 'Trash Bags XL (roll of 10)',        'cat' => 'Janitorial', 'min' => 10, 'unit_cost' => 90.00],

            // Office Supplies
            ['desc' => 'Ballpen 0.5mm (box of 12)',         'cat' => 'Office Supplies', 'min' => 6, 'unit_cost' => 85.00],
            ['desc' => 'Permanent Marker (pack of 12)',     'cat' => 'Office Supplies', 'min' => 5, 'unit_cost' => 220.00],
            ['desc' => 'Stapler No. 35 with staples',       'cat' => 'Office Supplies', 'min' => 3, 'unit_cost' => 180.00],

            // Pantry
            ['desc' => 'Paper Cups (50 pcs)',               'cat' => 'Pantry', 'min' => 6, 'unit_cost' => 60.00],
        ];

        foreach ($catalog as $i => $item) {
            $categoryId = Category::where(['name' => $item['cat'], 'type' => 'supply'])->value('id');
            if (!$categoryId) { continue; }

            // Distribute stocks: some out, some low, some ok
            $bucketRoll = random_int(1, 100);
            if ($bucketRoll <= 15) {
                $currentStock = 0; // out of stock
            } elseif ($bucketRoll <= 45) {
                // low stock: between 1 and min-1
                $currentStock = max(1, $item['min'] - random_int(1, max(1, (int) floor($item['min'] / 2))));
            } else {
                // ok stock: min to min+25
                $currentStock = random_int($item['min'], $item['min'] + 25);
            }

            // Dates: created/updated in past months/years
            $years = random_int(0, 3);
            $months = random_int(0, 11);
            $days = random_int(0, 27);
            $createdAt = now()->subYears($years)->subMonths($months)->subDays($days);
            $updatedAt = (clone $createdAt)->addDays(random_int(0, 120));
            if ($updatedAt->greaterThan(now())) { $updatedAt = now()->subDays(random_int(0, 5)); }

            // Branch: 70% main, 30% others
            $branchId = (!empty($otherBranchIds) && random_int(1,100) > 70)
                ? $otherBranchIds[array_rand($otherBranchIds)]
                : ($mainBranchId ?? User::where('role','admin')->value('branch_id') ?? 1);

            $payload = [
                'supply_number' => Supply::generateSupplyNumber(),
                'description' => $item['desc'],
                'category_id' => $categoryId,
                'current_stock' => $currentStock,
                'min_stock' => $item['min'],
                'unit_cost' => $item['unit_cost'],
                'status' => 'active',
                'branch_id' => $branchId,
                'created_by' => $adminId,
                'last_updated' => $updatedAt,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ];

            Supply::firstOrCreate(
                ['description' => $item['desc'], 'branch_id' => $branchId],
                $payload
            );
        }
    }
}
