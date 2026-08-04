<?php

namespace App\Domain\Email\DTOs;

use App\Domain\Email\Enums\IdentityType;

final class MailboxDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $localPart,
        public readonly string $domain,
        public readonly IdentityType $identityType,
        public readonly int $quotaMb,
        public readonly ?string $plainPassword = null,
        public readonly bool $enabled = true,
        public readonly ?string $name = null,
    ) {}

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'local_part' => $this->localPart,
            'domain' => $this->domain,
            'identity_type' => $this->identityType->value,
            'quota_mb' => $this->quotaMb,
            'enabled' => $this->enabled,
            'name' => $this->name,
        ];
    }
}
