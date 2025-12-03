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
     * Geslib product type ID in Lunar product types.
     * If you executed the lunar:geslib:install command on a fresh database, the geslib product type ID should be 1.
     */
    'product_type_id' => 1,

    /*
     * Geslib products price currency ID.
     * If you executed the lunar:geslib:install command on a fresh database, the EUR currency was created and has the ID 1.
     */
    'currency_id' => 1,

    /*
     * Geslib products taxation configuration.
     * Geslib code => Tax Class ID pairs.
     * If you executed the lunar:geslib:install command on a fresh database, the tax classes were created with the
     * expected IDs specified in the config.
     */
    'product_types_taxation' => [
        'L0' => 3, // Libros => superreducido
        'P0' => 1, // Papelería => general
        'R0' => 3, // Prensa => superreducido
        'A0' => 1, // Audiovisuales => general
    ],

    /*
     * Geslib statuses codes that make a product not purchasable.
     * This are the codes of the Geslib system, not the Lunar statuses IDs.
     */
    'not_purchasable_statuses' => [
        2, // Descatalogado
        3, // Agotado
    ],

    /*
     * Geslib products measurements units. You can change them to match your Geslib data measurements.
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
];
