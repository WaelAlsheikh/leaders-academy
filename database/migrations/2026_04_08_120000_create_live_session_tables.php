<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('class_sections')->cascadeOnDelete();
            $table->foreignId('section_meeting_id')->constrained('section_meetings')->cascadeOnDelete();
            $table->date('occurrence_date');
            $table->string('meeting_provider')->default('jitsi_public');
            $table->string('provider_room_name')->nullable()->unique();
            $table->json('provider_payload')->nullable();
            $table->dateTime('scheduled_starts_at');
            $table->dateTime('scheduled_ends_at');
            $table->dateTime('started_at')->nullable();
            $table->foreignId('started_by_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->dateTime('entry_closed_at')->nullable();
            $table->foreignId('entry_closed_by_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->dateTime('ended_at')->nullable();
            $table->foreignId('ended_by_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->boolean('comments_enabled')->default(true);
            $table->boolean('audio_moderation_enabled')->default(false);
            $table->boolean('video_moderation_enabled')->default(false);
            $table->timestamps();

            $table->unique(['section_meeting_id', 'occurrence_date'], 'live_sessions_occurrence_unique');
            $table->index(['section_id', 'occurrence_date'], 'live_sessions_section_occurrence_index');
            $table->index(['scheduled_starts_at', 'scheduled_ends_at'], 'live_sessions_schedule_index');
        });

        Schema::create('live_session_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->dateTime('first_joined_at')->nullable();
            $table->dateTime('last_seen_at')->nullable();
            $table->dateTime('last_left_at')->nullable();
            $table->boolean('is_present')->default(false);
            $table->unsignedInteger('join_count')->default(0);
            $table->string('jitsi_participant_id')->nullable();
            $table->timestamps();

            $table->unique(['live_session_id', 'student_id'], 'live_session_attendance_unique');
            $table->index(['live_session_id', 'is_present'], 'live_session_present_index');
        });

        Schema::create('live_session_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->string('author_type', 20);
            $table->unsignedBigInteger('author_id');
            $table->string('author_name_snapshot');
            $table->text('body');
            $table->boolean('is_hidden')->default(false);
            $table->dateTime('hidden_at')->nullable();
            $table->foreignId('hidden_by_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->timestamps();

            $table->index(['live_session_id', 'id'], 'live_session_comments_stream_index');
            $table->index(['author_type', 'author_id'], 'live_session_comments_author_index');
        });

        Schema::create('live_session_comment_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->boolean('is_blocked')->default(true);
            $table->dateTime('blocked_at')->nullable();
            $table->foreignId('blocked_by_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->timestamps();

            $table->unique(['live_session_id', 'student_id'], 'live_session_comment_block_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_session_comment_blocks');
        Schema::dropIfExists('live_session_comments');
        Schema::dropIfExists('live_session_attendances');
        Schema::dropIfExists('live_sessions');
    }
};
