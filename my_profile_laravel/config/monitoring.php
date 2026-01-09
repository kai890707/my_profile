<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Application Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configure application monitoring, health checks, and performance tracking
    |
    */

    'enabled' => env('MONITORING_ENABLED', true),

    'health_check' => [
        'enabled' => env('HEALTH_CHECK_ENABLED', true),
        'path' => env('HEALTH_CHECK_PATH', '/api/health'),
        'detailed_path' => env('HEALTH_DETAILED_PATH', '/api/health/detailed'),
    ],

    'metrics' => [
        'enabled' => env('METRICS_ENABLED', false),
        'path' => env('METRICS_PATH', '/metrics'),
        'collectors' => [
            'request_duration' => true,
            'memory_usage' => true,
            'database_queries' => true,
            'cache_hits' => true,
        ],
    ],

    'alerts' => [
        'enabled' => env('ALERTS_ENABLED', false),
        'channels' => explode(',', env('ALERT_CHANNELS', 'slack')),
        'thresholds' => [
            'error_rate' => env('ALERT_ERROR_RATE', 5), // errors per minute
            'response_time' => env('ALERT_RESPONSE_TIME', 1000), // milliseconds
            'memory_usage' => env('ALERT_MEMORY_USAGE', 80), // percentage
        ],
    ],

    'sentry' => [
        'enabled' => env('SENTRY_ENABLED', false),
        'dsn' => env('SENTRY_LARAVEL_DSN'),
        'environment' => env('APP_ENV', 'production'),
        'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.1),
    ],

];
