<?php

declare(strict_types=1);

use ZeroBoiler\Observability\Observability;
use ZeroBoiler\Observability\OtelTracer;

test('observability initializes successfully', function (): void {
    $observability = app(Observability::class);

    $observability->initialize();

    expect($observability)->toBeInstanceOf(Observability::class);
});

test('observability health check passes', function (): void {
    $observability = app(Observability::class);
    $observability->initialize();

    $health = $observability->health();

    expect($health->isHealthy())->toBeTrue();
    expect($health->status)->toBe('pass');
});

test('observability returns tracer', function (): void {
    $observability = app(Observability::class);
    $observability->initialize();

    $tracer = $observability->getTracer();

    expect($tracer)->toBeInstanceOf(OtelTracer::class);
});
