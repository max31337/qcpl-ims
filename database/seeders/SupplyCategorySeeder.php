<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class SupplyCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Follow GUIDE.md example categories for supplies
        // Only these four supply categories should be active; others will be deactivated
        $target = [
            ['name' => 'Office Supplies', 'is_default' => true],
            ['name' => 'Janitorial', 'is_default' => false],
            ['name' => 'Printing & Paper', 'is_default' => false],
            ['name' => 'Pantry', 'is_default' => false],
        ];

        foreach ($target as $row) {
            Category::updateOrCreate(
                ['name' => $row['name'], 'type' => 'supply'],
                ['is_default' => $row['is_default'], 'is_active' => true]
            );
        }

        // Deactivate any other supply categories not in the target list
        $allowedNames = array_map(fn($r) => $r['name'], $target);
        Category::where('type', 'supply')
            ->whereNotIn('name', $allowedNames)
            ->update(['is_active' => false]);
    }
}
