<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['students', 'doctors', 'employees', 'users'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'institutional_email')) {
                    $table->string('institutional_email')->nullable()->unique()->after('email');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['students', 'doctors', 'employees', 'users'] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'institutional_email')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropUnique(['institutional_email']);
                $table->dropColumn('institutional_email');
            });
        }
    }
};
