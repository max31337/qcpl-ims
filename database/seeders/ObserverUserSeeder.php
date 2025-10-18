<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Division;  
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ObserverUserSeeder extends Seeder
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

        // Main Library Observer (Global Access)
        $email = 'observer@qcpl.gov.ph';
        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'System Observer',
                'firstname' => 'System',
                'lastname' => 'Observer',
                'username' => 'observer',
                'employee_id' => 'EMP-OBSERVER',
                'role' => 'observer',
                'branch_id' => $main->id,
                'division_id' => $div->id,
                'section_id' => $sec->id,
                'is_active' => true,
                'approval_status' => 'approved',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        echo "✅ Main Library Observer created: {$email} (password: password)\n";

        // Branch Observer (Branch-specific Access)
        $project7 = Branch::where('code', 'D1-P7')->first();
        if ($project7) {
            $branchDivCode = 'D1-P7-ADMINISTRATIVESERVICES';
            $branchSecCode = 'D1-P7-ADMINISTRATIVESERVICES-RECORDSSUPPLIESINVENTORYANDMAINTENANCE';
            $branchDiv = Division::where('code', $branchDivCode)->where('branch_id', $project7->id)->first();
            $branchSec = Section::where('code', $branchSecCode)->where('division_id', $branchDiv ? $branchDiv->id : null)->first();

            $branchEmail = 'observer.project7@qcpl.gov.ph';
            User::firstOrCreate(
                ['email' => $branchEmail],
                [
                    'name' => 'Project 7 Observer',
                    'firstname' => 'Project 7',
                    'lastname' => 'Observer',
                    'username' => 'observer_project7',
                    'employee_id' => 'EMP-OBS-P7',
                    'role' => 'observer',
                    'branch_id' => $project7->id,
                    'division_id' => $branchDiv ? $branchDiv->id : null,
                    'section_id' => $branchSec ? $branchSec->id : null,
                    'is_active' => true,
                    'approval_status' => 'approved',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ]
            );
            echo "✅ Branch Observer created: {$branchEmail} (password: password)\n";
        }
    }
}