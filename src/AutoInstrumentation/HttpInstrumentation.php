<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability\AutoInstrumentation;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use OpenTelemetry\API\Trace\StatusCode;
use ZeroBoiler\Observability\Span;

final class HttpInstrumentation extends BaseInstrumentation
{
    #[\Override]
    public function register(): void
    {
        // Register a middleware that wraps the entire request lifecycle
        // in a server span, so child spans (DB, cache, etc.) are captured.
        $this->registerMiddleware();

        // Keep listening to RequestHandled for response attributes enrichment
        // (status code, content length) — but the span is started in middleware.
        Event::listen(RequestHandled::class, function ($event): void {
            $span = Span::current();

            if (! $span->isRecording()) {
                return;
            }

            $response = $event->response;

            $span->setAttribute('http.status_code', $response->getStatusCode());
            $span->setAttribute('http.response_content_length', strlen((string) $response->getContent()));

            if ($response->getStatusCode() >= 500) {
                $span->setStatus(StatusCode::STATUS_ERROR, 'HTTP '.$response->getStatusCode());
            } elseif ($response->getStatusCode() >= 400) {
                $span->setStatus(StatusCode::STATUS_ERROR, 'HTTP '.$response->getStatusCode());
            } else {
                $span->setStatus(StatusCode::STATUS_OK);
            }
        });
    }

    #[\Override]
    protected function getKey(): string
    {
        return 'http';
    }

    /**
     * Register the tracing middleware as a global middleware.
     * Only register when running in an HTTP context, not console.
     */
    private function registerMiddleware(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $kernel = $this->app->make(Kernel::class);

        $kernel->prependMiddleware(HttpTracingMiddleware::class);
    }
}
