<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_sections', function (Blueprint $table) {
            $table->foreignId('doctor_id')
                ->nullable()
                ->after('registrable_subject_id')
                ->constrained('doctors')
                ->nullOnDelete();
        });

        if (Schema::hasColumn('registrable_subjects', 'doctor_id')) {
            DB::statement("
                UPDATE class_sections cs
                JOIN registrable_subjects rs ON rs.id = cs.registrable_subject_id
                SET cs.doctor_id = rs.doctor_id
                WHERE cs.doctor_id IS NULL
            ");
        }
    }

    public function down(): void
    {
        Schema::table('class_sections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('doctor_id');
        });
    }
};
