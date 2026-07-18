<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus') || ! Schema::hasTable('menu_items')) {
            return;
        }

        $menuId = DB::table('menus')->where('name', 'admin')->value('id');

        if (! $menuId) {
            return;
        }

        $exists = DB::table('menu_items')
            ->where('menu_id', $menuId)
            ->where(function ($query) {
                $query->where('route', 'admin.exam_question_bank.index')
                    ->orWhere('title', 'بنك الأسئلة')
                    ->orWhere('url', '/admin/exam-question-bank');
            })
            ->exists();

        if ($exists) {
            return;
        }

        $examsItem = DB::table('menu_items')
            ->where('menu_id', $menuId)
            ->where(function ($query) {
                $query->where('url', '/admin/exams')
                    ->orWhere('title', 'الامتحانات');
            })
            ->orderBy('order')
            ->first();

        $insertOrder = $examsItem ? ((int) $examsItem->order + 1) : ((int) DB::table('menu_items')->where('menu_id', $menuId)->max('order') + 1);

        DB::table('menu_items')
            ->where('menu_id', $menuId)
            ->where('order', '>=', $insertOrder)
            ->increment('order');

        DB::table('menu_items')->insert([
            'menu_id' => $menuId,
            'title' => 'بنك الأسئلة',
            'url' => '/admin/exam-question-bank',
            'target' => '_self',
            'icon_class' => 'voyager-list',
            'color' => null,
            'parent_id' => null,
            'order' => $insertOrder,
            'route' => null,
            'parameters' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('menus') || ! Schema::hasTable('menu_items')) {
            return;
        }

        $menuId = DB::table('menus')->where('name', 'admin')->value('id');

        DB::table('menu_items')
            ->when($menuId, fn ($query) => $query->where('menu_id', $menuId))
            ->where(function ($query) {
                $query->where('route', 'admin.exam_question_bank.index')
                    ->orWhere('title', 'بنك الأسئلة')
                    ->orWhere('url', '/admin/exam-question-bank');
            })
            ->delete();
    }
};
