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
        Schema::create('cash_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teller_id')->constrained('users')->onDelete('restrict');
            $table->date('closure_date')->index();
            $table->unsignedBigInteger('opening_balance')->default(0);
            $table->unsignedBigInteger('total_deposit')->default(0);
            $table->unsignedBigInteger('total_withdrawal')->default(0);
            $table->unsignedBigInteger('system_ending_balance')->default(0);
            $table->unsignedBigInteger('physical_ending_balance')->default(0);
            $table->bigInteger('difference')->default(0);
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft')->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['teller_id', 'closure_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_closures');
    }
};
