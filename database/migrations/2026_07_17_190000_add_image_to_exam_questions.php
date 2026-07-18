<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('question_text');
        });

        Schema::table('exam_quiz_questions', function (Blueprint $table) {
            $table->string('image_path_snapshot')->nullable()->after('question_text_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::table('exam_quiz_questions', function (Blueprint $table) {
            $table->dropColumn('image_path_snapshot');
        });
    }
};
