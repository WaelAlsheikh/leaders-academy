<?php

use App\Domain\Email\Enums\IdentityType;
use App\Domain\Email\Models\MailDomain;
use App\Domain\Email\Models\MailQuotaPolicy;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $domain = config('email_module.default_domain', 'leaders-academy.net');

        MailDomain::query()->firstOrCreate(
            ['name' => $domain],
            ['is_active' => true, 'dkim_selector' => 'mail']
        );

        foreach (IdentityType::cases() as $type) {
            MailQuotaPolicy::query()->firstOrCreate(
                ['identity_type' => $type->value],
                [
                    'quota_mb' => $type->defaultQuotaMb(),
                    'max_aliases' => $type->maxAliases(),
                ]
            );
        }
    }

    public function down(): void
    {
        // keep seeded data
    }
};
