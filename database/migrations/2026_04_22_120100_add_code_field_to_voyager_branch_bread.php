<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $dataTypeIds = DB::table('data_types')
            ->whereIn('slug', ['program-branches', 'training-program-branches'])
            ->pluck('id', 'slug');

        foreach ($dataTypeIds as $slug => $dataTypeId) {
            $exists = DB::table('data_rows')
                ->where('data_type_id', $dataTypeId)
                ->where('field', 'code')
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('data_rows')
                ->where('data_type_id', $dataTypeId)
                ->where('order', '>', 3)
                ->increment('order');

            DB::table('data_rows')->insert([
                'data_type_id' => $dataTypeId,
                'field' => 'code',
                'type' => 'text',
                'display_name' => 'الرمز',
                'required' => 0,
                'browse' => 1,
                'read' => 1,
                'edit' => 1,
                'add' => 1,
                'delete' => 1,
                'details' => json_encode((object) [
                    'validation' => (object) [
                        'rule' => 'nullable|string|max:255',
                    ],
                ]),
                'order' => 4,
            ]);
        }
    }

    public function down(): void
    {
        $dataTypeIds = DB::table('data_types')
            ->whereIn('slug', ['program-branches', 'training-program-branches'])
            ->pluck('id');

        DB::table('data_rows')
            ->whereIn('data_type_id', $dataTypeIds)
            ->where('field', 'code')
            ->delete();
    }
};
