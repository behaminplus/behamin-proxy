<?php

namespace BSProxy;

use Illuminate\Support\ServiceProvider;

class BSProxyServiceProvider extends ServiceProvider
{
    public function register()
    {
        parent::register();
        $this->mergeConfigFrom(
            __DIR__ . '/../config/proxy-services-url.php',
            'proxy-services-url'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . '/../config/proxy-services-url.php' => config_path(
                        'proxy-services-url.php'
                    )
                ],
                'config'
            );
        }
    }
}
