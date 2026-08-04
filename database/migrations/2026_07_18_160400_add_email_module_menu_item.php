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
            ->where(function ($q) {
                $q->where('title', 'البريد المؤسسي')
                    ->orWhere('url', '/admin/email/accounts');
            })
            ->exists();

        if ($exists) {
            return;
        }

        $anchor = DB::table('menu_items')
            ->where('menu_id', $menuId)
            ->where(function ($q) {
                $q->where('title', 'الوظائف')->orWhere('url', '/admin/assignments');
            })
            ->orderByDesc('order')
            ->first();

        $order = $anchor ? ((int) $anchor->order + 1) : ((int) DB::table('menu_items')->where('menu_id', $menuId)->max('order') + 1);

        DB::table('menu_items')->where('menu_id', $menuId)->where('order', '>=', $order)->increment('order');

        DB::table('menu_items')->insert([
            'menu_id' => $menuId,
            'title' => 'البريد المؤسسي',
            'url' => '/admin/email/accounts',
            'target' => '_self',
            'icon_class' => 'voyager-mail',
            'color' => null,
            'parent_id' => null,
            'order' => $order,
            'route' => null,
            'parameters' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('menu_items')) {
            return;
        }

        DB::table('menu_items')
            ->where('title', 'البريد المؤسسي')
            ->orWhere('url', '/admin/email/accounts')
            ->delete();
    }
};
