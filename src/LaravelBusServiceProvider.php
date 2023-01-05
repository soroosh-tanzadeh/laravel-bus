<?php

namespace Soroosh\LaravelBus;

use Soroosh\LaravelBus\Console\MakeJob;
use Illuminate\Support\ServiceProvider;

class LaravelBusServiceProvider extends ServiceProvider
{
    public function register()
    {

    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-bus');
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeJob::class,
            ]);
        }
    }
}
