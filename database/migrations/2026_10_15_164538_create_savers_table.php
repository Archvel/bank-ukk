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
        Schema::create('savers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('restrict');
            $table->foreignId('member_id')->unique()->constrained('members')->onDelete('restrict');
            $table->string('account_number')->unique()->index();
            $table->string('pin');
            $table->string('qr_code_token')->unique()->index();
            $table->unsignedBigInteger('balance')->default(0);
            $table->enum('status', ['active', 'blocked', 'closed'])->default('active');
            $table->timestamp('opened_at')->useCurrent();
            $table->softDeletes();
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE savers ADD CONSTRAINT chk_savers_balance CHECK (balance >= 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savers');
    }
};
