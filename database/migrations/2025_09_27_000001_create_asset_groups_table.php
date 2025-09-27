<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_groups', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->foreignId('category_id')->constrained();
            $table->date('date_acquired');
            $table->decimal('unit_cost', 12, 2);
            $table->enum('status', ['active','condemn','disposed']);
            $table->enum('source', ['qc_property','donation']);
            $table->string('image_path')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Add the asset_group_id FK to assets without dropping legacy columns yet (safe/staged migration)
        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'asset_group_id')) {
                $table->foreignId('asset_group_id')->after('id')->nullable()->constrained('asset_groups');
            }
        });

        // Optional backfill: create groups from existing asset rows and link them
        // Only run if legacy columns exist (older schema) to infer grouping attributes.
        if (
            Schema::hasColumn('assets', 'description') &&
            Schema::hasColumn('assets', 'category_id') &&
            Schema::hasColumn('assets', 'date_acquired') &&
            Schema::hasColumn('assets', 'unit_cost') &&
            Schema::hasColumn('assets', 'status') &&
            Schema::hasColumn('assets', 'source')
        ) {
            // Backfill per chunk using Query Builder
            \DB::table('assets')->whereNull('asset_group_id')->orderBy('id')->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    // Determine creator for the group
                    $createdBy = $row->created_by ?? 1; // fallback
                    // Reuse existing image_path if present
                    $imagePath = $row->image_path ?? null;
                    // Find or create an asset_group with matching shared attributes
                    $groupId = \DB::table('asset_groups')->where([
                        ['description', '=', $row->description],
                        ['category_id', '=', $row->category_id],
                        ['date_acquired', '=', $row->date_acquired],
                        ['unit_cost', '=', $row->unit_cost],
                        ['status', '=', $row->status],
                        ['source', '=', $row->source],
                        ['created_by', '=', $createdBy],
                    ])
                    ->when($imagePath !== null, function ($q) use ($imagePath) { $q->where('image_path', $imagePath); })
                    ->value('id');

                    if (!$groupId) {
                        $groupId = \DB::table('asset_groups')->insertGetId([
                            'description' => $row->description,
                            'category_id' => $row->category_id,
                            'date_acquired' => $row->date_acquired,
                            'unit_cost' => $row->unit_cost,
                            'status' => $row->status,
                            'source' => $row->source,
                            'image_path' => $imagePath,
                            'created_by' => $createdBy,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Link the asset to the group
                    \DB::table('assets')->where('id', $row->id)->update([
                        'asset_group_id' => $groupId,
                    ]);
                }
            });
        }
    }

    public function down(): void
    {
        // Drop FK column only if it exists; do not attempt to restore legacy columns
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'asset_group_id')) {
                $table->dropForeign(['asset_group_id']);
                $table->dropColumn('asset_group_id');
            }
        });
        Schema::dropIfExists('asset_groups');
    }
};
