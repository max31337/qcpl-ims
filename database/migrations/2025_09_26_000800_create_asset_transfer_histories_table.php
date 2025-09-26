<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_transfer_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained();
            $table->date('transfer_date');

            $table->foreignId('origin_branch_id')->constrained('branches');
            $table->foreignId('origin_division_id')->constrained('divisions');
            $table->foreignId('origin_section_id')->constrained('sections');

            $table->foreignId('previous_branch_id')->nullable()->constrained('branches');
            $table->foreignId('previous_division_id')->nullable()->constrained('divisions');
            $table->foreignId('previous_section_id')->nullable()->constrained('sections');

            $table->foreignId('current_branch_id')->constrained('branches');
            $table->foreignId('current_division_id')->constrained('divisions');
            $table->foreignId('current_section_id')->constrained('sections');

            $table->text('remarks')->nullable();
            $table->foreignId('transferred_by')->constrained('users');
            $table->timestamps();

            $table->index(['transfer_date']);
            $table->index(['origin_branch_id']);
            $table->index(['current_branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_transfer_histories');
    }
};
