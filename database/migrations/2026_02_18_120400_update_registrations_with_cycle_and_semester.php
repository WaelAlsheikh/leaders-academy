<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignId('enrollment_cycle_id')->nullable()->after('college_id')
                ->constrained('enrollment_cycles')->nullOnDelete();
            $table->foreignId('semester_id')->nullable()->after('enrollment_cycle_id')
                ->constrained('semesters')->nullOnDelete();
            $table->enum('academic_status', ['submitted', 'accepted', 'rejected', 'waitlisted'])
                ->default('submitted')
                ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('enrollment_cycle_id');
            $table->dropConstrainedForeignId('semester_id');
            $table->dropColumn('academic_status');
        });
    }
};
