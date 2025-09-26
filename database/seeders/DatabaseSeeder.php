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
        // Base org
        $main = Branch::firstOrCreate(
            ['code' => 'MAIN'],
            ['name' => 'Main Library','district' => 'QC','address' => 'QC Main','is_main' => true]
        );
        $div = Division::firstOrCreate(['code' => 'GEN'], ['name' => 'General Services','branch_id' => $main->id]);
        $sec = Section::firstOrCreate(['code' => 'OPS'], ['name' => 'Operations','division_id' => $div->id]);

        // Categories
        foreach ([
            ['name' => 'Furnitures','type' => 'asset','is_default' => true],
            ['name' => 'Books','type' => 'asset','is_default' => true],
            ['name' => 'IT Equipment','type' => 'asset','is_default' => true],
            ['name' => 'Office Equipment','type' => 'asset','is_default' => true],
        ] as $cat) {
            Category::firstOrCreate(['name' => $cat['name'], 'type' => $cat['type']], $cat);
        }

        // Default admin if missing
        if (!User::where('email', 'admin@qcpl.local')->exists()) {
            User::create([
                'name' => 'System Admin',
                'firstname' => 'System',
                'lastname' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@qcpl.gov.ph',
                'employee_id' => 'EMP-ADMIN',
                'role' => 'admin',
                'branch_id' => $main->id,
                'division_id' => $div->id,
                'section_id' => $sec->id,
                'is_active' => true,
                'approval_status' => 'approved',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]);
        }
    }
}
