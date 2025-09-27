<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Supply;
use App\Models\Category;
use App\Models\User;

class SupplyDemoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('role','admin')->value('id') ?? 1;
        $branchId = User::where('role','admin')->value('branch_id') ?? 1;

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

            $payload = [
                'supply_number' => Supply::generateSupplyNumber(),
                'description' => $item['desc'],
                'category_id' => $categoryId,
                'current_stock' => random_int($item['min'], $item['min'] + 25),
                'min_stock' => $item['min'],
                'unit_cost' => $item['unit_cost'],
                'status' => 'active',
                'branch_id' => $branchId,
                'created_by' => $adminId,
                'last_updated' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            Supply::firstOrCreate(
                ['description' => $item['desc'], 'branch_id' => $branchId],
                $payload
            );
        }
    }
}
