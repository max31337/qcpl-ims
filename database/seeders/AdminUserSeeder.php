<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Division;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure base org exists (idempotent)
        $main = Branch::firstOrCreate(
            ['code' => 'MAIN'],
            ['name' => 'Main Library','district' => 'QC','address' => 'QC Main','is_main' => true]
        );
        $divCode = 'MAIN-ADMINISTRATIVESERVICES';
        $secCode = 'MAIN-ADMINISTRATIVESERVICES-RECORDSSUPPLIESINVENTORYANDMAINTENANCE';
        $div = Division::where('code', $divCode)->where('branch_id', $main->id)->first();
        $sec = Section::where('code', $secCode)->where('division_id', $div ? $div->id : null)->first();

        $email = 'admin@qcpl.gov.ph';
        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'System Admin',
                'firstname' => 'System',
                'lastname' => 'Admin',
                'username' => 'admin',
                'employee_id' => 'EMP-ADMIN',
                'role' => 'admin',
                'branch_id' => $main->id,
                'division_id' => $div ? $div->id : null,
                'section_id' => $sec ? $sec->id : null,
                'is_active' => true,
                'approval_status' => 'approved',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
    }
}
