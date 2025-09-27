<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Branch;
use App\Models\Division;
use App\Models\Section;
use App\Models\Category;
use App\Models\Asset;
use App\Models\AssetGroup;

class AssetDemoSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->value('id') ?? 1;

        // Ensure categories exist
        $categories = [
            'Books' => 'asset',
            'IT Equipment' => 'asset',
            'Office Equipment' => 'asset',
            'Furnitures' => 'asset',
        ];
        foreach ($categories as $name => $type) {
            Category::firstOrCreate(['name' => $name, 'type' => $type], ['is_default' => false, 'is_active' => true]);
        }
        $catIds = Category::where('type','asset')->pluck('id','name');

        // Use main user's current location as default
        $branchId = Branch::query()->value('id');
        $divisionId = Division::where('branch_id', $branchId)->value('id');
        $sectionId = Section::where('division_id', $divisionId)->value('id');

        $defaultLoc = [
            'current_branch_id' => $branchId,
            'current_division_id' => $divisionId,
            'current_section_id' => $sectionId,
        ];

        $today = now()->toDateString();

        // Collect available asset images from storage (public disk -> storage/app/public/assets)
        $availableImages = collect(Storage::disk('public')->files('assets'))
            ->map(fn($path) => [
                'path' => $path,
                'name' => Str::lower(basename($path)),
            ]);

        // Helper to pick a best-match image by keywords
        $pickImage = function(array $keywords) use ($availableImages) {
            $keywords = array_map(fn($t) => Str::lower($t), $keywords);
            // Prefer files that match ALL keywords; fall back to ANY
            $allMatch = $availableImages->first(function ($file) use ($keywords) {
                foreach ($keywords as $kw) {
                    if (!Str::contains($file['name'], $kw)) return false;
                }
                return true;
            });
            if ($allMatch) return $allMatch['path'];
            $anyMatch = $availableImages->first(function ($file) use ($keywords) {
                foreach ($keywords as $kw) {
                    if (Str::contains($file['name'], $kw)) return true;
                }
                return false;
            });
            return $anyMatch['path'] ?? null;
        };

        // Helper to create a group and N items
        $make = function(array $g, int $count) use ($userId, $defaultLoc) {
            $group = AssetGroup::firstOrCreate([
                'description' => $g['description'],
                'category_id' => $g['category_id'],
                'date_acquired' => $g['date_acquired'],
                'unit_cost' => $g['unit_cost'],
                'status' => $g['status'],
                'source' => $g['source'],
                'created_by' => $g['created_by'],
            ], [ 'image_path' => $g['image_path'] ?? null ]);

            for ($i=0; $i<$count; $i++) {
                Asset::create(array_merge($defaultLoc, [
                    'property_number' => Asset::generatePropertyNumber(),
                    'asset_group_id' => $group->id,
                    // legacy columns retained during staged migration
                    'description' => $g['description'],
                    'quantity' => 1,
                    'date_acquired' => $g['date_acquired'],
                    'unit_cost' => $g['unit_cost'],
                    'total_cost' => $g['unit_cost'],
                    'category_id' => $g['category_id'],
                    'status' => $g['status'],
                    'source' => $g['source'],
                    'image_path' => $g['image_path'] ?? null,
                    'created_by' => $g['created_by'],
                ]));
            }
        };

        // Seed data sets
        // Multiple printers (IT Equipment)
        $make([
            'description' => 'HP LaserJet Pro M404dn Network Printer',
            'category_id' => $catIds['IT Equipment'],
            'date_acquired' => $today,
            'unit_cost' => 14500,
            'status' => 'active',
            'source' => 'qc_property',
            'created_by' => $userId,
            'image_path' => $pickImage(['hp','laserjet','m404','printer']),
        ], 5);

        // One giant OLED screen (IT Equipment)
        $make([
            'description' => 'LG 97-inch Signature OLED M (4K)',
            'category_id' => $catIds['IT Equipment'],
            'date_acquired' => $today,
            'unit_cost' => 1500000,
            'status' => 'active',
            'source' => 'qc_property',
            'created_by' => $userId,
            'image_path' => $pickImage(['lg','oled','97']),
        ], 1);

        // Multiple books of the same title (Books)
        $make([
            'description' => 'Penguin Classics: Noli Me Tangere (Touch Me Not) by Jose Rizal',
            'category_id' => $catIds['Books'],
            'date_acquired' => $today,
            'unit_cost' => 650,
            'status' => 'active',
            'source' => 'donation',
            'created_by' => $userId,
            'image_path' => $pickImage(['noli','rizal']),
        ], 12);

        // An ancient/special book (Books)
        $make([
            'description' => 'Rare 1st Ed. Vocabulario de la lengua tagala (1613) Facsimile',
            'category_id' => $catIds['Books'],
            'date_acquired' => $today,
            'unit_cost' => 250000,
            'status' => 'active',
            'source' => 'donation',
            'created_by' => $userId,
            'image_path' => $pickImage(['vocabulario','tagala']),
        ], 1);

        // Office equipment (chairs, tables) as separate groups
        $make([
            'description' => 'Ergonomic Mesh Office Chair (Black)',
            'category_id' => $catIds['Furnitures'],
            'date_acquired' => $today,
            'unit_cost' => 4800,
            'status' => 'active',
            'source' => 'qc_property',
            'created_by' => $userId,
            'image_path' => $pickImage(['chair','mesh']),
        ], 20);

        $make([
            'description' => 'Library Study Table (120x60 cm, Oak)',
            'category_id' => $catIds['Furnitures'],
            'date_acquired' => $today,
            'unit_cost' => 9200,
            'status' => 'active',
            'source' => 'qc_property',
            'created_by' => $userId,
            'image_path' => $pickImage(['table','oak']),
        ], 10);

        // Office Equipment (laminator, shredder)
        $make([
            'description' => 'A3 Thermal Laminator (4-roller)',
            'category_id' => $catIds['Office Equipment'],
            'date_acquired' => $today,
            'unit_cost' => 7800,
            'status' => 'active',
            'source' => 'qc_property',
            'created_by' => $userId,
            'image_path' => $pickImage(['laminator','a3']),
        ], 2);

        $make([
            'description' => 'Heavy-duty Paper Shredder (20-sheet)',
            'category_id' => $catIds['Office Equipment'],
            'date_acquired' => $today,
            'unit_cost' => 18900,
            'status' => 'active',
            'source' => 'qc_property',
            'created_by' => $userId,
            'image_path' => $pickImage(['shredder','20']),
        ], 2);
    }
}
