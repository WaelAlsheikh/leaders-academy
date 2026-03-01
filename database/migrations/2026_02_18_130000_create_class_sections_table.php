<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('mode', ['online', 'in_person'])->default('online');
            $table->string('zoom_url')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['semester_id', 'subject_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sections');
    }
};
