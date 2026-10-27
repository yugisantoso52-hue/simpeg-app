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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'gas_drive' => [
        'web_app_url'    => env('GAS_DRIVE_WEBAPP_URL'),
        'api_key'        => env('GAS_DRIVE_API_KEY'),
        'root_folder_id' => env('GAS_DRIVE_ROOT_FOLDER_ID'),
    ],

    'sync' => [
        'outbox_enabled' => env('SYNC_OUTBOX_ENABLED', false),
        'secret_key'     => env('SYNC_SECRET_KEY'),
        'token'          => env('SYNC_TOKEN'),
        'online_url'     => env('ONLINE_SYNC_URL'),
    ],

    'deploy' => [
        'webhook_secret' => env('DEPLOY_WEBHOOK_KEY'),
    ],

    'backup' => [
        'mysqldump_path' => env('MYSQLDUMP_PATH'),
    ],

];
