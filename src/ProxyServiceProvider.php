<?php

namespace Behamin\ServiceProxy;

use Illuminate\Support\ServiceProvider;

class ProxyServiceProvider extends ServiceProvider
{
    public function register()
    {
        parent::register();
        $this->mergeConfigFrom(
            __DIR__.'/../config/proxy.php',
            'bsproxy'
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
