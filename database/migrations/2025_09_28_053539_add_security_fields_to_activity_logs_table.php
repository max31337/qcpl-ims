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
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('ip_address')->nullable()->after('description');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->string('browser')->nullable()->after('user_agent');
            $table->string('browser_version')->nullable()->after('browser');
            $table->string('platform')->nullable()->after('browser_version');
            $table->string('device')->nullable()->after('platform');
            $table->string('session_id')->nullable()->after('device');
            $table->json('request_data')->nullable()->after('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn([
                'ip_address',
                'user_agent', 
                'browser',
                'browser_version',
                'platform',
                'device',
                'session_id',
                'request_data'
            ]);
        });
    }
};
