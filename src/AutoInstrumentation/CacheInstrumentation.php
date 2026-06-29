<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability\AutoInstrumentation;

use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Support\Facades\Event;
use ZeroBoiler\Observability\Span;

final class CacheInstrumentation extends BaseInstrumentation
{
    #[\Override]
    public function register(): void
    {
        Event::listen(CacheHit::class, function (CacheHit $event): void {
            Span::start('cache.get', 'client', [
                'cache.system' => $event->storeName ?? config('cache.default'),
                'cache.key' => $event->key,
                'cache.operation' => 'get',
                'cache.hit' => true,
            ])->end();
        });

        Event::listen(CacheMissed::class, function (CacheMissed $event): void {
            Span::start('cache.get', 'client', [
                'cache.system' => $event->storeName ?? config('cache.default'),
                'cache.key' => $event->key,
                'cache.operation' => 'get',
                'cache.hit' => false,
            ])->end();
        });

        Event::listen(KeyWritten::class, function (KeyWritten $event): void {
            Span::start('cache.set', 'client', [
                'cache.system' => $event->storeName ?? config('cache.default'),
                'cache.key' => $event->key,
                'cache.operation' => 'set',
            ])->end();
        });

        Event::listen(KeyForgotten::class, function (KeyForgotten $event): void {
            Span::start('cache.delete', 'client', [
                'cache.system' => $event->storeName ?? config('cache.default'),
                'cache.key' => $event->key,
                'cache.operation' => 'delete',
            ])->end();
        });
    }

    #[\Override]
    protected function getKey(): string
    {
        return 'cache';
    }
}
