<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability\AutoInstrumentation;

use Illuminate\Support\Facades\Queue;
use ZeroBoiler\Observability\Span;

final class QueueInstrumentation extends BaseInstrumentation
{
    /** @var array<string, Span> Active spans keyed by job ID */
    private array $activeSpans = [];

    #[\Override]
    protected function getKey(): string
    {
        return 'queue';
    }

    #[\Override]
    public function register(): void
    {
        Queue::before(function ($event) {
            $jobId = $event->job->getJobId();
            $span = Span::start('queue.process', 'consumer', [
                'messaging.system' => config('queue.default', 'redis'),
                'messaging.destination' => $event->job->getQueue(),
                'messaging.message_id' => $jobId,
                'messaging.operation' => 'process',
            ]);

            $this->activeSpans[$jobId] = $span;
            app()->instance("observability.queue_span.{$jobId}", $span);
        });

        Queue::after(function ($event) {
            $this->cleanupSpan($event->job->getJobId(), [
                'messaging.attempts' => $event->job->attempts(),
            ]);
        });

        Queue::failing(function ($event) {
            $this->cleanupSpan($event->job->getJobId(), [
                'messaging.attempts' => $event->job->attempts(),
            ], $event->exception);
        });

        // Safety net: clean up any leaked spans on application termination
        // when a job throws an unhandled exception that bypasses after/failing.
        app()->terminating(function () {
            foreach ($this->activeSpans as $jobId => $span) {
                if ($span->isRecording()) {
                    $span->end();
                }
                unset($this->activeSpans[$jobId]);
                app()->forgetInstance("observability.queue_span.{$jobId}");
            }
        });
    }

    /**
     * Clean up a span by job ID, optionally recording an exception.
     *
     * @param array<string, mixed> $attributes
     */
    private function cleanupSpan(string $jobId, array $attributes = [], ?\Throwable $exception = null): void
    {
        $span = $this->activeSpans[$jobId] ?? null;

        if ($span instanceof Span && $span->isRecording()) {
            foreach ($attributes as $key => $value) {
                $span->setAttribute($key, $value);
            }

            if ($exception !== null) {
                $span->recordException($exception);
            }

            $span->end();
        }

        unset($this->activeSpans[$jobId]);
        app()->forgetInstance("observability.queue_span.{$jobId}");
    }
}
