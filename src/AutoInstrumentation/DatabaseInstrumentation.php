<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability\AutoInstrumentation;

use Illuminate\Support\Facades\DB;
use OpenTelemetry\API\Trace\Span as OtelSpan;
use OpenTelemetry\Context\Context;
use ZeroBoiler\Observability\Span;

final class DatabaseInstrumentation extends BaseInstrumentation
{
    /**
     * Note: Laravel's DB::listen fires AFTER query execution completes.
     * This means spans are created retrospectively with the known duration,
     * not wrapped around the actual query execution. This is a limitation
     of Laravel's query event system. The duration is still accurately
     captured from $query->time.
     */
    private ?float $slowQueryThreshold = null;

    #[\Override]
    public function register(): void
    {
        $this->slowQueryThreshold = (float) config('zeroboiler.observability.auto_instrumentation.database.slow_query_threshold', 1000.0);

        DB::listen(function ($query): void {
            // Use Laravel's built-in query duration (in milliseconds)
            $durationMs = (float) ($query->time ?? 0.0);

            $span = Span::start('db.query', 'client', [
                'db.system' => config('database.connections.'.$query->connectionName.'.driver', 'unknown'),
                'db.name' => config('database.connections.'.$query->connectionName.'.database'),
                'db.statement' => $query->sql,
                'db.connection' => $query->connectionName,
                'db.query.duration_ms' => round($durationMs, 2),
            ]);

            // Activate the span so child spans have the correct parent context
            $scope = Context::storage()->attach(OtelSpan::getCurrent()->storeInContext(Context::getCurrent()));

            try {
                if (isset($query->affected)) {
                    $span->setAttribute('db.rows_affected', $query->affected);
                }

                if ($durationMs > $this->slowQueryThreshold) {
                    $span->addEvent('slow_query', [
                        'threshold_ms' => $this->slowQueryThreshold,
                        'actual_ms' => round($durationMs, 2),
                    ]);
                }

                if (! empty($query->bindings)) {
                    $span->setAttribute('db.bindings', $query->bindings);
                }
            } finally {
                $scope->detach();
            }

            $span->end();
        });
    }

    #[\Override]
    protected function getKey(): string
    {
        return 'database';
    }
}
