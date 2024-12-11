<?php


return [

    /*
    |--------------------------------------------------------------------------
    | UserAgent to communicate with sms-assistent.by
    |--------------------------------------------------------------------------
    */
    'useragent' => env('SMS_ASSISTENT_USERAGENT', 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)'),

    /*
    |--------------------------------------------------------------------------
    | Username (Login) to authenticate at sms-assistent.by
    |--------------------------------------------------------------------------
    */
    'username' => env('SMS_ASSISTENT_USERNAME', 'example'),

    /*
    |--------------------------------------------------------------------------
    | API password to authenticate at sms-assistent.by
    |--------------------------------------------------------------------------
    */
    'password' => env('SMS_ASSISTENT_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Sender name will be displayed to recipients
    |--------------------------------------------------------------------------
    */
    'sender_name' => env('SMS_ASSISTENT_SENDER_NAME', 'TEST-assist'),

];