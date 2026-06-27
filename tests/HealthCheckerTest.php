<?php

declare(strict_types=1);

use ZeroBoiler\Observability\HealthChecker;
use ZeroBoiler\Observability\HealthResult;
use ZeroBoiler\Observability\Tests\Pest;

test('liveness check passes', function () {
    $checker = app(HealthChecker::class);
    $result = $checker->liveness();

    expect($result)->toBeInstanceOf(HealthResult::class);
    expect($result->status)->toBe('pass');
    expect($result->isHealthy())->toBeTrue();
    expect($result->checks)->toHaveKey('app');
});

test('readiness check passes', function () {
    $checker = app(HealthChecker::class);
    $result = $checker->readiness();

    expect($result)->toBeInstanceOf(HealthResult::class);
    expect($result->checks)->toHaveKey('database');
    expect($result->checks)->toHaveKey('cache');
    expect($result->checks)->toHaveKey('queue');
});

test('startup check passes', function () {
    $checker = app(HealthChecker::class);
    $result = $checker->startup();

    expect($result)->toBeInstanceOf(HealthResult::class);
    expect($result->checks)->toHaveKey('otel_init');
});