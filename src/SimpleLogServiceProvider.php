<?php
namespace Kopaing\SimpleLog;

use Illuminate\Support\ServiceProvider;

class SimpleLogServiceProvider extends ServiceProvider
{
    public function boot()
    {
         // Load package migrations
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        // Merge package configuration
        $this->mergeConfigFrom(
            __DIR__.'/config/log.php', 'simplelog'
        );

        // Publish models to the application
        $this->publishes([
            __DIR__.'/Models/ActivityLog.php' => app_path('/Models/ActivityLog.php'),
        ], 'models');

        // Publish package configuration
        $this->publishes([
            __DIR__.'/config/log.php' => config_path('log.php'),
        ], 'config');

        $this->commands([
            \Kopaing\SimpleLog\Console\PurgeOldLogs::class,
        ]);


        

    }

    public function register()
    {

    }
}
