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
        return self::getConfigBaseUrl();
    }

    /**
     * @return string
     */
    private static function getConfigBaseUrl(): string
    {
        $url = config('proxy.base_url');

        if (empty($url) || !is_string($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Invalid or empty base_url in config file.");
        }

        return $url;
    }
}
