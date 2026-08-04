<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('pass_percentage')
                ->default(60)
                ->after('creation_mode');
        });
    }

    public function down(): void
    {
        Schema::table('exam_settings', function (Blueprint $table) {
            $table->dropColumn('pass_percentage');
        });
    }
};
