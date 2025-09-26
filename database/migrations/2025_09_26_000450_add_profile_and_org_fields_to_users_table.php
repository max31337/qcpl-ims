<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('firstname')->nullable();
            $table->string('middlename')->nullable();
            $table->string('lastname')->nullable();
            $table->string('username')->unique()->nullable();
            $table->string('employee_id')->unique()->nullable();
            $table->enum('role', ['admin','staff','supply_officer','property_officer','observer'])->default('staff');
            $table->foreignId('branch_id')->nullable()->constrained();
            $table->foreignId('division_id')->nullable()->constrained();
            $table->foreignId('section_id')->nullable()->constrained();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['firstname','middlename','lastname','username','employee_id','role','is_active']);
            $table->dropConstrainedForeignId('branch_id');
            $table->dropConstrainedForeignId('division_id');
            $table->dropConstrainedForeignId('section_id');
        });
    }
};
