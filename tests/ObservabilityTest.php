<?php

declare(strict_types=1);

use ZeroBoiler\Observability\Observability;
use ZeroBoiler\Observability\Tests\Pest;

test('observability initializes successfully', function () {
    $observability = app(Observability::class);

    $observability->initialize();

    expect($observability)->toBeInstanceOf(Observability::class);
});

test('observability health check passes', function () {
    $observability = app(Observability::class);
    $observability->initialize();

    $health = $observability->health();

    expect($health->isHealthy())->toBeTrue();
    expect($health->status)->toBe('pass');
});

test('observability returns tracer', function () {
    $observability = app(Observability::class);
    $observability->initialize();

    $tracer = $observability->getTracer();

    expect($tracer)->toBeInstanceOf(\ZeroBoiler\Observability\OtelTracer::class);
});