<?php

/**
 * Squirrel-Forge Laravel Core Support Configuration.
 */
return [

    /**
     * Dynamic debug mode.
     * This allows debug mode to be enabled for specific ips,
     * via keypass GET variable or under other custom conditions.
     */
    'debug' => [

        /**
         * Register middleware.
         * Set to false to disable the middleware.
         */
        'enabled' => true,

        /**
         * Which activators to use.
         * Default are ip and range, if needed a keypass GET variable based activator can be added.
         * Custom activators can be defined as functions and must be referenced with full namespacing,
         * or you may define the use array here in the config using a Closure value,
         * that will get called with two arguments:
         * function($request, $middleware):void { $middleware->activate('origin-name'); }
         * You may also set this value via your service provider boot method, using:
         * use Illuminate\Support\Facades\Config;
         * Config::set('sqf-cs.debug.use', array_merge(Config::get('sqf-cs.debug.use'), [
         *   function($request, $middleware):void { $middleware->activate('origin-name'); }
         * ]));
         */
        'use' => preg_split('/[,;]+/', env('SQF_CS_USE', 'ip,range'), -1, PREG_SPLIT_NO_EMPTY),

        /**
         * List of environment variables to fetch client ip from.
         * Usually required when using a proxy, depending on the setup you
         * may define multiple names to check in the given order,
         * for example 'X-CLIENT-IP,REMOTE_ADDR'
         */
        'env' => preg_split('/[,;]+/', env('SQF_CS_ENV', ''), -1, PREG_SPLIT_NO_EMPTY),

        /**
         * Comma or semicolon separated list of ips (v4 + v6)
         * that get debug mode enabled when accessing the application.
         */
        'ips' => preg_split('/[,;]+/', env('SQF_CS_IPS', ''), -1, PREG_SPLIT_NO_EMPTY),

        /**
         * Comma or semicolon separated list of ip ranges (v4 + v6)
         * that get debug mode enabled when accessing the application.
         */
        'ranges' => preg_split('/[,;]+/', env('SQF_CS_RANGES', ''), -1, PREG_SPLIT_NO_EMPTY),

        /**
         * Keypass options.
         * With the default lifetime limits, debug access will last
         * until the next full hour, at which time access must be refreshed.
         * key = Name of the get variable.
         * pass = Value of the get variable.
         * lifetime = Comparison date format to match.
         * limit = Cookie lifetime.
         */
        'key' => env('SQF_CS_KEY'),
        'pass' => env('SQF_CS_PASS'),
        'lifetime' => env('SQF_CS_LIFETIME', 'Y-m-d-H'),
        'limit' => env('SQF_CS_LIMIT', 60),

        /**
         * When enabled every activation is logged as an info message.
         */
        'log' => env('SQF_CS_LOG', false),
    ],

    /**
     * Global response headers.
     * These security headers are set for every response delivered by laravel.
     * Values can be set as closures and receive two arguments: request and response objects;
     * and may return a value, or perform all actions and return null or void.
     * 'header-name' => function ($request, $response) { return 'value'; }
     * 'header-name' => function ($request, $response) { $response->header('name', 'value'); }
     * To disable the middleware set the value to false, null or an empty array.
     */
    'headers' => [
        'X-Frame-Options' => 'deny',
        'X-XSS-Protection' => '1; mode=block',
        'X-Content-Type-Options' => 'nosniff',
        'Content-Security-Policy' => env('SQF_CS_CSP'),
        'X-Permitted-Cross-Domain-Policies' => 'none',
    ],
];
