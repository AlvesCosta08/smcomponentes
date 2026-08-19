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

    /*
    |--------------------------------------------------------------------------
    | Mercado Pago
    |--------------------------------------------------------------------------
    |
    | Configuração da integração com o Mercado Pago para pagamentos
    |
    */

    'mercadopago' => [
        // 🔥 Chaves de API
        'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
        
        // 🔥 URLs
        'webhook_url' => env('MERCADOPAGO_WEBHOOK_URL'),
        
        // 🔥 Ambiente: 'production' ou 'sandbox'
        'env' => env('MERCADOPAGO_ENV', 'sandbox'),
        
        // 🔥 Modo de teste (true = mock, false = real)
        'mock' => env('MERCADOPAGO_MOCK', true),
        
        // 🔥 Configurações adicionais
        'client_id' => env('MERCADOPAGO_CLIENT_ID'),
        'client_secret' => env('MERCADOPAGO_CLIENT_SECRET'),
        
        // 🔥 Métodos de pagamento habilitados
        'payment_methods' => [
            'pix' => [
                'enabled' => true,
                'label' => 'PIX',
                'icon' => 'fa-qrcode',
                'description' => 'Pagamento instantâneo via PIX',
            ],
            'boleto' => [
                'enabled' => true,
                'label' => 'Boleto Bancário',
                'icon' => 'fa-barcode',
                'description' => 'Pagamento em até 3 dias úteis',
            ],
            'cartao' => [
                'enabled' => true,
                'label' => 'Cartão de Crédito',
                'icon' => 'fa-credit-card',
                'description' => 'Parcelado em até 12x',
            ],
        ],
    ],

];