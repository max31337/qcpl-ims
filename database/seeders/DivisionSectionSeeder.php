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
        // Full structure (for Main Library)
        $mainMap = [
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

        // Simplified structure (for other branches)
        $branchMap = [
            "READER’S SERVICES DIVISION" => [
                'GENERAL READING',
                'CHILDREN’S SECTION',
            ],
        ];

        // Ensure MAIN exists
        Branch::firstOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'Main Library',
                'district' => 'QC',
                'address' => 'QC Main',
                'is_main' => true,
            ]
        );

        foreach (Branch::all() as $branch) {
            $branchCode = strtoupper($branch->code);

            // Pick structure depending on branch type
            $map = $branch->is_main ? $mainMap : $branchMap;

            foreach ($map as $divisionName => $sections) {
                $divBase = strtoupper(preg_replace('/[^A-Z0-9]+/', '', Str::ascii($divisionName)));
                $divCode = $branchCode . '-' . $divBase;

                $division = Division::firstOrCreate(
                    ['code' => $divCode],
                    [
                        'name' => $divisionName,
                        'branch_id' => $branch->id,
                        'is_active' => true,
                    ]
                );

                foreach ($sections as $sectionName) {
                    $secBase = strtoupper(preg_replace('/[^A-Z0-9]+/', '', Str::ascii($sectionName)));
                    $secCode = $divCode . '-' . $secBase;

                    Section::firstOrCreate(
                        ['code' => $secCode],
                        [
                            'name' => $sectionName,
                            'division_id' => $division->id,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }
}
