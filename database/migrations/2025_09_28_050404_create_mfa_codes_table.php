<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mfa_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('code', 6);
            $table->string('type')->default('email'); // email, sms, etc.
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->string('purpose')->default('login'); // login, password_change, etc.
            $table->timestamps();
            
            $table->index(['user_id', 'code', 'used']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mfa_codes');
    }
};
