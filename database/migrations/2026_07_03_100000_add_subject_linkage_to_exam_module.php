<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_question_categories', function (Blueprint $table) {
            $table->foreignId('registrable_subject_id')
                ->nullable()
                ->after('doctor_id')
                ->constrained('registrable_subjects')
                ->nullOnDelete();
        });

        Schema::table('exam_questions', function (Blueprint $table) {
            $table->foreignId('registrable_subject_id')
                ->nullable()
                ->after('doctor_id')
                ->constrained('registrable_subjects')
                ->nullOnDelete();
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->json('category_ids')->nullable()->after('question_count');
            $table->json('question_types_filter')->nullable()->after('category_ids');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['category_ids', 'question_types_filter']);
        });

        Schema::table('exam_questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('registrable_subject_id');
        });

        Schema::table('exam_question_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('registrable_subject_id');
        });
    }
};
