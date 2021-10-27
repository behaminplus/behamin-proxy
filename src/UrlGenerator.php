<?php


namespace Behamin\ServiceProxy;


use http\Exception\InvalidArgumentException;
use PharIo\Manifest\InvalidUrlException;

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
     * @param $url
     */
    private static function checkIfUrlIsValid($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Url isn't set in bsproxy config file");
        }
    }

    /**
     * @return string
     */
    private static function getConfigBaseUrl(): string
    {
        // TODO check if url is a valid url
        $url = config('bsproxy.proxy_base_url');
        if ($url == null) {
            throw new InvalidUrlException("Url isn't set in bsproxy config file");
        }
        self::checkIfUrlIsValid($url);
        return $url;
    }
}
