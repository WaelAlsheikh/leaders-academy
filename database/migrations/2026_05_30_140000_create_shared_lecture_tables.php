<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_lecture_groups', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->foreignId('host_registrable_subject_id')
                ->nullable()
                ->constrained('registrable_subjects')
                ->nullOnDelete();
            $table->foreignId('host_section_id')
                ->nullable()
                ->constrained('class_sections')
                ->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('shared_lecture_group_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shared_lecture_group_id')
                ->constrained('shared_lecture_groups')
                ->cascadeOnDelete();
            $table->foreignId('registrable_subject_id')
                ->constrained('registrable_subjects')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['shared_lecture_group_id', 'registrable_subject_id'],
                'shared_lecture_group_subject_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_lecture_group_subjects');
        Schema::dropIfExists('shared_lecture_groups');
    }
};
