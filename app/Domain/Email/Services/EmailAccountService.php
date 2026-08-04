<?php

namespace App\Domain\Email\Services;

use App\Domain\Email\Enums\IdentityType;
use App\Domain\Email\Models\MailAccount;
use App\Jobs\Email\ProvisionMailboxJob;
use App\Models\Doctor;
use App\Models\Employee;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EmailAccountService
{
    public function __construct(
        private readonly MailboxProvisioningService $provisioning,
    ) {}

    public function ensureAccount(Model $identity, bool $sync = false): MailAccount
    {
        if ($sync || config('queue.default') === 'sync') {
            return $this->provisioning->provisionForIdentity($identity)['account'];
        }

        ProvisionMailboxJob::dispatch($identity::class, (int) $identity->getKey());

        return MailAccount::query()
            ->where('mailable_type', $identity->getMorphClass())
            ->where('mailable_id', $identity->getKey())
            ->first() ?? $this->provisioning->provisionForIdentity($identity)['account'];
    }

    /**
     * @return Collection<int, Model>
     */
    public function identitiesWithoutMail(?string $type = null, ?int $limit = null): Collection
    {
        $map = [
            'student' => Student::class,
            'doctor' => Doctor::class,
            'employee' => Employee::class,
            'admin' => User::class,
        ];

        $classes = $type ? [$map[$type] ?? null] : array_values($map);
        $classes = array_filter($classes);

        $items = collect();
        foreach ($classes as $class) {
            $query = $class::query()
                ->whereDoesntHave('mailAccount')
                ->orderBy('id');

            if ($limit) {
                $query->limit($limit);
            }

            $items = $items->merge($query->get());
            if ($limit && $items->count() >= $limit) {
                return $items->take($limit);
            }
        }

        return $items;
    }
}
