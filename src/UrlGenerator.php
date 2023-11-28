<?php

namespace Behamin\ServiceProxy;

use InvalidArgumentException;

class UrlGenerator
{
    /**
     * @return string
     */
    public static function baseUrl(): string
    {
        return config('proxy.base_url');
    }
}
