<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stub for future support-ticket ↔ mailbox threading (Phase F design).
 * Not used by provisioning yet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_workflow_messages', function (Blueprint $table) {
            $table->id();
            $table->string('thread_key', 64)->nullable()->index();
            $table->nullableMorphs('related');
            $table->foreignId('mail_account_id')->nullable()->constrained('mail_accounts')->nullOnDelete();
            $table->string('direction', 16)->default('outbound');
            $table->string('from_email')->nullable();
            $table->string('to_email')->nullable();
            $table->string('subject')->nullable();
            $table->string('message_id')->nullable()->index();
            $table->string('in_reply_to')->nullable();
            $table->json('headers')->nullable();
            $table->text('body_excerpt')->nullable();
            $table->string('status', 32)->default('stub');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_workflow_messages');
    }
};
