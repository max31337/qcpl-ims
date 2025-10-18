<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Division;  
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffUserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure base org exists (idempotent)
        $main = Branch::firstOrCreate(
            ['code' => 'MAIN'],
            ['name' => 'Main Library', 'district' => 'QC', 'address' => 'QC Main', 'is_main' => true]
        );
        $divCode = 'MAIN-ADMINISTRATIVESERVICES';
        $secCode = 'MAIN-ADMINISTRATIVESERVICES-RECORDSSUPPLIESINVENTORYANDMAINTENANCE';
        $div = Division::where('code', $divCode)->where('branch_id', $main->id)->first();
        $sec = Section::where('code', $secCode)->where('division_id', $div ? $div->id : null)->first();

        // Main Library Staff (Global Access)
        $email = 'staff@qcpl.gov.ph';
        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'System Staff',
                'firstname' => 'System',
                'lastname' => 'Staff',
                'username' => 'staff',
                'employee_id' => 'EMP-STAFF',
                'role' => 'staff',
                'branch_id' => $main->id,
                'division_id' => $div ? $div->id : null,
                'section_id' => $sec ? $sec->id : null,
                'is_active' => true,
                'approval_status' => 'approved',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        echo "✅ Main Library Staff created: {$email} (password: password)\n";

        // Branch Staff (Branch-specific Access)
        $project7 = Branch::where('code', 'D1-P7')->first();
        if ($project7) {
            $branchDivCode = 'D1-P7-ADMINISTRATIVESERVICES';
            $branchSecCode = 'D1-P7-ADMINISTRATIVESERVICES-RECORDSSUPPLIESINVENTORYANDMAINTENANCE';
            $branchDiv = Division::where('code', $branchDivCode)->where('branch_id', $project7->id)->first();
            $branchSec = Section::where('code', $branchSecCode)->where('division_id', $branchDiv ? $branchDiv->id : null)->first();

            $branchEmail = 'staff.project7@qcpl.gov.ph';
            User::firstOrCreate(
                ['email' => $branchEmail],
                [
                    'name' => 'Project 7 Staff',
                    'firstname' => 'Project 7',
                    'lastname' => 'Staff',
                    'username' => 'staff_project7',
                    'employee_id' => 'EMP-STAFF-P7',
                    'role' => 'staff',
                    'branch_id' => $project7->id,
                    'division_id' => $branchDiv ? $branchDiv->id : null,
                    'section_id' => $branchSec ? $branchSec->id : null,
                    'is_active' => true,
                    'approval_status' => 'approved',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ]
            );
            echo "✅ Branch Staff created: {$branchEmail} (password: password)\n";
        }
    }
}