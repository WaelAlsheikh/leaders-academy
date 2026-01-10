<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_platforms', function (Blueprint $table) {
            // أقسام ثابتة: قسم 1 و قسم 2
            $table->string('title1')->nullable()->after('title');
            $table->string('image1')->nullable()->after('title1'); // مسار الصورة أو اسم الملف في storage
            $table->longText('content1')->nullable()->after('image1');

            $table->string('title2')->nullable()->after('content1');
            $table->string('image2')->nullable()->after('title2');
            $table->longText('content2')->nullable()->after('image2');
        });
    }

    public function down(): void
    {
        Schema::table('student_platforms', function (Blueprint $table) {
            $table->dropColumn(['title1', 'image1', 'content1', 'title2', 'image2', 'content2']);
        });
    }
};
