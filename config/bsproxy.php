<?php

return [

    /**
     * Headers added to every request
     */
    'global_headers' => [
        'Accept' => 'application/json'
    ],

    'proxy_base_url' => env('PROXY_BASE__URL', env('APP_URL')),
];
