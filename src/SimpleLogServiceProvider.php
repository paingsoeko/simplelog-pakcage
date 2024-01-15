<?php
namespace Kopaing\SimpleLog;

use Illuminate\Support\ServiceProvider;

class SimpleLogServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->mergeConfigFrom(
            __DIR__.'/config/log.php', 'simplelog'
        );

        $this->publishes([
            __DIR__.'/config/log.php' => config_path('log.php'),
        ]);

    }

    public function register()
    {

    }
}
