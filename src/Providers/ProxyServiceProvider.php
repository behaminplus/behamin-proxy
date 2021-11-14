<?php

namespace Behamin\ServiceProxy\Providers;

use Illuminate\Support\ServiceProvider;

class ProxyServiceProvider extends ServiceProvider
{
    public function register()
    {
        parent::register();
        $this->mergeConfigFrom(
            __DIR__.'/../config/proxy.php',
            'proxy'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [__DIR__.'/../config/proxy.php' => config_path('proxy.php')],
                'config'
            );
        }
    }
}
