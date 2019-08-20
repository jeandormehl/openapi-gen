<?php

return [
    /**
     * Route setup.
     *
     * Please note that if using Dingo, your API_PREFIX is appened to the path.
     * The default path is `docs` but can be changed to anything you desire.
     *
     * Middleware should be an array of FCQN's.
     */
    'route' => [
        'enabled'    => true,
        'prefix'     => '',
        'path'       => 'docs',
        'middleware' => [],
    ],

    /**
     * Yaml generator output file path.
     */
    'yaml' => storage_path('app') . DIRECTORY_SEPARATOR . 'oas.yml',

    /**
     * https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#oasObject.
     */
    'openapi' => \Rapid\OAS\OpenApi::VERSION,

    /**
     * https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#infoObject.
     */
    'info' => [
        'title'   => 'OpenApi',
        'version' => env('API_VERSION') ?? env('APP_VERSION', 'v1'),
    ],

    /**
     * https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#pathsObject.
     */
    'paths' => [],
];
