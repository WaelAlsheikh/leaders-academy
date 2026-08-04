<?php

return [
    'default_domain' => env('MAIL_MODULE_DOMAIN', 'leaders-academy.net'),

    /*
    | Driver: log (local/dev) | postfix_virtual (production Mail DB)
    */
    'driver' => env('MAIL_MODULE_DRIVER', 'log'),

    'webmail_url' => env('MAIL_MODULE_WEBMAIL_URL', 'https://mail.leaders-academy.net'),

    'identity_suffixes' => [
        'student' => 'student',
        'doctor' => 'doctor',
        'employee' => 'employee',
        'admin' => 'admin',
        'system' => 'system',
    ],

    'default_quotas_mb' => [
        'student' => 1024,
        'doctor' => 2048,
        'employee' => 2048,
        'admin' => 5120,
        'system' => 1024,
    ],

    'max_aliases' => [
        'student' => 2,
        'doctor' => 5,
        'employee' => 5,
        'admin' => 20,
        'system' => 50,
    ],

    'functional_aliases' => [
        'support',
        'finance',
        'hr',
        'it',
        'registrar',
        'complaints',
        'noreply',
    ],

    'password_length' => 16,

    'provision_on_create' => env('MAIL_MODULE_PROVISION_ON_CREATE', true),

    'notification_prefer_institutional' => true,
];
