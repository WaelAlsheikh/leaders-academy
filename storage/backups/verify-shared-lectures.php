<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SharedLectureGroup;
use App\Models\RegistrableSubject;
use App\Models\ClassSection;
use App\Models\College;

echo "=== SHARED GROUPS ===\n";
foreach (SharedLectureGroup::with(['hostSection.meetings', 'registrableSubjects'])->get() as $g) {
    echo "{$g->key}: host_section={$g->host_section_id} subjects=".$g->registrableSubjects->count()." students=".$g->hostSection?->students()->count()."\n";
    foreach ($g->hostSection?->meetings ?? [] as $m) {
        echo "  meeting dow={$m->day_of_week} {$m->starts_at}-{$m->ends_at}\n";
    }
}

echo "\n=== MED COLLEGE NEW SUBJECTS ===\n";
$medRs = RegistrableSubject::query()
    ->whereHas('registrableEntity', fn ($q) => $q->where('entity_type', 'college')->where('entity_id', 5))
    ->whereIn('name', ['الكيمياء الحيوية', 'اللغة الطبية (المصطلحات)'])
    ->with('studyTerm')
    ->get();
foreach ($medRs as $rs) {
    echo "id={$rs->id} {$rs->name} term={$rs->studyTerm?->display_title} code={$rs->code}\n";
}

echo "\n=== MIRROR SECTIONS (med) ===\n";
ClassSection::where('name', 'like', '%مرآة%')->with(['meetings', 'semester'])->get()->each(function ($s) {
    $college = College::find($s->semester?->enrollmentCycle?->college_id);
    echo "sec={$s->id} college={$college?->title} meetings={$s->meetings->count()}\n";
});
