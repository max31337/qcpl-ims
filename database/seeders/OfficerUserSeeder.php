<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Division;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OfficerUserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure main branch and basic org units exist (idempotent)
        $main = Branch::firstOrCreate(
            ['code' => 'MAIN'],
            ['name' => 'Main Library','district' => 'QC','address' => 'QC Main','is_main' => true]
        );

    $divCode = 'MAIN-ADMINISTRATIVESERVICES';
    $secCode = 'MAIN-ADMINISTRATIVESERVICES-RECORDSSUPPLIESINVENTORYANDMAINTENANCE';
    $div = Division::where('code', $divCode)->where('branch_id', $main->id)->first();
    $sec = Section::where('code', $secCode)->where('division_id', $div ? $div->id : null)->first();

        // Property Officer - Main Library
        $email = 'property.officer@qcpl.gov.ph';
        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Property Officer',
                'firstname' => 'Property',
                'lastname' => 'Officer',
                'username' => 'property_officer',
                'employee_id' => 'EMP-PROP-MAIN',
                'role' => 'property_officer',
                'branch_id' => $main->id,
                'division_id' => $div->id,
                'section_id' => $sec->id,
                'is_active' => true,
                'approval_status' => 'approved',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        echo "\u2705 Property Officer created: {$email} (password: password)\n";

        // Supply Officer - Main Library
        $supplyEmail = 'supply.officer@qcpl.gov.ph';
        User::firstOrCreate(
            ['email' => $supplyEmail],
            [
                'name' => 'Supply Officer',
                'firstname' => 'Supply',
                'lastname' => 'Officer',
                'username' => 'supply_officer',
                'employee_id' => 'EMP-SUP-MAIN',
                'role' => 'supply_officer',
                'branch_id' => $main->id,
                'division_id' => $div->id,
                'section_id' => $sec->id,
                'is_active' => true,
                'approval_status' => 'approved',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        echo "\u2705 Supply Officer created: {$supplyEmail} (password: password)\n";

        // Branch-specific officers: try Project 7 (D1-P7) as example branch
        $project7 = Branch::where('code', 'D1-P7')->first();
        if ($project7) {
            $branchDivCode = 'D1-P7-ADMINISTRATIVESERVICES';
            $branchSecCode = 'D1-P7-ADMINISTRATIVESERVICES-RECORDSSUPPLIESINVENTORYANDMAINTENANCE';
            $branchDiv = Division::where('code', $branchDivCode)->where('branch_id', $project7->id)->first();
            $branchSec = Section::where('code', $branchSecCode)->where('division_id', $branchDiv ? $branchDiv->id : null)->first();

            $branchPropEmail = 'property.project7@qcpl.gov.ph';
            User::firstOrCreate(
                ['email' => $branchPropEmail],
                [
                    'name' => 'Project 7 Property Officer',
                    'firstname' => 'Project7',
                    'lastname' => 'PropertyOfficer',
                    'username' => 'property_p7',
                    'employee_id' => 'EMP-PROP-P7',
                    'role' => 'property_officer',
                    'branch_id' => $project7->id,
                    'division_id' => $branchDiv ? $branchDiv->id : null,
                    'section_id' => $branchSec ? $branchSec->id : null,
                    'is_active' => true,
                    'approval_status' => 'approved',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ]
            );
            echo "✅ Branch Property Officer created: {$branchPropEmail} (password: password)\n";

            $branchSupplyEmail = 'supply.project7@qcpl.gov.ph';
            User::firstOrCreate(
                ['email' => $branchSupplyEmail],
                [
                    'name' => 'Project 7 Supply Officer',
                    'firstname' => 'Project7',
                    'lastname' => 'SupplyOfficer',
                    'username' => 'supply_p7',
                    'employee_id' => 'EMP-SUP-P7',
                    'role' => 'supply_officer',
                    'branch_id' => $project7->id,
                    'division_id' => $branchDiv ? $branchDiv->id : null,
                    'section_id' => $branchSec ? $branchSec->id : null,
                    'is_active' => true,
                    'approval_status' => 'approved',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ]
            );
            echo "✅ Branch Supply Officer created: {$branchSupplyEmail} (password: password)\n";
        }
    }
}
