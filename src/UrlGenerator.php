<?php

namespace Behamin\ServiceProxy;

use InvalidArgumentException;

class UrlGenerator
{
    /**
     * @param  string|null  $baseUrl
     * @return string
     */
    public static function baseUrl(?string $baseUrl): string
    {
        return $baseUrl ?: config('proxy.base_url');
    }
}
