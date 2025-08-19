<?php

declare(strict_types=1);

return [

    /*
     * Filesystem disk where inter files are stored.
     */
    'inter_files_disk' => 'local',

    /*
     * Path where inter files are stored in the configured disk.
     */
    'inter_files_path' => '/geslib/inter',

    /*
     * Product type ID.
     */
    'product_type_id' => 1,

    /*
     * Currency ID
     */
    'currency_id' => 1,

    /*
     * Geslib code => Tax Class ID pairs
     */
    'product_types_taxation' => [
        'L0' => 3, // Libros => superreducido
        'P0' => 1, // Papelería => general
        'R0' => 3, // Prensa => superreducido
        'A0' => 1, // Audiovisuales => general
    ],

    /*
     * Geslib statuses codes that make a product not purchasable
     */
    'not_purchasable_statuses' => [
        2, // Descatalogado
        3, // Agotado
    ],

    /*
     * Geslib products measurements units
     * Check Lunar shipping config to find out the available valid units
     */
    'measurements' => [
        'width_unit' => 'mm',
        'height_unit' => 'mm',
        'weight_unit' => 'g',
    ],

    /*
     * Dilve API configuration
     */
    'dilve' => [
        'enabled' => env('GESLIB_DILVE_ENABLED', false),
        'username' => env('GESLIB_DILVE_USERNAME', ''),
        'password' => env('GESLIB_DILVE_PASSWORD', ''),
    ],

    /*
     * CEGAL API configuration
     */
    'cegal' => [
        'enabled' => env('GESLIB_CEGAL_ENABLED', false),
        'username' => env('GESLIB_CEGAL_USERNAME', ''),
        'password' => env('GESLIB_CEGAL_PASSWORD', ''),
    ],

    /*
     *--------------------------------------------------------------------------
     * API Routes Configuration
     *--------------------------------------------------------------------------
     *
     * Enable or disable the API routes for Geslib.
     *
     */
    'api_routes_enabled' => env('GESLIB_API_ROUTES_ENABLED', true),

    /*
     *--------------------------------------------------------------------------
     * Storefront Configuration
     *--------------------------------------------------------------------------
     *
     * Enable or disable the Geslib storefront features.
     *
     */
    'storefront_enabled' => env('GESLIB_STOREFRONT_ENABLED', true),

    /*
     *--------------------------------------------------------------------------
     * Notification Settings
     *--------------------------------------------------------------------------
     *
     * Configure email notifications for important events within the Geslib integration.
     *
     */
    'notifications' => [
        'enabled' => env('GESLIB_NOTIFICATIONS_ENABLED', true),

        'mail_to' => env('GESLIB_NOTIFICATIONS_MAIL_TO', 'admin@example.com'), // Default admin email

        // Throttle period in minutes to avoid spamming notifications for the same issue.
        'throttle_period_minutes' => env('GESLIB_NOTIFICATIONS_THROTTLE_MINUTES', 60),
    ],
];
