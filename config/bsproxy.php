<?php

return [

    /*
    * Services List and Their URLs
    */
    'service_urls' => [
        // 'BEHYAR'      => "behyar-service",
    ],

    /*
    * Headers added to every request
    */
    'global_headers' => [
        'Accept' => 'application/json'
    ],

    'global_app_url' => env('GLOBAL_APP_URL', env('APP_URL')),
];
