<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_domains', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->text('dkim_selector')->nullable();
            $table->text('dkim_public_key')->nullable();
            $table->timestamps();
        });

        Schema::create('mail_quotas_policies', function (Blueprint $table) {
            $table->id();
            $table->string('identity_type', 32)->unique();
            $table->unsignedInteger('quota_mb')->default(1024);
            $table->unsignedInteger('max_aliases')->default(5);
            $table->timestamps();
        });

        Schema::create('mail_accounts', function (Blueprint $table) {
            $table->id();
            $table->morphs('mailable');
            $table->string('identity_type', 32);
            $table->foreignId('domain_id')->constrained('mail_domains')->cascadeOnDelete();
            $table->string('local_part');
            $table->string('institutional_email')->unique();
            $table->enum('status', ['pending', 'active', 'disabled', 'deleted'])->default('pending');
            $table->unsignedInteger('quota_mb')->default(1024);
            $table->unsignedBigInteger('used_bytes')->default(0);
            $table->enum('provisioning_status', ['pending', 'synced', 'failed'])->default('pending');
            $table->timestamp('last_synced_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['domain_id', 'local_part']);
            $table->index(['identity_type', 'status']);
            $table->index('provisioning_status');
        });

        Schema::create('mail_mailboxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_account_id')->constrained('mail_accounts')->cascadeOnDelete();
            $table->string('maildir_path')->nullable();
            $table->unsignedInteger('uid')->nullable();
            $table->unsignedInteger('gid')->nullable();
            $table->boolean('enabled')->default(true);
            $table->string('password_hash_algo')->default('BLF-CRYPT');
            $table->timestamps();

            $table->unique('mail_account_id');
        });

        Schema::create('mail_aliases', function (Blueprint $table) {
            $table->id();
            $table->string('source_email');
            $table->string('destination_email')->nullable();
            $table->foreignId('mail_account_id')->nullable()->constrained('mail_accounts')->nullOnDelete();
            $table->enum('type', ['user', 'functional', 'legacy'])->default('user');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('source_email');
            $table->index(['mail_account_id', 'is_active']);
        });

        Schema::create('mail_forwards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_account_id')->constrained('mail_accounts')->cascadeOnDelete();
            $table->string('forward_to');
            $table->boolean('keep_copy')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['mail_account_id', 'is_active']);
        });

        Schema::create('mail_distribution_lists', function (Blueprint $table) {
            $table->id();
            $table->string('address')->unique();
            $table->string('name');
            $table->json('sync_rule')->nullable();
            $table->boolean('is_auto_synced')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('mail_distribution_list_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('mail_distribution_lists')->cascadeOnDelete();
            $table->foreignId('mail_account_id')->nullable()->constrained('mail_accounts')->cascadeOnDelete();
            $table->string('external_email')->nullable();
            $table->timestamps();

            $table->unique(['list_id', 'mail_account_id']);
            $table->index('external_email');
        });

        Schema::create('mail_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('actor');
            $table->string('action', 64);
            $table->foreignId('mail_account_id')->nullable()->constrained('mail_accounts')->nullOnDelete();
            $table->json('payload')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['action', 'created_at']);
        });

        Schema::create('mail_provisioning_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 64);
            $table->json('payload')->nullable();
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_provisioning_jobs');
        Schema::dropIfExists('mail_audit_logs');
        Schema::dropIfExists('mail_distribution_list_members');
        Schema::dropIfExists('mail_distribution_lists');
        Schema::dropIfExists('mail_forwards');
        Schema::dropIfExists('mail_aliases');
        Schema::dropIfExists('mail_mailboxes');
        Schema::dropIfExists('mail_accounts');
        Schema::dropIfExists('mail_quotas_policies');
        Schema::dropIfExists('mail_domains');
    }
};
