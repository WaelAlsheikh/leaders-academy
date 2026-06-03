<?php

namespace App\Console\Commands;

use App\Services\SharedLectureService;
use Illuminate\Console\Command;

class SyncSharedLectures extends Command
{
    protected $signature = 'shared-lectures:sync
                            {--group= : مفتاح مجموعة واحدة من config/shared_lectures.php}
                            {--dry-run : عرض المجموعات دون تنفيذ}';

    protected $description = 'مزامنة المحاضرات المشتركة (البرنامج التحضيري، مواد مشتركة بين كليتين، إلخ).';

    public function handle(SharedLectureService $service): int
    {
        $groups = config('shared_lectures', []);
        $only = $this->option('group');

        if ($only) {
            if (! isset($groups[$only])) {
                $this->error("المجموعة [{$only}] غير موجودة في config/shared_lectures.php");

                return self::FAILURE;
            }
            $groups = [$only => $groups[$only]];
        }

        if ($this->option('dry-run')) {
            foreach ($groups as $key => $config) {
                $this->line("• {$key}: ".($config['name'] ?? $key));
            }

            return self::SUCCESS;
        }

        foreach ($groups as $key => $config) {
            $this->info("=== {$key} ===");
            $message = $service->syncGroup($key, $config);
            $this->line($message);
        }

        $this->info('تمت المزامنة.');

        return self::SUCCESS;
    }
}
