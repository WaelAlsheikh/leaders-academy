<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registrable_entity_id')->constrained('registrable_entities')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();

            $table->unique(['registrable_entity_id', 'sort_order'], 'study_years_entity_order_unique');
        });

        Schema::create('study_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_year_id')->constrained('study_years')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();

            $table->unique(['study_year_id', 'sort_order'], 'study_terms_year_order_unique');
        });

        Schema::table('colleges', function (Blueprint $table) {
            $table->string('code')->nullable()->after('title');
        });

        Schema::table('program_branches', function (Blueprint $table) {
            $table->string('code')->nullable()->after('title');
        });

        Schema::table('training_program_branches', function (Blueprint $table) {
            $table->string('code')->nullable()->after('title');
        });

        Schema::table('enrollment_cycles', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('study_term_id')
                ->nullable()
                ->after('college_id')
                ->constrained('study_terms')
                ->nullOnDelete();
        });

        Schema::table('registrable_subjects', function (Blueprint $table) {
            $table->foreignId('study_term_id')
                ->nullable()
                ->after('registrable_entity_id')
                ->constrained('study_terms')
                ->nullOnDelete();
        });

        Schema::table('registration_registrable_subject', function (Blueprint $table) {
            $table->enum('result_status', ['undefined', 'passed', 'failed'])
                ->default('undefined')
                ->after('total_price');
        });

        $this->backfillRegistrableEntities();
        $defaultTermIdsByEntity = $this->seedDefaultStudyStructure();
        $this->backfillStudyTermIds($defaultTermIdsByEntity);
    }

    public function down(): void
    {
        Schema::table('registration_registrable_subject', function (Blueprint $table) {
            $table->dropColumn('result_status');
        });

        Schema::table('registrable_subjects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('study_term_id');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('study_term_id');
        });

        Schema::table('enrollment_cycles', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('training_program_branches', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('program_branches', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('colleges', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::dropIfExists('study_terms');
        Schema::dropIfExists('study_years');
    }

    private function backfillRegistrableEntities(): void
    {
        $now = now();

        DB::table('colleges')
            ->select('id', 'title', 'price_per_credit_hour')
            ->orderBy('id')
            ->chunk(200, function ($colleges) use ($now): void {
                foreach ($colleges as $college) {
                    $existing = DB::table('registrable_entities')
                        ->where('entity_type', 'college')
                        ->where('entity_id', $college->id)
                        ->exists();

                    if ($existing) {
                        continue;
                    }

                    DB::table('registrable_entities')->insert([
                        'entity_type' => 'college',
                        'entity_id' => $college->id,
                        'title_snapshot' => $college->title,
                        'price_per_credit_hour' => $college->price_per_credit_hour ?? 0,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });

        DB::table('program_branches')
            ->select('id', 'title', 'is_active', 'price_per_credit_hour')
            ->orderBy('id')
            ->chunk(200, function ($branches) use ($now): void {
                foreach ($branches as $branch) {
                    $existing = DB::table('registrable_entities')
                        ->where('entity_type', 'program_branch')
                        ->where('entity_id', $branch->id)
                        ->exists();

                    if ($existing) {
                        continue;
                    }

                    DB::table('registrable_entities')->insert([
                        'entity_type' => 'program_branch',
                        'entity_id' => $branch->id,
                        'title_snapshot' => $branch->title,
                        'price_per_credit_hour' => $branch->price_per_credit_hour ?? 0,
                        'is_active' => (bool) ($branch->is_active ?? true),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });

        DB::table('training_program_branches')
            ->select('id', 'title', 'is_active', 'price_per_credit_hour')
            ->orderBy('id')
            ->chunk(200, function ($branches) use ($now): void {
                foreach ($branches as $branch) {
                    $existing = DB::table('registrable_entities')
                        ->where('entity_type', 'training_program_branch')
                        ->where('entity_id', $branch->id)
                        ->exists();

                    if ($existing) {
                        continue;
                    }

                    DB::table('registrable_entities')->insert([
                        'entity_type' => 'training_program_branch',
                        'entity_id' => $branch->id,
                        'title_snapshot' => $branch->title,
                        'price_per_credit_hour' => $branch->price_per_credit_hour ?? 0,
                        'is_active' => (bool) ($branch->is_active ?? true),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });
    }

    /**
     * @return array<int, int>
     */
    private function seedDefaultStudyStructure(): array
    {
        $now = now();
        $defaultTermIdsByEntity = [];

        DB::table('registrable_entities')
            ->select('id')
            ->orderBy('id')
            ->chunk(200, function ($entities) use (&$defaultTermIdsByEntity, $now): void {
                foreach ($entities as $entity) {
                    $studyYearId = DB::table('study_years')
                        ->where('registrable_entity_id', $entity->id)
                        ->where('sort_order', 1)
                        ->value('id');

                    if (!$studyYearId) {
                        $studyYearId = DB::table('study_years')->insertGetId([
                            'registrable_entity_id' => $entity->id,
                            'name' => 'السنة الأولى',
                            'sort_order' => 1,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }

                    $studyTermId = DB::table('study_terms')
                        ->where('study_year_id', $studyYearId)
                        ->where('sort_order', 1)
                        ->value('id');

                    if (!$studyTermId) {
                        $studyTermId = DB::table('study_terms')->insertGetId([
                            'study_year_id' => $studyYearId,
                            'name' => 'الفصل الأول',
                            'code' => null,
                            'sort_order' => 1,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }

                    $defaultTermIdsByEntity[$entity->id] = $studyTermId;
                }
            });

        return $defaultTermIdsByEntity;
    }

    /**
     * @param array<int, int> $defaultTermIdsByEntity
     */
    private function backfillStudyTermIds(array $defaultTermIdsByEntity): void
    {
        foreach ($defaultTermIdsByEntity as $entityId => $studyTermId) {
            DB::table('registrable_subjects')
                ->where('registrable_entity_id', $entityId)
                ->whereNull('study_term_id')
                ->update(['study_term_id' => $studyTermId]);
        }

        $collegeEntityIds = DB::table('registrable_entities')
            ->where('entity_type', 'college')
            ->pluck('id', 'entity_id');

        foreach ($collegeEntityIds as $collegeId => $entityId) {
            $studyTermId = $defaultTermIdsByEntity[$entityId] ?? null;

            if (!$studyTermId) {
                continue;
            }

            DB::table('subjects')
                ->where('college_id', $collegeId)
                ->whereNull('study_term_id')
                ->update(['study_term_id' => $studyTermId]);
        }
    }
};
