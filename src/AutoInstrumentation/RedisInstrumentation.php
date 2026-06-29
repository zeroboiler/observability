<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability\AutoInstrumentation;

use Illuminate\Support\Facades\Redis;
use ZeroBoiler\Observability\Span;

final class RedisInstrumentation extends BaseInstrumentation
{
    #[\Override]
    public function register(): void
    {
        Redis::enableEvents();

        Redis::listen(function ($event): void {
            Span::start('redis.command', 'client', [
                'db.system' => 'redis',
                'db.name' => $event->connection?->getName() ?? 'default',
                'db.statement' => $event->command,
                'db.redis.arguments' => $event->parameters,
                'db.redis.connection' => $event->connection?->getName(),
            ])->end();
        });
    }

    #[\Override]
    protected function getKey(): string
    {
        return 'redis';
    }
}
