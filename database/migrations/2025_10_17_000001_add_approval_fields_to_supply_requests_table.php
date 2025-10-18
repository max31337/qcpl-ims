<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('supply_requests', function (Blueprint $table) {
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('supply_requests', function (Blueprint $table) {
            $table->dropColumn(['approved_by', 'approved_at', 'rejected_by', 'rejected_at']);
        });
    }
};
