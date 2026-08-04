<?php

namespace App\Console\Commands;

use App\Domain\Email\Services\EmailAccountService;
use App\Domain\Email\Services\MailboxProvisioningService;
use App\Jobs\Email\ProvisionMailboxJob;
use Illuminate\Console\Command;

class LeadersGenerateEmailsCommand extends Command
{
    protected $signature = 'leaders:generate-emails
        {--type= : student|doctor|employee|admin}
        {--dry-run : Show what would be created without provisioning}
        {--sync : Run provisioning synchronously}
        {--limit= : Max identities to process}';

    protected $description = 'Generate and provision institutional email accounts for existing identities';

    public function handle(EmailAccountService $accounts, MailboxProvisioningService $provisioning): int
    {
        $type = $this->option('type');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $dryRun = (bool) $this->option('dry-run');
        $sync = (bool) $this->option('sync');

        $identities = $accounts->identitiesWithoutMail($type, $limit);
        $this->info('Found '.$identities->count().' identities without mail accounts.');

        $created = 0;
        $failed = 0;

        foreach ($identities as $identity) {
            $label = class_basename($identity).'#'.$identity->getKey();

            if ($dryRun) {
                $this->line("[dry-run] would provision {$label}");
                $created++;
                continue;
            }

            try {
                if ($sync || config('queue.default') === 'sync') {
                    $result = $provisioning->provisionForIdentity($identity);
                    $this->info("Provisioned {$label} → {$result['account']->institutional_email}");
                } else {
                    ProvisionMailboxJob::dispatch($identity::class, (int) $identity->getKey());
                    $this->info("Queued {$label}");
                }
                $created++;
            } catch (\Throwable $e) {
                $failed++;
                $this->error("Failed {$label}: ".$e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Done. created/queued={$created}, failed={$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
