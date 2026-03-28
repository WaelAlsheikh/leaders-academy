<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archived_enrollment_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_cycle_id')
                ->unique()
                ->constrained('enrollment_cycles')
                ->cascadeOnDelete();
            $table->foreignId('archived_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('archived_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archived_enrollment_cycles');
    }
};
