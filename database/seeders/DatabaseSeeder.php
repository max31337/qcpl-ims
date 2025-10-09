<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use App\Models\Division;
use App\Models\Section;
use App\Models\Category;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
    // Ensure branches and districts
    $this->call(BranchSeeder::class);
    // Ensure divisions and sections
    $this->call(DivisionSectionSeeder::class);

        // Categories
        foreach ([
            ['name' => 'Furnitures','type' => 'asset','is_default' => true],
            ['name' => 'Books','type' => 'asset','is_default' => true],
            ['name' => 'IT Equipment','type' => 'asset','is_default' => true],
            ['name' => 'Office Equipment','type' => 'asset','is_default' => true],
        ] as $cat) {
            Category::firstOrCreate(['name' => $cat['name'], 'type' => $cat['type']], $cat);
        }

        // Supply categories (toiletries and cleaning consumables)
        $this->call(SupplyCategorySeeder::class);

        // Default admin (idempotent)
        $this->call(AdminUserSeeder::class);

        // Default observer (idempotent)
        $this->call(ObserverUserSeeder::class);

        // Demo assets with images (idempotent; links to files under storage/app/public/assets)
        $this->call(AssetDemoSeeder::class);

        // Demo supplies (idempotent; toiletries and cleaning materials)
        $this->call(SupplyDemoSeeder::class);
    }
}
