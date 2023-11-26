<?php

return [

    /**
     * Headers added to every request
     */
    'global_headers' => [
        'Accept' => 'application/json'
    ],

    'base_url' => env('PROXY_BASE_URL', env('APP_URL')),

    'is_local_url' => env('PROXY_IS_LOCAL_URL', true),

];
