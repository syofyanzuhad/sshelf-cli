<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\ConfigManager::class);
        $this->app->singleton(\App\Services\ApiClient::class);
        $this->app->singleton(\App\Services\OutputFormatter::class);
    }
}
