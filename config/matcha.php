<?php

return [
    // Where "new contact message" emails are sent.
    'notify_email' => env('CONTACT_NOTIFY_EMAIL'),

    // Used in the email's "Open admin" button.
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),

    // The first admin account, created by `php artisan db:seed`.
    'admin' => [
        'name' => env('ADMIN_NAME', 'Admin'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],
];
