<?php

namespace Arvan\LaravelBus;

use Arvan\LaravelBus\Console\MakeJob;
use Illuminate\Support\ServiceProvider;

class LaravelBusServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeJob::class,
            ]);
        }
    }
}