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
        Schema::create('pin_reset_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saver_id')->constrained('savers')->onDelete('restrict');
            $table->string('reset_token')->unique()->index();
            $table->enum('status', ['pending', 'approved', 'completed', 'rejected'])->default('pending')->index();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pin_reset_requests');
    }
};
