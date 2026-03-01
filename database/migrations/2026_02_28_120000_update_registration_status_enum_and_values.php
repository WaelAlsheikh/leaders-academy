<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE registrations
            MODIFY status ENUM('pending','approved','paid','under_review','accepted','rejected') NOT NULL DEFAULT 'pending'
        ");

        DB::statement("
            UPDATE registrations
            SET status = CASE
                WHEN academic_status = 'accepted' THEN 'accepted'
                WHEN academic_status = 'rejected' THEN 'rejected'
                WHEN status IN ('approved', 'paid') THEN 'accepted'
                WHEN status IN ('under_review', 'accepted', 'rejected') THEN status
                ELSE 'under_review'
            END
        ");

        DB::statement("
            ALTER TABLE registrations
            MODIFY status ENUM('under_review','accepted','rejected') NOT NULL DEFAULT 'under_review'
        ");

        if ($this->hasAcademicStatusColumn()) {
            DB::statement("
                UPDATE registrations
                SET academic_status = CASE
                    WHEN status = 'accepted' THEN 'accepted'
                    WHEN status = 'rejected' THEN 'rejected'
                    ELSE 'submitted'
                END
            ");
        }
    }

    public function down(): void
    {
        DB::statement("
            UPDATE registrations
            SET status = CASE
                WHEN status = 'accepted' THEN 'approved'
                WHEN status = 'rejected' THEN 'pending'
                ELSE 'pending'
            END
        ");

        DB::statement("
            ALTER TABLE registrations
            MODIFY status ENUM('pending','approved','paid') NOT NULL DEFAULT 'pending'
        ");
    }

    private function hasAcademicStatusColumn(): bool
    {
        $columns = DB::select("SHOW COLUMNS FROM registrations LIKE 'academic_status'");
        return count($columns) > 0;
    }
};
