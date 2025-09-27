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
use App\Models\AssetTransferHistory;

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

        // Prefer MAIN library as default location, with some items in other branches
        $mainBranch = Branch::where('is_main', true)->first();
        $otherBranches = Branch::where('is_main', false)->pluck('id')->all();

        // Helper: pick a random Section within a Branch (with its Division)
        $pickLocation = function (?int $preferredBranchId = null) {
            $branchId = $preferredBranchId;
            if (!$branchId) {
                $branchId = Branch::inRandomOrder()->value('id');
            }
            $divisionId = Division::where('branch_id', $branchId)->inRandomOrder()->value('id');
            $sectionId = $divisionId ? Section::where('division_id', $divisionId)->inRandomOrder()->value('id') : null;
            return [
                'current_branch_id' => $branchId,
                'current_division_id' => $divisionId,
                'current_section_id' => $sectionId,
            ];
        };

        // Default loc is MAIN
        $defaultLoc = $pickLocation($mainBranch?->id);

        // Helper: random past date within last N years
        $randomDate = function(int $maxYearsBack = 5): string {
            $years = random_int(0, $maxYearsBack);
            $months = random_int(0, 11);
            $days = random_int(0, 27);
            return now()->subYears($years)->subMonths($months)->subDays($days)->toDateString();
        };

        // Helper: decide per-item status based on age (older more likely disposed)
        $pickStatus = function (string $dateAcquired): string {
            $ageYears = now()->diffInYears($dateAcquired);
            $roll = random_int(1, 100);
            if ($ageYears >= 5) {
                // Older items: 25% disposed, 20% condemn
                if ($roll <= 25) return 'disposed';
                if ($roll <= 45) return 'condemn';
                return 'active';
            }
            if ($ageYears >= 3) {
                // Mid-age: 10% disposed, 15% condemn
                if ($roll <= 10) return 'disposed';
                if ($roll <= 25) return 'condemn';
                return 'active';
            }
            // Newer: 5% condemn
            if ($roll <= 5) return 'condemn';
            return 'active';
        };

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
        $make = function(array $g, int $count) use ($userId, $defaultLoc, $pickLocation, $otherBranches, $mainBranch, $pickStatus) {
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
                // Location: 70% MAIN, 30% other branch
                $loc = (random_int(1,100) <= 70 || empty($otherBranches))
                    ? ($defaultLoc ?? $pickLocation($mainBranch?->id))
                    : $pickLocation($otherBranches[array_rand($otherBranches)]);

                // Status: per-item based on age
                $status = $pickStatus($g['date_acquired']);

                // created_at near acquired date
                $createdAt = now()->parse($g['date_acquired'])->addDays(random_int(0, 60));
                if ($createdAt->greaterThan(now())) { $createdAt = now()->subDays(random_int(0, 10)); }

                $asset = Asset::create(array_merge($loc, [
                    'property_number' => Asset::generatePropertyNumber(),
                    'asset_group_id' => $group->id,
                    // legacy columns retained during staged migration
                    'description' => $g['description'],
                    'quantity' => 1,
                    'date_acquired' => $g['date_acquired'],
                    'unit_cost' => $g['unit_cost'],
                    'total_cost' => $g['unit_cost'],
                    'category_id' => $g['category_id'],
                    'status' => $status,
                    'source' => $g['source'],
                    'image_path' => $g['image_path'] ?? null,
                    'created_by' => $g['created_by'],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]));

                // Initial transfer history (origin + current = initial loc)
                AssetTransferHistory::create([
                    'asset_id' => $asset->id,
                    'transfer_date' => $g['date_acquired'],
                    'origin_branch_id' => $loc['current_branch_id'],
                    'origin_division_id' => $loc['current_division_id'],
                    'origin_section_id' => $loc['current_section_id'],
                    'previous_branch_id' => null,
                    'previous_division_id' => null,
                    'previous_section_id' => null,
                    'current_branch_id' => $loc['current_branch_id'],
                    'current_division_id' => $loc['current_division_id'],
                    'current_section_id' => $loc['current_section_id'],
                    'remarks' => 'Initialized at origin',
                    'transferred_by' => $userId,
                ]);

                // Optional subsequent transfers
                $transfers = 0;
                $roll = random_int(1, 100);
                if ($roll <= 30) { $transfers = 1; }
                if ($roll <= 10) { $transfers = 2; }

                $originLoc = $loc; // keep original origin
                $currentLoc = $loc;
                $transferDate = now()->parse($g['date_acquired'])->addMonths(random_int(1, 18));
                for ($t = 0; $t < $transfers; $t++) {
                    // New target branch (prefer switching branch)
                    $targetBranch = $currentLoc['current_branch_id'];
                    if (!empty($otherBranches)) {
                        // 60% chance go to/from MAIN
                        if (random_int(1,100) <= 60 && $mainBranch) {
                            $targetBranch = ($currentLoc['current_branch_id'] === $mainBranch->id)
                                ? $otherBranches[array_rand($otherBranches)]
                                : $mainBranch->id;
                        } else {
                            // random other branch different from current
                            $candidates = array_values(array_filter($otherBranches, fn($b) => $b !== $currentLoc['current_branch_id']));
                            if (!empty($candidates)) {
                                $targetBranch = $candidates[array_rand($candidates)];
                            }
                        }
                    }
                    $newLoc = $pickLocation($targetBranch);

                    // Ensure transfer date increases and not in future
                    $transferDate = $transferDate->addMonths(random_int(1, 6));
                    if ($transferDate->greaterThan(now())) { $transferDate = now()->subDays(random_int(0, 5)); }

                    AssetTransferHistory::create([
                        'asset_id' => $asset->id,
                        'transfer_date' => $transferDate->toDateString(),
                        'origin_branch_id' => $originLoc['current_branch_id'],
                        'origin_division_id' => $originLoc['current_division_id'],
                        'origin_section_id' => $originLoc['current_section_id'],
                        'previous_branch_id' => $currentLoc['current_branch_id'],
                        'previous_division_id' => $currentLoc['current_division_id'],
                        'previous_section_id' => $currentLoc['current_section_id'],
                        'current_branch_id' => $newLoc['current_branch_id'],
                        'current_division_id' => $newLoc['current_division_id'],
                        'current_section_id' => $newLoc['current_section_id'],
                        'remarks' => 'Auto-seeded transfer',
                        'transferred_by' => $userId,
                    ]);

                    // Update asset location to new current
                    $asset->update($newLoc);
                    $currentLoc = $newLoc;
                }
            }
        };

        // Seed data sets
        // Multiple printers (IT Equipment)
        $make([
            'description' => 'HP LaserJet Pro M404dn Network Printer',
            'category_id' => $catIds['IT Equipment'],
            'date_acquired' => $randomDate(),
            'unit_cost' => 14500,
            'status' => 'active', // group default, per-item may differ
            'source' => (random_int(1,100) <= 20 ? 'donation' : 'qc_property'),
            'created_by' => $userId,
            'image_path' => $pickImage(['hp','laserjet','m404','printer']),
        ], 5);

        // One giant OLED screen (IT Equipment)
        $make([
            'description' => 'LG 97-inch Signature OLED M (4K)',
            'category_id' => $catIds['IT Equipment'],
            'date_acquired' => $randomDate(),
            'unit_cost' => 1500000,
            'status' => 'active',
            'source' => (random_int(1,100) <= 10 ? 'donation' : 'qc_property'),
            'created_by' => $userId,
            'image_path' => $pickImage(['lg','oled','97']),
        ], 1);

        // Multiple books of the same title (Books)
        $make([
            'description' => 'Penguin Classics: Noli Me Tangere (Touch Me Not) by Jose Rizal',
            'category_id' => $catIds['Books'],
            'date_acquired' => $randomDate(),
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
            'date_acquired' => $randomDate(),
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
            'date_acquired' => $randomDate(),
            'unit_cost' => 4800,
            'status' => 'active',
            'source' => (random_int(1,100) <= 10 ? 'donation' : 'qc_property'),
            'created_by' => $userId,
            'image_path' => $pickImage(['chair','mesh']),
        ], 20);

        $make([
            'description' => 'Library Study Table (120x60 cm, Oak)',
            'category_id' => $catIds['Furnitures'],
            'date_acquired' => $randomDate(),
            'unit_cost' => 9200,
            'status' => 'active',
            'source' => (random_int(1,100) <= 10 ? 'donation' : 'qc_property'),
            'created_by' => $userId,
            'image_path' => $pickImage(['table','oak']),
        ], 10);

        // Office Equipment (laminator, shredder)
        $make([
            'description' => 'A3 Thermal Laminator (4-roller)',
            'category_id' => $catIds['Office Equipment'],
            'date_acquired' => $randomDate(),
            'unit_cost' => 7800,
            'status' => 'active',
            'source' => (random_int(1,100) <= 10 ? 'donation' : 'qc_property'),
            'created_by' => $userId,
            'image_path' => $pickImage(['laminator','a3']),
        ], 2);

        $make([
            'description' => 'Heavy-duty Paper Shredder (20-sheet)',
            'category_id' => $catIds['Office Equipment'],
            'date_acquired' => $randomDate(),
            'unit_cost' => 18900,
            'status' => 'active',
            'source' => (random_int(1,100) <= 10 ? 'donation' : 'qc_property'),
            'created_by' => $userId,
            'image_path' => $pickImage(['shredder','20']),
        ], 2);
    }
}
