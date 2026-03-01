<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('class_sections')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Sunday ... 6=Saturday
            $table->time('starts_at');
            $table->time('ends_at');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->unique(['section_id', 'day_of_week', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_meetings');
    }
};
