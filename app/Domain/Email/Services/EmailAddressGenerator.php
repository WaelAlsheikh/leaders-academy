<?php

namespace App\Domain\Email\Services;

use App\Domain\Email\Enums\IdentityType;
use App\Domain\Email\Models\MailAccount;
use App\Domain\Email\Models\MailDomain;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailAddressGenerator
{
    public function generate(Model $identity, ?IdentityType $type = null): string
    {
        $type ??= IdentityType::fromModel($identity);
        $domain = $this->domainName();
        $base = $this->baseLocalPart($identity, $type);
        $suffix = $type->suffix();

        $candidateLocal = $base.'.'.$suffix;
        $counter = 1;

        while ($this->isTaken($candidateLocal, $domain)) {
            $counter++;
            $candidateLocal = $base.$counter.'.'.$suffix;
        }

        return strtolower($candidateLocal.'@'.$domain);
    }

    public function localPartFromEmail(string $email): string
    {
        return strtolower((string) Str::before($email, '@'));
    }

    public function domainName(): string
    {
        $configured = (string) config('email_module.default_domain', 'leaders-academy.net');

        $domain = MailDomain::query()->where('name', $configured)->where('is_active', true)->first()
            ?? MailDomain::query()->where('is_active', true)->orderBy('id')->first();

        return $domain?->name ?? $configured;
    }

    private function baseLocalPart(Model $identity, IdentityType $type): string
    {
        $source = match ($type) {
            IdentityType::Student => $identity->first_name_en
                ?? $identity->username
                ?? ($identity->first_name.' '.$identity->last_name),
            IdentityType::Doctor, IdentityType::Employee => $identity->username
                ?? $identity->full_name
                ?? 'user',
            IdentityType::Admin => $identity->name ?? $identity->email ?? 'admin',
            IdentityType::System => 'system',
        };

        $slug = Str::of((string) $source)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '.')
            ->trim('.')
            ->substr(0, 40)
            ->toString();

        if ($slug === '' || $slug === '.') {
            $slug = 'user'.$identity->getKey();
        }

        return $slug;
    }

    private function isTaken(string $localPart, string $domain): bool
    {
        $email = strtolower($localPart.'@'.$domain);

        return MailAccount::query()
            ->where(function ($q) use ($email, $localPart, $domain) {
                $q->where('institutional_email', $email)
                    ->orWhere(function ($inner) use ($localPart, $domain) {
                        $inner->where('local_part', $localPart)
                            ->whereHas('domain', fn ($d) => $d->where('name', $domain));
                    });
            })
            ->exists();
    }
}
