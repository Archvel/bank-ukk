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
        Schema::table('cash_closures', function (Blueprint $table) {
            $table->bigInteger('system_ending_balance')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_closures', function (Blueprint $table) {
            $table->unsignedBigInteger('system_ending_balance')->default(0)->change();
        });
    }
};
