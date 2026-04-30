<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')
                ->constrained('doctors')
                ->cascadeOnDelete();
            $table->foreignId('registrable_subject_id')
                ->constrained('registrable_subjects')
                ->cascadeOnDelete();
            $table->enum('material_type', ['video', 'file']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['doctor_id', 'registrable_subject_id', 'material_type'], 'subject_materials_doctor_subject_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_materials');
    }
};
