<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('full_name');
        });

        Schema::table('registrable_subjects', function (Blueprint $table) {
            $table->foreignId('doctor_id')
                ->nullable()
                ->after('legacy_subject_id')
                ->constrained('doctors')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('registrable_subjects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('doctor_id');
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
