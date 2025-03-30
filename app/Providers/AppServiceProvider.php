<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\InitializeDepartmentRosters;
use App\Console\Commands\CheckStaffDistribution;

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
        // Register the command if we are in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                InitializeDepartmentRosters::class,
                CheckStaffDistribution::class,
            ]);
        }
    }
}
