<?php

declare(strict_types=1);

return [
    'service_name' => env('OTEL_SERVICE_NAME', config('app.name', 'laravel')),
    'service_version' => env('OTEL_SERVICE_VERSION', '1.0.0'),

    'auto_instrumentation' => [
        'enabled' => env('OTEL_AUTO_INSTRUMENTATION_ENABLED', true),

        'http' => [
            'enabled' => env('OTEL_HTTP_INSTRUMENTATION', true),
        ],

        'database' => [
            'enabled' => env('OTEL_DATABASE_INSTRUMENTATION', true),
            'slow_query_threshold' => env('OTEL_SLOW_QUERY_THRESHOLD', 1000),
        ],

        'queue' => [
            'enabled' => env('OTEL_QUEUE_INSTRUMENTATION', true),
        ],

        'redis' => [
            'enabled' => env('OTEL_REDIS_INSTRUMENTATION', true),
        ],

        'mail' => [
            'enabled' => env('OTEL_MAIL_INSTRUMENTATION', true),
        ],

        'cache' => [
            'enabled' => env('OTEL_CACHE_INSTRUMENTATION', true),
        ],
    ],

    'exporter' => [
        'type' => env('OTEL_EXPORTER_TYPE', 'otlp'),

        'batch_enabled' => env('OTEL_BATCH_ENABLED', true),

        'otlp' => [
            'endpoint' => env('OTEL_EXPORTER_OTLP_ENDPOINT', 'http://localhost:4318/v1/traces'),
            'protocol' => env('OTEL_EXPORTER_OTLP_PROTOCOL', 'http/protobuf'),
            'headers' => array_filter(array_map('trim', explode(',', env('OTEL_EXPORTER_OTLP_HEADERS', '')))),
            'timeout' => env('OTEL_EXPORTER_OTLP_TIMEOUT', 10),
        ],
    ],

    'logging' => [
        'bridge_enabled' => env('OTEL_LOGGING_BRIDGE', true),
    ],

    'health' => [
        'liveness_path' => env('OTEL_HEALTH_LIVENESS_PATH', '/health/live'),
        'readiness_path' => env('OTEL_HEALTH_READINESS_PATH', '/health/ready'),
        'startup_path' => env('OTEL_HEALTH_STARTUP_PATH', '/health/startup'),
    ],
];
