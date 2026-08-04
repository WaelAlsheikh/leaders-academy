<?php

namespace App\Console\Commands;

use App\Domain\Email\Models\MailDistributionList;
use App\Domain\Email\Services\DistributionListService;
use App\Jobs\Email\SyncDistributionListsJob;
use Illuminate\Console\Command;

class LeadersSyncDistributionListsCommand extends Command
{
    protected $signature = 'leaders:sync-distribution-lists
                            {--list= : Sync a single list id}
                            {--sync : Run inline instead of queue}
                            {--all : Include non-auto-synced lists}';

    protected $description = 'Sync institutional distribution list members from mail_accounts rules';

    public function handle(DistributionListService $lists): int
    {
        if (! $this->option('sync') && ! $this->option('list') && ! $this->option('all')) {
            SyncDistributionListsJob::dispatch();
            $this->info('Queued SyncDistributionListsJob for auto-synced lists.');

            return self::SUCCESS;
        }

        $query = MailDistributionList::query()->where('is_active', true);
        if ($listId = $this->option('list')) {
            $query->whereKey((int) $listId);
        } elseif (! $this->option('all')) {
            $query->where('is_auto_synced', true);
        }

        $total = 0;
        foreach ($query->cursor() as $list) {
            $count = $lists->sync($list);
            $this->line("{$list->address}: {$count} members");
            $total += $count;
        }

        $this->info("Done. members synced across lists ≈ {$total}");

        return self::SUCCESS;
    }
}
