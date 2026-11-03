<?php

namespace App\Providers;

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
        // ponytail: jangan pernah bocorin stack trace/path/env di produksi,
        // walau APP_DEBUG di .env salah set jadi true.
        if ($this->app->environment('production')) {
            config(['app.debug' => false]);
        }
    }
}
