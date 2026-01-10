<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        // إذا كان هناك لغة محفوظة في الجلسة استخدمها
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } else {
            // اللغة الافتراضية العربية
            App::setLocale('ar');
            Session::put('locale', 'ar');
        }

        return $next($request);
    }
}
