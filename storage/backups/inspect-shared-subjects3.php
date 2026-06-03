<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StudyTerm;
use App\Models\StudyYear;
use App\Models\RegistrableEntity;
use App\Models\Semester;
use App\Models\EnrollmentCycle;
use App\Models\Subject;

$entity25 = RegistrableEntity::where('entity_type', 'college')->where('entity_id', 15)->first();
$entity3 = RegistrableEntity::where('entity_type', 'college')->where('entity_id', 5)->first();

echo "Health terms:\n";
foreach ($entity25->studyTerms as $t) {
    echo "  {$t->id} {$t->display_title}\n";
}
echo "Med terms:\n";
foreach ($entity3->studyTerms as $t) {
    echo "  {$t->id} {$t->display_title}\n";
}

$sem96 = Semester::find(96);
echo "\nSem 96 cycle subjects:\n";
$cycle = $sem96?->enrollmentCycle;
$linked = $cycle?->registrableSubjects()->pluck('name', 'registrable_subjects.id');
foreach ($linked ?? [] as $id => $name) {
    if (str_contains($name, 'كيمياء') || str_contains($name, 'طبية') || str_contains($name, 'تحضيري')) {
        echo "  {$id} {$name}\n";
    }
}

echo "\nLegacy subjects college 15:\n";
Subject::where('college_id', 15)->where(function ($q) {
    $q->where('name', 'like', '%كيمياء%')->orWhere('name', 'like', '%طبية%');
})->get(['id', 'name', 'code', 'study_term_id'])->each(fn ($s) => print("  {$s->id} {$s->name} term={$s->study_term_id}\n"));

echo "\nLegacy subjects college 5:\n";
Subject::where('college_id', 5)->where(function ($q) {
    $q->where('name', 'like', '%كيمياء%')->orWhere('name', 'like', '%طبية%');
})->get(['id', 'name', 'code', 'study_term_id'])->each(fn ($s) => print("  {$s->id} {$s->name} term={$s->study_term_id}\n"));
