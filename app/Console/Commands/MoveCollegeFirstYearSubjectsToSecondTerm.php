<?php

namespace App\Console\Commands;

use App\Models\RegistrableEntity;
use App\Models\StudyTerm;
use App\Models\StudyYear;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MoveCollegeFirstYearSubjectsToSecondTerm extends Command
{
    protected const EXCLUDED_NAME = 'البرنامج التحضيري';

    protected $signature = 'college-subjects:move-to-second-term
                            {--dry-run : عرض الأعداد دون تحديث قاعدة البيانات}';

    protected $description = 'نقل مواد الفصل الأول (السنة الأولى) إلى الفصل الثاني لكل كلية، مع إبقاء مادة «البرنامج التحضيري» في الفصل الأول فقط.';

    public function handle(): int
    {
        $dry = $this->option('dry-run');

        if ($dry) {
            $this->warn('وضع المعاينة — لن يُجرَ أي تعديل.');
        }

        $this->ensureSecondTermsExist($dry);

        $subjectSql = <<<'SQL'
UPDATE subjects s
INNER JOIN registrable_entities re
    ON re.entity_type = 'college' AND re.entity_id = s.college_id
INNER JOIN study_years sy
    ON sy.registrable_entity_id = re.id AND sy.sort_order = 1
INNER JOIN study_terms t1
    ON t1.study_year_id = sy.id AND t1.sort_order = 1
INNER JOIN study_terms t2
    ON t2.study_year_id = sy.id AND t2.sort_order = 2
SET s.study_term_id = t2.id, s.updated_at = NOW()
WHERE s.study_term_id = t1.id
  AND TRIM(s.name) <> ?
SQL;

        $regSql = <<<'SQL'
UPDATE registrable_subjects rs
INNER JOIN registrable_entities re
    ON re.id = rs.registrable_entity_id AND re.entity_type = 'college'
INNER JOIN study_years sy
    ON sy.registrable_entity_id = re.id AND sy.sort_order = 1
INNER JOIN study_terms t1
    ON t1.study_year_id = sy.id AND t1.sort_order = 1
INNER JOIN study_terms t2
    ON t2.study_year_id = sy.id AND t2.sort_order = 2
SET rs.study_term_id = t2.id, rs.updated_at = NOW()
WHERE rs.study_term_id = t1.id
  AND TRIM(rs.name) <> ?
SQL;

        if ($dry) {
            $nSub = $this->countSubjectsToMove();
            $nReg = $this->countRegistrableToMove();
            $this->info("مواد (subjects) المراد نقلها: {$nSub}");
            $this->info("مواد قابلة للتسجيل (registrable_subjects) المراد نقلها: {$nReg}");

            return self::SUCCESS;
        }

        DB::transaction(function () use ($subjectSql, $regSql): void {
            $a = DB::update($subjectSql, [self::EXCLUDED_NAME]);
            $b = DB::update($regSql, [self::EXCLUDED_NAME]);
            $this->info("تم تحديث subjects: {$a} صفًا.");
            $this->info("تم تحديث registrable_subjects: {$b} صفًا.");
        });

        return self::SUCCESS;
    }

    private function ensureSecondTermsExist(bool $dry): void
    {
        $collegeEntities = RegistrableEntity::query()
            ->where('entity_type', 'college')
            ->get();

        foreach ($collegeEntities as $entity) {
            $year = StudyYear::query()
                ->where('registrable_entity_id', $entity->id)
                ->where('sort_order', 1)
                ->first();

            if (! $year) {
                continue;
            }

            $exists = StudyTerm::query()
                ->where('study_year_id', $year->id)
                ->where('sort_order', 2)
                ->exists();

            if ($exists) {
                continue;
            }

            if ($dry) {
                $this->line(" [معاينة] ستُنشأ «الفصل الثاني» للسنة الأولى — كيان كلية ID {$entity->id}.");

                continue;
            }

            StudyTerm::query()->create([
                'study_year_id' => $year->id,
                'name' => 'الفصل الثاني',
                'code' => null,
                'sort_order' => 2,
            ]);

            $this->info("أُنشئ «الفصل الثاني» للسنة الأولى — كيان كلية ID {$entity->id}.");
        }
    }

    private function countSubjectsToMove(): int
    {
        return (int) DB::table('subjects as s')
            ->join('registrable_entities as re', function ($join): void {
                $join->where('re.entity_type', '=', 'college')
                    ->whereColumn('re.entity_id', 's.college_id');
            })
            ->join('study_years as sy', function ($join): void {
                $join->whereColumn('sy.registrable_entity_id', 're.id')
                    ->where('sy.sort_order', '=', 1);
            })
            ->join('study_terms as t1', function ($join): void {
                $join->whereColumn('t1.study_year_id', 'sy.id')
                    ->where('t1.sort_order', '=', 1);
            })
            ->whereColumn('s.study_term_id', 't1.id')
            ->whereRaw('TRIM(s.name) <> ?', [self::EXCLUDED_NAME])
            ->count();
    }

    private function countRegistrableToMove(): int
    {
        return (int) DB::table('registrable_subjects as rs')
            ->join('registrable_entities as re', function ($join): void {
                $join->whereColumn('re.id', 'rs.registrable_entity_id')
                    ->where('re.entity_type', '=', 'college');
            })
            ->join('study_years as sy', function ($join): void {
                $join->whereColumn('sy.registrable_entity_id', 're.id')
                    ->where('sy.sort_order', '=', 1);
            })
            ->join('study_terms as t1', function ($join): void {
                $join->whereColumn('t1.study_year_id', 'sy.id')
                    ->where('t1.sort_order', '=', 1);
            })
            ->whereColumn('rs.study_term_id', 't1.id')
            ->whereRaw('TRIM(rs.name) <> ?', [self::EXCLUDED_NAME])
            ->count();
    }
}
