<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ClassSection;

foreach ([80, 96, 93, 92] as $sid) {
    echo "Sem {$sid}\n";
    $secs = ClassSection::where('semester_id', $sid)
        ->whereIn('registrable_subject_id', [45, 48, 90, 86, 89])
        ->with('meetings')
        ->get();
    foreach ($secs as $s) {
        echo "  subj={$s->registrable_subject_id} sec={$s->id} meetings={$s->meetings->count()}\n";
        foreach ($s->meetings as $m) {
            echo "    dow={$m->day_of_week} {$m->starts_at}-{$m->ends_at}\n";
        }
    }
}
