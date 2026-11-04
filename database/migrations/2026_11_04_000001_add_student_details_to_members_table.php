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
        Schema::table('members', function (Blueprint $table) {
            $table->string('nis')->nullable()->index()->after('user_id');
            $table->unsignedInteger('absen')->nullable()->after('nis');
            $table->string('class_name')->nullable()->index()->after('student_name');
            $table->string('major')->nullable()->index()->after('class_name');
            $table->string('academic_year')->nullable()->after('major');
            $table->string('gender', 2)->nullable()->after('academic_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['nis', 'absen', 'class_name', 'major', 'academic_year', 'gender']);
        });
    }
};

