<?php

declare(strict_types=1);

use ZeroBoiler\Observability\Counter;
use ZeroBoiler\Observability\Gauge;
use ZeroBoiler\Observability\Histogram;
use ZeroBoiler\Observability\MetricsRegistry;

test('metrics registry creates counter', function (): void {
    $registry = app(MetricsRegistry::class);

    $counter = $registry->counter('test.counter', 'Test counter');

    expect($counter)->toBeInstanceOf(Counter::class);
});

test('counter increments', function (): void {
    $registry = app(MetricsRegistry::class);

    $counter = $registry->counter('test.counter_increment');

    expect(fn () => $counter->increment())->not->toThrow(Exception::class);
});

test('counter increments with amount', function (): void {
    $registry = app(MetricsRegistry::class);

    $counter = $registry->counter('test.counter_amount');

    expect(fn () => $counter->increment(5))->not->toThrow(Exception::class);
});

test('counter with attributes', function (): void {
    $registry = app(MetricsRegistry::class);

    $counter = $registry->counter('test.counter_attributes')
        ->withAttributes(['label' => 'test']);

    expect(fn () => $counter->increment())->not->toThrow(Exception::class);
});

test('metrics registry creates gauge', function (): void {
    $registry = app(MetricsRegistry::class);

    $gauge = $registry->gauge('test.gauge', 'Test gauge');

    expect($gauge)->toBeInstanceOf(Gauge::class);
});

test('gauge increments and decrements', function (): void {
    $registry = app(MetricsRegistry::class);

    $gauge = $registry->gauge('test.gauge_inc_dec');

    expect(fn () => $gauge->increment())->not->toThrow(Exception::class);
    expect(fn () => $gauge->decrement())->not->toThrow(Exception::class);
});

test('gauge sets value', function (): void {
    $registry = app(MetricsRegistry::class);

    $gauge = $registry->gauge('test.gauge_set');

    expect(fn () => $gauge->set(42))->not->toThrow(Exception::class);
});

test('metrics registry creates histogram', function (): void {
    $registry = app(MetricsRegistry::class);

    $histogram = $registry->histogram('test.histogram', 'Test histogram');

    expect($histogram)->toBeInstanceOf(Histogram::class);
});

test('histogram records values', function (): void {
    $registry = app(MetricsRegistry::class);

    $histogram = $registry->histogram('test.histogram_record');

    expect(fn () => $histogram->record(123.45))->not->toThrow(Exception::class);
});

test('metrics registry flushes', function (): void {
    $registry = app(MetricsRegistry::class);

    expect(fn () => $registry->flush())->not->toThrow(Exception::class);
});
