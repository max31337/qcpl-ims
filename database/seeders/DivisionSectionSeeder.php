<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Division;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DivisionSectionSeeder extends Seeder
{
    public function run(): void
    {
        // Attach to MAIN branch; create if missing
        $main = Branch::firstOrCreate(
            ['code' => 'MAIN'],
            ['name' => 'Main Library','district' => 'QC','address' => 'QC Main','is_main' => true]
        );

        $map = [
            'ADMINISTRATIVE SERVICES' => [
                'MIS SECTION',
                'FINANCE & BUDGET',
                'HUMAN RESOURCE MANAGEMENT',
                'RECORDS, SUPPLIES, INVENTORY AND MAINTENANCE',
            ],
            "READER’S SERVICES DIVISION" => [
                'REFERENCE SECTION',
                'FILIPINIANA/LOCAL HISTORY & ARCHIVES',
                'PERIODICALS SECTION',
                'CHILDRENS SECTION',
                'LAW RESEARCH SECTION',
                'E-GOVERNMENT SECTION',
                'E-RESOURCES SECTION',
            ],
            'TECHNICAL SERVICES DIVISION' => [
                'COLLECTION DEVELOPMENT',
                'CATALOGING',
                'BINDING PRESERVATION',
            ],
            'LIBRARY EXTENSION DIVISION' => [
                'RECREATIONAL, SOCIAL & EDUCATIONAL',
                'PUBLICATION',
            ],
        ];

        foreach ($map as $divisionName => $sections) {
            $divCode = strtoupper(preg_replace('/[^A-Z0-9]+/', '', Str::ascii($divisionName)));
            $division = Division::firstOrCreate(
                ['code' => $divCode],
                ['name' => $divisionName, 'branch_id' => $main->id, 'is_active' => true]
            );

            foreach ($sections as $sectionName) {
                $secCode = strtoupper(preg_replace('/[^A-Z0-9]+/', '', Str::ascii($sectionName)));
                Section::firstOrCreate(
                    ['code' => $secCode],
                    ['name' => $sectionName, 'division_id' => $division->id, 'is_active' => true]
                );
            }
        }
    }
}
