<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'tmdb' => [
        'token' => env('eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiIzYTE1NGE4N2U3NzIyMWUzNGI3ZDJiMTAzYjZhODkwMiIsIm5iZiI6MTczMzczNDU0Mi43MDU5OTk5LCJzdWIiOiI2NzU2YjA4ZTA5ODJiNDYyNjc4YTI2YTkiLCJzY29wZXMiOlsiYXBpX3JlYWQiXSwidmVyc2lvbiI6MX0.0TQ06SHm1Qfxee8B27EidGbOj-b72UKMaOIX90SiJLc'),
        'key' => env('3a154a87e77221e34b7d2b103b6a8902'),
        'base_url' => 'https://api.themoviedb.org/3',
    ],
];
