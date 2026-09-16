<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        if (!App::runningInConsole()) {
            // Default Datetime Config
            config(['app.locale' => 'id']);
            \Carbon\Carbon::setLocale('id');
            date_default_timezone_set('Asia/Jakarta');

            Blade::if('role', function (string $value) {
                return auth()->user()->role == $value;
            });
        }
    }
}
