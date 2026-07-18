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
                $query->where('title', 'الوظائف')
                    ->orWhere('url', '/admin/assignments');
            })
            ->exists();

        if ($exists) {
            return;
        }

        $anchor = DB::table('menu_items')
            ->where('menu_id', $menuId)
            ->where(function ($query) {
                $query->where('title', 'بنك الأسئلة')
                    ->orWhere('url', '/admin/exam-question-bank')
                    ->orWhere('url', '/admin/exams')
                    ->orWhere('title', 'الامتحانات');
            })
            ->orderByDesc('order')
            ->first();

        $insertOrder = $anchor ? ((int) $anchor->order + 1) : ((int) DB::table('menu_items')->where('menu_id', $menuId)->max('order') + 1);

        DB::table('menu_items')
            ->where('menu_id', $menuId)
            ->where('order', '>=', $insertOrder)
            ->increment('order');

        DB::table('menu_items')->insert([
            'menu_id' => $menuId,
            'title' => 'الوظائف',
            'url' => '/admin/assignments',
            'target' => '_self',
            'icon_class' => 'voyager-folder',
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
                $query->where('title', 'الوظائف')
                    ->orWhere('url', '/admin/assignments');
            })
            ->delete();
    }
};
