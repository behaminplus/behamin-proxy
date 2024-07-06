<?php

namespace Behamin\ServiceProxy;

use InvalidArgumentException;

class UrlGenerator
{
    /**
     * @param string|null $baseUrl
     * @return string|null
     */
    public static function baseUrl(?string $baseUrl): string|null
    {
        return $baseUrl ?: config('proxy.base_url');
    }
}
