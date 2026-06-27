<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability\AutoInstrumentation;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use ZeroBoiler\Observability\Span;

final class DatabaseInstrumentation extends BaseInstrumentation
{
    private float $slowQueryThreshold;

    public function __construct()
    {
        $this->slowQueryThreshold = config('zeroboiler.observability.auto_instrumentation.database.slow_query_threshold', 1000.0);
    }

    #[\Override]
    protected function getKey(): string
    {
        return 'database';
    }

    #[\Override]
    public function register(): void
    {
        DB::listen(function ($query) {
            $startTime = microtime(true);

            DB::afterQuery(function () use ($query, $startTime) {
                $durationMs = (microtime(true) - $startTime) * 1000;

                $span = Span::start('db.query', 'client', [
                    'db.system' => config('database.connections.' . $query->connectionName . '.driver', 'unknown'),
                    'db.name' => config('database.connections.' . $query->connectionName . '.database'),
                    'db.statement' => $query->sql,
                    'db.connection' => $query->connectionName,
                    'db.query.duration_ms' => round($durationMs, 2),
                    'db.rows_affected' => $query->affected ?? null,
                ]);

                if ($durationMs > $this->slowQueryThreshold) {
                    $span->addEvent('slow_query', [
                        'threshold_ms' => $this->slowQueryThreshold,
                        'actual_ms' => round($durationMs, 2),
                    ]);
                }

                if (! empty($query->bindings)) {
                    $span->setAttribute('db.bindings', $query->bindings);
                }

                $span->end();
            });
        });
    }
}