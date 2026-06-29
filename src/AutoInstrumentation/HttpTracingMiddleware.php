<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability\AutoInstrumentation;

use Closure;
use Illuminate\Http\Request;
use OpenTelemetry\API\Trace\StatusCode;
use ZeroBoiler\Observability\Span;

/**
 * HTTP tracing middleware — starts a server span BEFORE the request is handled,
 * so all child spans (controller, DB, cache, etc.) are captured within it.
 */
final class HttpTracingMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $span = Span::start(
            name: $this->spanName($request),
            kind: 'server',
            attributes: [
                'http.method' => $request->method(),
                'http.url' => $request->fullUrl(),
                'http.scheme' => $request->getScheme(),
                'http.host' => $request->getHost(),
                'http.target' => $request->getRequestUri(),
                'http.user_agent' => $request->userAgent() ?? '',
                'http.client_ip' => $request->ip() ?? '',
            ],
        );

        // Attach route info once available (after routing)
        $response = $next($request);

        // Enrich with route name if available
        $route = $request->route();

        if ($route !== null) {
            $span->setAttribute('http.route', $route->getName() ?? $request->path());
        }

        $span->setAttribute('http.status_code', $response->getStatusCode());

        if ($response->getStatusCode() >= 500) {
            $span->setStatus(StatusCode::STATUS_ERROR, 'HTTP '.$response->getStatusCode());
        } elseif ($response->getStatusCode() >= 400) {
            $span->setStatus(StatusCode::STATUS_ERROR, 'HTTP '.$response->getStatusCode());
        } else {
            $span->setStatus(StatusCode::STATUS_OK);
        }

        $span->end();

        return $response;
    }

    /**
     * Build a descriptive span name from the request.
     */
    private function spanName(Request $request): string
    {
        $route = $request->route();

        if ($route !== null && $route->getName() !== null) {
            return 'http.server '.$route->getName();
        }

        return 'http.server '.$request->method().' '.$request->path();
    }
}
