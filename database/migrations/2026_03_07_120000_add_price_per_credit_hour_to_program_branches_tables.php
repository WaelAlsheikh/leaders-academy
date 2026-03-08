<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_branches', function (Blueprint $table) {
            if (!Schema::hasColumn('program_branches', 'price_per_credit_hour')) {
                $table->decimal('price_per_credit_hour', 10, 2)
                    ->default(0)
                    ->after('image');
            }
        });

        Schema::table('training_program_branches', function (Blueprint $table) {
            if (!Schema::hasColumn('training_program_branches', 'price_per_credit_hour')) {
                $table->decimal('price_per_credit_hour', 10, 2)
                    ->default(0)
                    ->after('image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('program_branches', function (Blueprint $table) {
            if (Schema::hasColumn('program_branches', 'price_per_credit_hour')) {
                $table->dropColumn('price_per_credit_hour');
            }
        });

        Schema::table('training_program_branches', function (Blueprint $table) {
            if (Schema::hasColumn('training_program_branches', 'price_per_credit_hour')) {
                $table->dropColumn('price_per_credit_hour');
            }
        });
    }
};

