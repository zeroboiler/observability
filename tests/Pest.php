<?php

declare(strict_types=1);

use Illuminate\Config\Repository;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Support\Facades\Facade;
use ZeroBoiler\Observability\ObservabilityServiceProvider;

/*
|--------------------------------------------------------------------------
| Test bootstrap
|--------------------------------------------------------------------------
*/

$app = new Application(
    $_ENV['APP_NAME'] ?? 'ZeroBoiler Observability Test',
);

$app->instance('config', new Repository([
    'app' => [
        'env' => 'testing',
        'debug' => true,
        'key' => 'base64:dGhpcyBpcyBhIHRlc3Qga2V5IGZvciB6ZXJvYm9pbGVy',
        'cipher' => 'AES-256-CBC',
    ],
    'database' => [
        'default' => 'sqlite',
        'connections' => [
            'sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
            ],
        ],
    ],
    'logging' => [
        'default' => 'stack',
        'channels' => [
            'stack' => [
                'driver' => 'stack',
                'channels' => ['single'],
            ],
            'single' => [
                'driver' => 'single',
                'path' => '/dev/null',
            ],
        ],
    ],
    'zeroboiler' => [
        'observability' => [
            'enabled' => true,
            'service_name' => 'zeroboiler-test',
            'service_namespace' => 'zeroboiler',
            'exporter' => [
                'type' => 'console',
                'batch_enabled' => false,
            ],
            'auto_instrumentation' => [
                'enabled' => false,
            ],
            'logging' => [
                'bridge_enabled' => false,
            ],
        ],
    ],
]));

// Set base path
$app->setBasePath(__DIR__.'/..');

// Bind console kernel for Artisan facade
$app->singleton(ConsoleKernelContract::class, ConsoleKernel::class);

// Bind exception handler for console commands
$app->singleton(ExceptionHandler::class, Handler::class);

// Set facade application before boot so facades work during service provider boot
Facade::setFacadeApplication($app);

// Register the service provider
$app->register(ObservabilityServiceProvider::class);

// Boot the application manually
$app->boot();

date_default_timezone_set('UTC');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

function something(): void
{
    // ..
}
