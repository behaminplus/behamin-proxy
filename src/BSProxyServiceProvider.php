<?php

namespace Behamin\BSProxy;

use Illuminate\Support\ServiceProvider;

class BSProxyServiceProvider extends ServiceProvider
{
    public function register()
    {
        parent::register();
        $this->mergeConfigFrom(
            __DIR__ . '/../config/bsproxy.php',
            'bsproxy'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . '/../config/bsproxy.php' => config_path(
                        'bsproxy.php'
                    )
                ],
                'config'
            );
        }
    }
}
