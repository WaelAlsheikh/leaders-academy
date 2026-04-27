<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->dateTime('registration_starts_at')->nullable();
            $table->dateTime('registration_ends_at')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::table('enrollment_cycles', function (Blueprint $table) {
            $table->foreignId('registration_season_id')
                ->nullable()
                ->after('id')
                ->constrained('registration_seasons')
                ->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true)->after('status');
        });

        $this->backfillRegistrationSeasons();

        Schema::table('enrollment_cycles', function (Blueprint $table) {
            $table->unique(
                ['registration_season_id', 'registrable_entity_id'],
                'enrollment_cycles_season_entity_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('enrollment_cycles', function (Blueprint $table) {
            $table->dropUnique('enrollment_cycles_season_entity_unique');
            $table->dropConstrainedForeignId('registration_season_id');
            $table->dropColumn('is_enabled');
        });

        Schema::dropIfExists('registration_seasons');
    }

    private function backfillRegistrationSeasons(): void
    {
        DB::table('enrollment_cycles')
            ->orderBy('id')
            ->chunk(100, function ($cycles): void {
                foreach ($cycles as $cycle) {
                    $seasonId = DB::table('registration_seasons')->insertGetId([
                        'name' => $cycle->name,
                        'code' => $cycle->code,
                        'registration_starts_at' => $cycle->registration_starts_at,
                        'registration_ends_at' => $cycle->registration_ends_at,
                        'status' => 'closed',
                        'created_by' => $cycle->created_by,
                        'created_at' => $cycle->created_at,
                        'updated_at' => $cycle->updated_at,
                    ]);

                    DB::table('enrollment_cycles')
                        ->where('id', $cycle->id)
                        ->update([
                            'registration_season_id' => $seasonId,
                            'is_enabled' => true,
                        ]);
                }
            });
    }
};
