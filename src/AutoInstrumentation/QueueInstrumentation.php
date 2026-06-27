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
            $span = Span::start('queue.process', 'consumer', [
                'messaging.system' => config('queue.default', 'redis'),
                'messaging.destination' => $event->job->getQueue(),
                'messaging.message_id' => $event->job->getJobId(),
                'messaging.operation' => 'process',
            ]);

            app()->instance('observability.current_queue_span', $span);
        });

        Queue::after(function ($event) {
            $span = app('observability.current_queue_span');

            if ($span) {
                $span->setAttribute('messaging.attempts', $event->job->attempts());
                $span->end();
            }
        });

        Queue::failing(function ($event) {
            $span = app('observability.current_queue_span');

            if ($span) {
                $span->recordException($event->exception);
                $span->setAttribute('messaging.attempts', $event->job->attempts());
                $span->end();
            }
        });
    }
}