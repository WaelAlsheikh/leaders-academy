<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('creation_mode', ['random', 'manual'])->default('random');
            $table->timestamps();
        });

        Schema::create('exam_question_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('exam_question_categories')->nullOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('exam_question_categories')->nullOnDelete();
            $table->enum('type', ['single_choice', 'multiple_choice', 'essay']);
            $table->text('question_text');
            $table->decimal('default_points', 8, 2)->default(1);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exam_question_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('exam_questions')->cascadeOnDelete();
            $table->text('choice_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('registrable_subject_id')->constrained('registrable_subjects')->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained('class_sections')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->enum('creation_mode', ['random', 'manual']);
            $table->unsignedInteger('question_count')->default(0);
            $table->decimal('total_points', 8, 2)->default(100);
            $table->enum('status', ['draft', 'scheduled', 'running', 'finished', 'archived'])->default('draft');
            $table->date('exam_date');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedInteger('duration_minutes');
            $table->boolean('allow_late_entry')->default(false);
            $table->boolean('questions_locked')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('exam_quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('exam_questions')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->decimal('points', 8, 2);
            $table->text('question_text_snapshot');
            $table->enum('type_snapshot', ['single_choice', 'multiple_choice', 'essay']);
            $table->timestamps();

            $table->unique(['exam_id', 'question_id']);
        });

        Schema::create('exam_quiz_question_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_quiz_question_id')->constrained('exam_quiz_questions')->cascadeOnDelete();
            $table->foreignId('choice_id')->nullable()->constrained('exam_question_choices')->nullOnDelete();
            $table->text('choice_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('status', ['in_progress', 'submitted', 'expired', 'abandoned'])->default('in_progress');
            $table->dateTime('started_at');
            $table->dateTime('expires_at');
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('last_autosave_at')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'student_id']);
        });

        Schema::create('exam_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignId('exam_quiz_question_id')->constrained('exam_quiz_questions')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('exam_questions')->cascadeOnDelete();
            $table->text('answer_text')->nullable();
            $table->unsignedBigInteger('selected_choice_id')->nullable();
            $table->json('selected_choice_ids')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('points_awarded', 8, 2)->nullable();
            $table->foreignId('graded_by_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->dateTime('graded_at')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->unique(['attempt_id', 'exam_quiz_question_id']);
        });

        Schema::create('exam_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('raw_score', 8, 2)->default(0);
            $table->decimal('max_score', 8, 2)->default(100);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->enum('status', [
                'draft',
                'auto_corrected',
                'pending_review',
                'reviewed',
                'approved',
                'published',
            ])->default('draft');
            $table->foreignId('reviewed_by_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'student_id']);
        });

        Schema::create('exam_grade_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('exam_grades')->cascadeOnDelete();
            $table->foreignId('reviewer_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_grade_reviews');
        Schema::dropIfExists('exam_grades');
        Schema::dropIfExists('exam_attempt_answers');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('exam_quiz_question_choices');
        Schema::dropIfExists('exam_quiz_questions');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('exam_question_choices');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exam_question_categories');
        Schema::dropIfExists('exam_settings');
    }
};
