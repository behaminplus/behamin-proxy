<?php

return [
    'USER'      => "user-service/",
    'PRODUCT'   => "product-service/",
    'PAYMENT'   => "product-service/",
    'SICKNESS'  => "user-service/",
    'TICKET'    => "ticket-service/",
    'FOOD_LIST' => "food-list/",
    'HELIA'     => "helia-service/",
    'INBOX'     => "inbox-service/",
    'SMS'       => "sms-service/",

    'CONTACT_BROKER' => "contact-broker/",

    'GLOBAL_APP_URL' => env('GLOBAL_APP_URL', $_SERVER['HTTP_HOST'])
];