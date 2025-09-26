<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('property_number')->unique();
            $table->text('description');
            $table->integer('quantity');
            $table->date('date_acquired');
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('total_cost', 12, 2);
            $table->foreignId('category_id')->constrained();
            $table->enum('status', ['active','condemn','disposed']);
            $table->enum('source', ['qc_property','donation']);
            $table->string('image_path')->nullable();

            $table->foreignId('current_branch_id')->constrained('branches');
            $table->foreignId('current_division_id')->constrained('divisions');
            $table->foreignId('current_section_id')->constrained('sections');

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['status']);
            $table->index(['date_acquired']);
            $table->index(['current_branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
