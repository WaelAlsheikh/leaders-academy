<?php

namespace App\Domain\Email\Providers;

use App\Domain\Email\Contracts\MailServerDriver;
use App\Domain\Email\Drivers\LogMailServerDriver;
use App\Domain\Email\Drivers\PostfixVirtualDriver;
use App\Domain\Email\Observers\IdentityMailObserver;
use App\Models\Doctor;
use App\Models\Employee;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\ServiceProvider;

class EmailModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MailServerDriver::class, function () {
            return match (config('email_module.driver', 'log')) {
                'postfix_virtual' => new PostfixVirtualDriver,
                default => new LogMailServerDriver,
            };
        });
    }

    public function boot(): void
    {
        Student::observe(IdentityMailObserver::class);
        Doctor::observe(IdentityMailObserver::class);
        Employee::observe(IdentityMailObserver::class);
        User::observe(IdentityMailObserver::class);
    }
}
