<?php

declare(strict_types=1);

use ZeroBoiler\Observability\ObservabilityServiceProvider;
use ZeroBoiler\Observability\Tests\Pest;

test('service provider registers observability', function () {
    $app = app();

    $provider = new ObservabilityServiceProvider($app);
    $provider->register();

    expect($app->has(\ZeroBoiler\Observability\Observability::class))->toBeTrue();
    expect($app->bound('observability'))->toBeTrue();
});

test('service provider registers health checker', function () {
    $app = app();

    $provider = new ObservabilityServiceProvider($app);
    $provider->register();

    expect($app->has(\ZeroBoiler\Observability\HealthChecker::class))->toBeTrue();
});

test('service provider registers metrics registry', function () {
    $app = app();

    $provider = new ObservabilityServiceProvider($app);
    $provider->register();

    expect($app->has(\ZeroBoiler\Observability\MetricsRegistry::class))->toBeTrue();
});