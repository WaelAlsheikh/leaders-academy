<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\College;
use App\Models\RegistrableEntity;
use App\Models\RegistrableSubject;
use App\Models\ClassSection;
use App\Models\Semester;
use App\Models\EnrollmentCycle;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

echo "=== ENTITY MAP ===\n";
foreach (RegistrableEntity::where('entity_type', 'college')->get() as $e) {
    $c = College::find($e->entity_id);
    echo "entity={$e->id}\tcollege_id={$e->entity_id}\t{$c?->title}\n";
}

$healthEntity = RegistrableEntity::where('entity_type', 'college')->where('entity_id', 15)->first();
$medEntity = RegistrableEntity::where('entity_type', 'college')->where('entity_id', 5)->first();
echo "\nHealth entity: {$healthEntity?->id}, Med entity: {$medEntity?->id}\n";

foreach (['الكيمياء الحيوية', 'اللغة الطبية (المصطلحات)'] as $n) {
    echo "\nMed college subjects matching {$n}:\n";
    $subs = RegistrableSubject::where('registrable_entity_id', $medEntity?->id)->where('name', 'like', "%{$n}%")->get();
    foreach ($subs as $s) {
        echo "  id={$s->id} term={$s->studyTerm?->display_title}\n";
    }
}

echo "\n=== ACTIVE SEMESTERS (recent) ===\n";
Semester::with('enrollmentCycle.registrableEntity')->orderByDesc('id')->limit(20)->get()->each(function ($s) {
    $entity = $s->enrollmentCycle?->registrableEntity;
    $title = $entity?->entity_type === 'college' ? College::find($entity->entity_id)?->title : '?';
    echo "sem={$s->id}\t{$s->name}\tstatus={$s->status}\tentity={$title}\n";
});

echo "\n=== PREP registrations count per college ===\n";
$prepIds = RegistrableSubject::where('name', 'البرنامج التحضيري')->pluck('id');
$counts = DB::table('registration_registrable_subject')
    ->join('registrations', 'registrations.id', '=', 'registration_registrable_subject.registration_id')
    ->whereIn('registration_registrable_subject.registrable_subject_id', $prepIds)
    ->where('registrations.status', 'accepted')
    ->select('registrations.registrable_entity_id', DB::raw('count(distinct registrations.student_id) as cnt'))
    ->groupBy('registrations.registrable_entity_id')
    ->get();
foreach ($counts as $row) {
    $e = RegistrableEntity::find($row->registrable_entity_id);
    $title = College::find($e?->entity_id)?->title ?? '?';
    echo "entity={$row->registrable_entity_id} ({$title}): {$row->cnt} students\n";
}

echo "\n=== Health sciences sections for bio/med lang ===\n";
foreach ([45, 48] as $rsId) {
    ClassSection::where('registrable_subject_id', $rsId)->with(['meetings', 'semester'])->get()->each(function ($sec) use ($rsId) {
        echo "rs={$rsId} section={$sec->id} sem={$sec->semester?->name} students={$sec->students()->count()}\n";
        foreach ($sec->meetings as $m) {
            echo "  dow={$m->day_of_week} {$m->starts_at}-{$m->ends_at} {$m->start_date}..{$m->end_date}\n";
        }
    });
}
