<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricing_settings', function (Blueprint $table) {
            if (Schema::hasColumn('pricing_settings', 'price_per_credit_hour')) {
                $table->dropColumn('price_per_credit_hour');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pricing_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('pricing_settings', 'price_per_credit_hour')) {
                $table->decimal('price_per_credit_hour', 10, 2)->default(0);
            }
        });
    }
};
