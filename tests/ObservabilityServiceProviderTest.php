<?php

declare(strict_types=1);

use ZeroBoiler\Observability\HealthChecker;
use ZeroBoiler\Observability\MetricsRegistry;
use ZeroBoiler\Observability\Observability;
use ZeroBoiler\Observability\ObservabilityServiceProvider;

test('service provider registers observability', function (): void {
    $app = app();

    $provider = new ObservabilityServiceProvider($app);
    $provider->register();

    expect($app->has(Observability::class))->toBeTrue();
    expect($app->bound('observability'))->toBeTrue();
});

test('service provider registers health checker', function (): void {
    $app = app();

    $provider = new ObservabilityServiceProvider($app);
    $provider->register();

    expect($app->has(HealthChecker::class))->toBeTrue();
});

test('service provider registers metrics registry', function (): void {
    $app = app();

    $provider = new ObservabilityServiceProvider($app);
    $provider->register();

    expect($app->has(MetricsRegistry::class))->toBeTrue();
});
