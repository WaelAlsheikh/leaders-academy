<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_program_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_program_id')->constrained('training_programs')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('short_description', 1000)->nullable();
            $table->text('long_description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('training_program_id', 'tpb_program_idx');
            $table->index(['training_program_id', 'is_active', 'order'], 'tpb_program_active_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_program_branches');
    }
};
