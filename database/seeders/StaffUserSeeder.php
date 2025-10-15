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
        $div = Division::firstOrCreate(['code' => 'GEN'], ['name' => 'General Services', 'branch_id' => $main->id]);
        $sec = Section::firstOrCreate(['code' => 'OPS'], ['name' => 'Operations', 'division_id' => $div->id]);

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
                'division_id' => $div->id,
                'section_id' => $sec->id,
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
            $branchDiv = Division::firstOrCreate(['code' => 'P7-GEN', 'branch_id' => $project7->id], ['name' => 'General Services', 'branch_id' => $project7->id]);
            $branchSec = Section::firstOrCreate(['code' => 'P7-OPS', 'division_id' => $branchDiv->id], ['name' => 'Operations', 'division_id' => $branchDiv->id]);

            $branchEmail = 'staff.project7@qcpl.gov.ph';
            User::firstOrCreate(
                ['email' => $branchEmail],
                [
                    'name' => 'Project 7 Staff',
                    'firstname' => 'Project 7',
                    'lastname' => 'Staff',
                    'username' => ' ',
                    'employee_id' => 'EMP-STAFF-P7',
                    'role' => 'staff',
                    'branch_id' => $project7->id,
                    'division_id' => $branchDiv->id,
                    'section_id' => $branchSec->id,
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