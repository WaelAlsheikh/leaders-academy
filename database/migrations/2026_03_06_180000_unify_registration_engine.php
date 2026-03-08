<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrable_entities', function (Blueprint $table) {
            $table->id();
            $table->enum('entity_type', ['college', 'program_branch', 'training_program_branch']);
            $table->unsignedBigInteger('entity_id');
            $table->string('title_snapshot')->nullable();
            $table->decimal('price_per_credit_hour', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['entity_type', 'entity_id'], 'reg_entity_unique');
            $table->index(['entity_type', 'entity_id'], 'reg_entity_lookup_idx');
        });

        Schema::create('registrable_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registrable_entity_id')->constrained('registrable_entities')->cascadeOnDelete();
            $table->unsignedBigInteger('legacy_subject_id')->nullable();
            $table->string('name');
            $table->string('code')->nullable();
            $table->unsignedInteger('credit_hours');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['registrable_entity_id', 'code'], 'reg_subject_code_unique');
            $table->index(['registrable_entity_id', 'is_active'], 'reg_subject_active_idx');
        });

        if (Schema::hasTable('subjects')) {
            Schema::table('registrable_subjects', function (Blueprint $table) {
                $table->foreign('legacy_subject_id')
                    ->references('id')
                    ->on('subjects')
                    ->nullOnDelete();
            });
        }

        Schema::create('enrollment_cycle_registrable_subject', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enrollment_cycle_id');
            $table->unsignedBigInteger('registrable_subject_id');
            $table->boolean('is_open')->default(true);
            $table->timestamps();

            $table->unique(['enrollment_cycle_id', 'registrable_subject_id'], 'cycle_reg_subject_unique');
            $table->foreign('enrollment_cycle_id', 'ec_rs_cycle_fk')
                ->references('id')
                ->on('enrollment_cycles')
                ->cascadeOnDelete();
            $table->foreign('registrable_subject_id', 'ec_rs_subject_fk')
                ->references('id')
                ->on('registrable_subjects')
                ->cascadeOnDelete();
        });

        Schema::create('registration_registrable_subject', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registration_id');
            $table->unsignedBigInteger('registrable_subject_id');
            $table->unsignedInteger('credit_hours');
            $table->decimal('price_per_hour', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();

            $table->unique(['registration_id', 'registrable_subject_id'], 'registration_reg_subject_unique');
            $table->foreign('registration_id', 'rrs_registration_fk')
                ->references('id')
                ->on('registrations')
                ->cascadeOnDelete();
            $table->foreign('registrable_subject_id', 'rrs_subject_fk')
                ->references('id')
                ->on('registrable_subjects')
                ->cascadeOnDelete();
        });

        Schema::table('enrollment_cycles', function (Blueprint $table) {
            $table->foreignId('registrable_entity_id')
                ->nullable()
                ->after('college_id')
                ->constrained('registrable_entities')
                ->nullOnDelete();
        });
        DB::statement('ALTER TABLE enrollment_cycles MODIFY college_id BIGINT UNSIGNED NULL');

        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignId('registrable_entity_id')
                ->nullable()
                ->after('college_id')
                ->constrained('registrable_entities')
                ->nullOnDelete();
        });
        DB::statement('ALTER TABLE registrations MODIFY college_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE semesters MODIFY college_id BIGINT UNSIGNED NULL');

        Schema::table('semester_subject', function (Blueprint $table) {
            $table->foreignId('registrable_subject_id')
                ->nullable()
                ->after('subject_id')
                ->constrained('registrable_subjects')
                ->nullOnDelete();
        });

        DB::statement('ALTER TABLE semester_subject MODIFY subject_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE class_sections MODIFY subject_id BIGINT UNSIGNED NULL');

        Schema::table('class_sections', function (Blueprint $table) {
            $table->foreignId('registrable_subject_id')
                ->nullable()
                ->after('subject_id')
                ->constrained('registrable_subjects')
                ->nullOnDelete();
        });

        DB::table('colleges')->orderBy('id')->chunk(200, function ($colleges): void {
            foreach ($colleges as $college) {
                DB::table('registrable_entities')->updateOrInsert(
                    ['entity_type' => 'college', 'entity_id' => $college->id],
                    [
                        'title_snapshot' => $college->title,
                        'price_per_credit_hour' => $college->price_per_credit_hour ?? 0,
                        'is_active' => true,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        });

        if (Schema::hasTable('program_branches')) {
            $programBranchHasPrice = Schema::hasColumn('program_branches', 'price_per_credit_hour');
            DB::table('program_branches')->orderBy('id')->chunk(200, function ($branches) use ($programBranchHasPrice): void {
                foreach ($branches as $branch) {
                    DB::table('registrable_entities')->updateOrInsert(
                        ['entity_type' => 'program_branch', 'entity_id' => $branch->id],
                        [
                            'title_snapshot' => $branch->title,
                            'price_per_credit_hour' => $programBranchHasPrice ? ($branch->price_per_credit_hour ?? 0) : 0,
                            'is_active' => (bool) ($branch->is_active ?? true),
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            });
        }

        if (Schema::hasTable('training_program_branches')) {
            $trainingBranchHasPrice = Schema::hasColumn('training_program_branches', 'price_per_credit_hour');
            DB::table('training_program_branches')->orderBy('id')->chunk(200, function ($branches) use ($trainingBranchHasPrice): void {
                foreach ($branches as $branch) {
                    DB::table('registrable_entities')->updateOrInsert(
                        ['entity_type' => 'training_program_branch', 'entity_id' => $branch->id],
                        [
                            'title_snapshot' => $branch->title,
                            'price_per_credit_hour' => $trainingBranchHasPrice ? ($branch->price_per_credit_hour ?? 0) : 0,
                            'is_active' => (bool) ($branch->is_active ?? true),
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            });
        }

        DB::statement("
            UPDATE enrollment_cycles ec
            JOIN registrable_entities re
              ON re.entity_type = 'college' AND re.entity_id = ec.college_id
            SET ec.registrable_entity_id = re.id
            WHERE ec.registrable_entity_id IS NULL
        ");

        DB::statement("
            UPDATE registrations r
            JOIN registrable_entities re
              ON re.entity_type = 'college' AND re.entity_id = r.college_id
            SET r.registrable_entity_id = re.id
            WHERE r.registrable_entity_id IS NULL
        ");

        if (Schema::hasTable('subjects')) {
            DB::statement("
                INSERT INTO registrable_subjects
                    (registrable_entity_id, legacy_subject_id, name, code, credit_hours, is_active, created_at, updated_at)
                SELECT re.id, s.id, s.name, s.code, s.credit_hours, s.is_active, NOW(), NOW()
                FROM subjects s
                JOIN registrable_entities re
                  ON re.entity_type = 'college' AND re.entity_id = s.college_id
            ");
        }

        if (Schema::hasTable('enrollment_cycle_subject')) {
            DB::statement("
                INSERT INTO enrollment_cycle_registrable_subject
                    (enrollment_cycle_id, registrable_subject_id, is_open, created_at, updated_at)
                SELECT ecs.enrollment_cycle_id, rs.id, ecs.is_open, NOW(), NOW()
                FROM enrollment_cycle_subject ecs
                JOIN registrable_subjects rs
                  ON rs.legacy_subject_id = ecs.subject_id
                JOIN enrollment_cycles ec
                  ON ec.id = ecs.enrollment_cycle_id AND ec.registrable_entity_id = rs.registrable_entity_id
            ");
        }

        if (Schema::hasTable('registration_subject')) {
            DB::statement("
                INSERT INTO registration_registrable_subject
                    (registration_id, registrable_subject_id, credit_hours, price_per_hour, total_price, created_at, updated_at)
                SELECT rp.registration_id, rs.id, rp.credit_hours, rp.price_per_hour, rp.total_price, NOW(), NOW()
                FROM registration_subject rp
                JOIN registrations r
                  ON r.id = rp.registration_id
                JOIN registrable_subjects rs
                  ON rs.legacy_subject_id = rp.subject_id
                 AND rs.registrable_entity_id = r.registrable_entity_id
            ");
        }

        DB::statement("
            UPDATE semester_subject ss
            JOIN registrable_subjects rs ON rs.legacy_subject_id = ss.subject_id
            JOIN semesters sem ON sem.id = ss.semester_id
            JOIN enrollment_cycles ec ON ec.id = sem.enrollment_cycle_id
            SET ss.registrable_subject_id = rs.id
            WHERE ec.registrable_entity_id = rs.registrable_entity_id
        ");

        DB::statement("
            UPDATE class_sections cs
            JOIN registrable_subjects rs ON rs.legacy_subject_id = cs.subject_id
            JOIN semesters sem ON sem.id = cs.semester_id
            JOIN enrollment_cycles ec ON ec.id = sem.enrollment_cycle_id
            SET cs.registrable_subject_id = rs.id
            WHERE ec.registrable_entity_id = rs.registrable_entity_id
        ");
    }

    public function down(): void
    {
        Schema::table('class_sections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('registrable_subject_id');
        });

        DB::statement('ALTER TABLE class_sections MODIFY subject_id BIGINT UNSIGNED NOT NULL');

        Schema::table('semester_subject', function (Blueprint $table) {
            $table->dropConstrainedForeignId('registrable_subject_id');
        });

        DB::statement('ALTER TABLE semester_subject MODIFY subject_id BIGINT UNSIGNED NOT NULL');

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('registrable_entity_id');
        });

        Schema::table('enrollment_cycles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('registrable_entity_id');
        });
        DB::statement('ALTER TABLE enrollment_cycles MODIFY college_id BIGINT UNSIGNED NOT NULL');

        Schema::dropIfExists('registration_registrable_subject');
        Schema::dropIfExists('enrollment_cycle_registrable_subject');
        Schema::dropIfExists('registrable_subjects');
        Schema::dropIfExists('registrable_entities');

        DB::statement('ALTER TABLE registrations MODIFY college_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE semesters MODIFY college_id BIGINT UNSIGNED NOT NULL');
    }
};
