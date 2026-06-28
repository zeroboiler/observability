<?php

declare(strict_types=1);

use ZeroBoiler\Observability\AutoInstrumentation\HttpInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\DatabaseInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\QueueInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\RedisInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\MailInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\CacheInstrumentation;
use ZeroBoiler\Observability\Tests\Pest;

beforeEach(function (): void {
    // Set the config that the instrumentations check via isEnabled()
    config()->set('zeroboiler.observability.auto_instrumentation.enabled', true);
    config()->set('zeroboiler.observability.auto_instrumentation.http.enabled', true);
    config()->set('zeroboiler.observability.auto_instrumentation.database.enabled', true);
    config()->set('zeroboiler.observability.auto_instrumentation.queue.enabled', true);
    config()->set('zeroboiler.observability.auto_instrumentation.redis.enabled', true);
    config()->set('zeroboiler.observability.auto_instrumentation.mail.enabled', true);
    config()->set('zeroboiler.observability.auto_instrumentation.cache.enabled', true);
});

afterEach(function (): void {
    // Reset to default test config
    config()->set('zeroboiler.observability.auto_instrumentation.enabled', false);
});

test('http instrumentation is enabled by default', function (): void {
    $instrumentation = app(HttpInstrumentation::class);

    expect($instrumentation->isEnabled())->toBeTrue();
});

test('database instrumentation is enabled by default', function (): void {
    $instrumentation = app(DatabaseInstrumentation::class);

    expect($instrumentation->isEnabled())->toBeTrue();
});

test('queue instrumentation is enabled by default', function (): void {
    $instrumentation = app(QueueInstrumentation::class);

    expect($instrumentation->isEnabled())->toBeTrue();
});

test('redis instrumentation is enabled by default', function (): void {
    $instrumentation = app(RedisInstrumentation::class);

    expect($instrumentation->isEnabled())->toBeTrue();
});

test('mail instrumentation is enabled by default', function (): void {
    $instrumentation = app(MailInstrumentation::class);

    expect($instrumentation->isEnabled())->toBeTrue();
});

test('cache instrumentation is enabled by default', function (): void {
    $instrumentation = app(CacheInstrumentation::class);

    expect($instrumentation->isEnabled())->toBeTrue();
});

test('instrumentation respects global disable', function (): void {
    config()->set('zeroboiler.observability.auto_instrumentation.enabled', false);

    $instrumentation = app(HttpInstrumentation::class);

    expect($instrumentation->isEnabled())->toBeFalse();
});

test('individual instrumentation can be disabled', function (): void {
    config()->set('zeroboiler.observability.auto_instrumentation.http.enabled', false);

    $instrumentation = app(HttpInstrumentation::class);

    expect($instrumentation->isEnabled())->toBeFalse();
});
