<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability\AutoInstrumentation;

use Illuminate\Support\Facades\Event;
use Illuminate\Http\Request;
use ZeroBoiler\Observability\Span;

final class HttpInstrumentation extends BaseInstrumentation
{
    #[\Override]
    protected function getKey(): string
    {
        return 'http';
    }

    #[\Override]
    public function register(): void
    {
        Event::listen(\Illuminate\Foundation\Http\Events\RequestHandled::class, function ($event) {
            $request = $event->request;
            $response = $event->response;

            Span::start('http.request', 'server', [
                'http.method' => $request->method(),
                'http.url' => $request->fullUrl(),
                'http.scheme' => $request->getScheme(),
                'http.host' => $request->getHost(),
                'http.target' => $request->getRequestUri(),
                'http.user_agent' => $request->userAgent(),
                'http.client_ip' => $request->ip(),
                'http.route' => $request->route()?->getName() ?? $request->path(),
                'http.status_code' => $response->getStatusCode(),
                'http.response_content_length' => strlen($response->getContent()),
            ])->end();
        });
    }
}