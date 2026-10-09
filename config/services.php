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

    // Checkout stays disabled until these manual bank-transfer details are configured.
    'sheykhan_transfer' => [
        'bank_name' => env('SHEIKHAN_TRANSFER_BANK_NAME'),
        'account_holder' => env('SHEIKHAN_TRANSFER_ACCOUNT_HOLDER'),
        'iban' => env('SHEIKHAN_TRANSFER_IBAN'),
        'account_number' => env('SHEIKHAN_TRANSFER_ACCOUNT_NUMBER'),
    ],

    'pdf_watermark' => [
        'binary' => env('PDF_WATERMARK_BINARY', 'gs'),
    ],

];
