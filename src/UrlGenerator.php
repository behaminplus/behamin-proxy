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
        return $baseUrl ?: self::getConfigBaseUrl();
    }

    /**
     * @return string
     */
    private static function getConfigBaseUrl(): string
    {
        $url = config('proxy.base_url');
        $isLocalUrl = config('proxy.is_local_url');

        if (!$isLocalUrl) {
            if (empty($url) || !is_string($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                throw new InvalidArgumentException("Invalid or empty URL provided.");
            }
        }

        return $url;
    }
}
