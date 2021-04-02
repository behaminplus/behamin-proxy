<?php

return [

    /*
    * Services List and Their URLs
    */
    'service_urls' => [
        // 'USER'      => "user-service",
        // 'PRODUCT'   => "product-service",
        // 'TICKET'    => "ticket-service",
        // 'SMS'       => "sms-service"
    ],

    /*
    * Headers added to every request
    */
    'global_headers' => [
        'Accept' => 'application/json'
    ],

    'global_app_url' => env('GLOBAL_APP_URL', env('APP_URL', null)),
];
