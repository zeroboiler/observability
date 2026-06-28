<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability\AutoInstrumentation;

use Illuminate\Support\Facades\Queue;
use ZeroBoiler\Observability\Span;

final class QueueInstrumentation extends BaseInstrumentation
{
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

            // Key by job ID to support concurrent processing
            app()->instance("observability.queue_span.{$jobId}", $span);
        });

        Queue::after(function ($event) {
            $jobId = $event->job->getJobId();
            $span = app("observability.queue_span.{$jobId}", null);

            if ($span instanceof Span) {
                $span->setAttribute('messaging.attempts', $event->job->attempts());
                $span->end();
                app()->forgetInstance("observability.queue_span.{$jobId}");
            }
        });

        Queue::failing(function ($event) {
            $jobId = $event->job->getJobId();
            $span = app("observability.queue_span.{$jobId}", null);

            if ($span instanceof Span) {
                $span->recordException($event->exception);
                $span->setAttribute('messaging.attempts', $event->job->attempts());
                $span->end();
                app()->forgetInstance("observability.queue_span.{$jobId}");
            }
        });
    }
}
