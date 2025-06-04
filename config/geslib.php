<?php

return [

    /*
     *
     */
    'inter_files_disk' => 'local',

    /*
     *
     */
    'inter_files_path' => '/geslib/inter',

    /*
     * Geslib code => Tax Class ID pairs
     */
    'product_types_taxation' => [
        'L0' => 3, // Libros => superreducido
        'P0' => 3, // Papelería => superreducido
        'R0' => 3, // Prensa => superreducido
        'A0' => 1, // Audiovisuales => general
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure email notifications for important events within the Geslib integration.
    |
    */
    'notifications' => [
        'enabled' => env('GESLIB_NOTIFICATIONS_ENABLED', true),

        'mail_to' => env('GESLIB_NOTIFICATIONS_MAIL_TO', 'admin@example.com'), // Default admin email

        // Throttle period in minutes to avoid spamming notifications for the same issue.
        'throttle_period_minutes' => env('GESLIB_NOTIFICATIONS_THROTTLE_MINUTES', 60),
    ],
];
