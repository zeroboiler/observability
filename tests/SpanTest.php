<?php

declare(strict_types=1);

use ZeroBoiler\Observability\Span;
use ZeroBoiler\Observability\Tests\Pest;

test('span creates and ends', function () {
    $span = Span::start('test.span');

    expect($span->isRecording())->toBeTrue();
    expect($span->getContext()->isValid())->toBeTrue();

    $span->end();

    expect($span->isRecording())->toBeFalse();
});

test('span adds attributes', function () {
    $span = Span::start('test.attributes')
        ->setAttribute('key1', 'value1')
        ->setAttribute('key2', 123)
        ->setAttribute('key3', ['nested' => 'value']);

    expect($span)->toBeInstanceOf(Span::class);

    $span->end();
});

test('span adds events', function () {
    $span = Span::start('test.events')
        ->addEvent('event1', ['data' => 'value'])
        ->addEvent('event2');

    expect($span)->toBeInstanceOf(Span::class);

    $span->end();
});

test('span records exception', function () {
    $span = Span::start('test.exception');

    $exception = new \RuntimeException('Test exception');

    $span->recordException($exception);

    expect($span)->toBeInstanceOf(Span::class);

    $span->end();
});

test('span trace method catches exceptions', function () {
    $result = Span::trace('test.trace', function (Span $span) {
        $span->setAttribute('test', 'value');

        throw new \RuntimeException('Test exception');
    });

    expect($result)->toBeNull();
})->throws(\RuntimeException::class, 'Test exception');

test('span trace method returns result', function () {
    $result = Span::trace('test.trace', function (Span $span) {
        $span->setAttribute('test', 'value');

        return 'success';
    });

    expect($result)->toBe('success');
});

test('span returns trace id', function () {
    $span = Span::start('test.trace_id');

    $traceId = $span->getTraceId();

    expect($traceId)->toBeString();
    expect($traceId)->toHaveLength(32);

    $span->end();
});

test('span returns span id', function () {
    $span = Span::start('test.span_id');

    $spanId = $span->getSpanId();

    expect($spanId)->toBeString();
    expect($spanId)->toHaveLength(16);

    $span->end();
});