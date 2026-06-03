<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\College;
use App\Models\RegistrableSubject;
use App\Models\ClassSection;
use App\Models\SectionMeeting;
use Illuminate\Support\Facades\DB;

$names = ['البرنامج التحضيري', 'الكيمياء الحيوية', 'اللغة الطبية (المصطلحات)'];

echo "=== COLLEGES ===\n";
foreach (College::orderBy('id')->get(['id', 'title']) as $c) {
    echo "{$c->id}\t{$c->title}\n";
}

echo "\n=== SUBJECTS BY NAME ===\n";
foreach ($names as $name) {
    echo "\n--- {$name} ---\n";
    $rows = RegistrableSubject::query()
        ->with(['registrableEntity', 'studyTerm.studyYear'])
        ->where('name', 'like', '%'.trim($name).'%')
        ->orWhere('name', $name)
        ->get();

    foreach ($rows as $rs) {
        $entity = $rs->registrableEntity;
        $collegeTitle = $entity?->entity_type === 'college'
            ? College::find($entity->entity_id)?->title
            : ($entity?->title_snapshot ?? 'n/a');
        $term = $rs->studyTerm?->display_title ?? 'no term';
        echo "rs_id={$rs->id}\tentity={$entity?->id}\tcollege={$collegeTitle}\tterm={$term}\tcode={$rs->code}\n";

        $sections = ClassSection::where('registrable_subject_id', $rs->id)
            ->with(['semester', 'meetings', 'students'])
            ->get();
        foreach ($sections as $sec) {
            $meetCount = $sec->meetings->count();
            $studentCount = $sec->students->count();
            echo "  section_id={$sec->id}\tsemester={$sec->semester?->name}\tmeetings={$meetCount}\tstudents={$studentCount}\n";
            foreach ($sec->meetings as $m) {
                echo "    meeting: dow={$m->day_of_week} {$m->starts_at}-{$m->ends_at} {$m->start_date} to {$m->end_date}\n";
            }
        }
    }
}

echo "\n=== Fuzzy college search ===\n";
foreach (['علوم صحية', 'طب', 'بشري', 'عام'] as $q) {
    $found = College::where('title', 'like', "%{$q}%")->pluck('title', 'id');
    echo "{$q}: ".$found->toJson(JSON_UNESCAPED_UNICODE)."\n";
}
