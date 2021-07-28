<?php


namespace Behamin\ServiceProxy;


use Behamin\ServiceProxy\Exceptions\ServiceProxyException;

class UrlGenerator
{

    /**
     * @return string
     * @throws \Exception
     */
    public static function baseUrl(): string
    {
        return self::getConfigBaseUrl();
    }

    /**
     * @param $url
     * @throws \Exception
     */
    private static function checkIfUrlIsValid($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new ServiceProxyException("Url isn't set in bsproxy config file");
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    private static function getConfigBaseUrl(): string
    {
        // TODO check if url is a valid url
        $url = config('bsproxy.global_app_url', 'https://debug.behaminplus.ir/');
        if ($url == null) {
            throw new ServiceProxyException("Url isn't set in bsproxy config file");
        }
        self::checkIfUrlIsValid($url);
        return $url;
    }

    /**
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     * @throws \Exception
     */
    public static function getConfigServicePath($service)
    {
        $servicePath = config('bsproxy.service_urls.'.$service, 'behyar-service');
        if (empty($service)) {
            throw new ServiceProxyException(
                $servicePath.' service url address not found.'
            );
        }
        return $servicePath;
    }
}
