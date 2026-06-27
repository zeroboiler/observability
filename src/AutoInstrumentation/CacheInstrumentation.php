<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability\AutoInstrumentation;

use Illuminate\Support\Facades\Cache;
use ZeroBoiler\Observability\Span;

final class CacheInstrumentation extends BaseInstrumentation
{
    #[\Override]
    protected function getKey(): string
    {
        return 'cache';
    }

    #[\Override]
    public function register(): void
    {
        Cache::beforeCommitting(function ($key, $value) {
            Span::start('cache.set', 'client', [
                'cache.system' => config('cache.default'),
                'cache.key' => $key,
                'cache.operation' => 'set',
                'cache.hit' => null,
            ])->end();
        });

        Cache::hit(function ($key) {
            Span::start('cache.get', 'client', [
                'cache.system' => config('cache.default'),
                'cache.key' => $key,
                'cache.operation' => 'get',
                'cache.hit' => true,
            ])->end();
        });

        Cache::missed(function ($key) {
            Span::start('cache.get', 'client', [
                'cache.system' => config('cache.default'),
                'cache.key' => $key,
                'cache.operation' => 'get',
                'cache.hit' => false,
            ])->end();
        });

        Cache::forget(function ($key) {
            Span::start('cache.delete', 'client', [
                'cache.system' => config('cache.default'),
                'cache.key' => $key,
                'cache.operation' => 'delete',
            ])->end();
        });
    }
}