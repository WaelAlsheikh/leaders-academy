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
                $query->where('route', 'admin.employees.index')
                    ->orWhere('title', 'إدارة الموظفين');
            })
            ->exists();

        if ($exists) {
            return;
        }

        $nextOrder = (int) DB::table('menu_items')
            ->where('menu_id', $menuId)
            ->max('order') + 1;

        DB::table('menu_items')->insert([
            'menu_id' => $menuId,
            'title' => 'إدارة الموظفين',
            'url' => '',
            'target' => '_self',
            'icon_class' => 'voyager-people',
            'color' => '#5cb85c',
            'parent_id' => null,
            'order' => $nextOrder,
            'route' => 'admin.employees.index',
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
                $query->where('route', 'admin.employees.index')
                    ->orWhere('title', 'إدارة الموظفين');
            })
            ->delete();
    }
};
