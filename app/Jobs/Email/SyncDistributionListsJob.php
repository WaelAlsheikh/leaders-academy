<?php

namespace App\Jobs\Email;

use App\Domain\Email\Models\MailDistributionList;
use App\Domain\Email\Services\DistributionListService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncDistributionListsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public function __construct(
        public readonly ?int $listId = null,
    ) {}

    public function handle(DistributionListService $lists): void
    {
        $query = MailDistributionList::query()
            ->where('is_active', true)
            ->where('is_auto_synced', true);

        if ($this->listId) {
            $query->whereKey($this->listId);
        }

        foreach ($query->cursor() as $list) {
            try {
                $lists->sync($list);
            } catch (\Throwable $e) {
                Log::error('SyncDistributionListsJob failed', [
                    'list_id' => $list->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
