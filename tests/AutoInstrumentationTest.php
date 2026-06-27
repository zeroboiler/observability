<?php

declare(strict_types=1);

use ZeroBoiler\Observability\AutoInstrumentation\HttpInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\DatabaseInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\QueueInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\RedisInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\MailInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\CacheInstrumentation;
use ZeroBoiler\Observability\Tests\Pest;

test('http instrumentation is enabled by default', function () {
    $instrumentation = app(HttpInstrumentation::class);

    expect($instrumentation->isEnabled())->toBeTrue();
});

test('database instrumentation is enabled by default', function () {
    $instrumentation = app(DatabaseInstrumentation::class);

    expect($instrumentation->isEnabled())->toBeTrue();
});

test('queue instrumentation is enabled by default', function () {
    $instrumentation = app(QueueInstrumentation::class);

    expect($instrumentation->isEnabled())->toBeTrue();
});

test('redis instrumentation is enabled by default', function () {
    $instrumentation = app(RedisInstrumentation::class);

    expect($instrumentation->isEnabled())->toBeTrue();
});

test('mail instrumentation is enabled by default', function () {
    $instrumentation = app(MailInstrumentation::class);

    expect($instrumentation->isEnabled())->toBeTrue();
});

test('cache instrumentation is enabled by default', function () {
    $instrumentation = app(CacheInstrumentation::class);

    expect($instrumentation->isEnabled())->toBeTrue();
});