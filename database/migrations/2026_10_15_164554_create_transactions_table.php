<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique()->index();
            $table->foreignId('saver_id')->constrained('savers')->onDelete('restrict');
            $table->foreignId('teller_id')->constrained('users')->onDelete('restrict');
            $table->enum('type', ['deposit', 'withdrawal'])->index();
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('opening_balance');
            $table->unsignedBigInteger('closing_balance');
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE transactions ADD CONSTRAINT chk_transactions_amount CHECK (amount > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
