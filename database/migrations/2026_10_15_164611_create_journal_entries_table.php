<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('restrict');
            $table->foreignId('cash_closure_id')->nullable()->constrained('cash_closures')->onDelete('restrict');
            $table->string('entry_number')->index();
            $table->string('account_code')->index();
            $table->enum('type', ['debit', 'credit']);
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->date('entry_date')->index();
            $table->timestamps();

            $table->foreign('account_code')->references('code')->on('accounts')->onDelete('restrict');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE journal_entries ADD CONSTRAINT chk_journal_entries_amount CHECK (amount > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
