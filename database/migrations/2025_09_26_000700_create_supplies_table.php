<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->string('supply_number')->unique();
            $table->text('description');
            $table->foreignId('category_id')->constrained();
            $table->integer('current_stock');
            $table->integer('min_stock');
            $table->decimal('unit_cost', 12, 2);
            $table->enum('status', ['active','inactive']);
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('last_updated')->useCurrent();
            $table->timestamps();

            $table->index(['branch_id']);
            $table->index(['status']);
            $table->index(['last_updated']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplies');
    }
};
