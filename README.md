# zeroboiler/observability

OpenTelemetry-based observability for Laravel applications: traces, metrics, structured logs, and health checks.

[![Latest Version](https://img.shields.io/github/v/release/zeroboiler/observability?style=flat-square)](https://github.com/zeroboiler/observability/releases)
[![License](https://img.shields.io/github/license/zeroboiler/observability?style=flat-square)](LICENSE.md)
[![CI](https://img.shields.io/github/actions/workflow/status/zeroboiler/observability/ci.yml?style=flat-square)](https://github.com/zeroboiler/observability/actions)

## Features

- **OpenTelemetry Tracing** — Distributed tracing with OTLP export
- **Auto-instrumentation** — HTTP, Database, Queue, Redis, Mail, Cache
- **Attribute-based Tracing** — `#[Trace]` attribute for manual spans
- **Structured Logging** — Automatic log → OTel bridge
- **Health Checks** — Liveness, readiness, and startup probes
- **Metrics** — Counters, gauges, and histograms
- **Backend Agnostic** — Works with Jaeger, Tempo, Honeycomb, Datadog, Grafana

## Installation

```bash
composer require zeroboiler/observability
```

Publish the config:

```bash
php artisan vendor:publish --tag=observability-config
```

## Configuration

### Basic Setup

Configure your OTLP exporter in `config/zeroboiler/observability.php` or via `.env`:

```env
OTEL_SERVICE_NAME=your-app
OTEL_SERVICE_VERSION=1.0.0
OTEL_EXPORTER_TYPE=otlp
OTEL_EXPORTER_OTLP_ENDPOINT=http://localhost:4318/v1/traces
OTEL_EXPORTER_OTLP_PROTOCOL=http/protobuf
```

### Auto-instrumentation

Enable/disable specific auto-instrumentation:

```env
OTEL_AUTO_INSTRUMENTATION_ENABLED=true
OTEL_HTTP_INSTRUMENTATION=true
OTEL_DATABASE_INSTRUMENTATION=true
OTEL_QUEUE_INSTRUMENTATION=true
OTEL_REDIS_INSTRUMENTATION=true
OTEL_MAIL_INSTRUMENTATION=true
OTEL_CACHE_INSTRUMENTATION=true
OTEL_SLOW_QUERY_THRESHOLD=1000  # milliseconds
```

### Logging Bridge

Enable automatic log → span event bridging:

```env
OTEL_LOGGING_BRIDGE=true
```

## Usage

### Manual Tracing

Use the `#[Trace]` attribute on any method:

```php
use ZeroBoiler\Observability\Trace;

class OrderService
{
    #[Trace(operation: 'order.create')]
    public function create(CreateOrderDto $dto): Order
    {
        // This automatically creates a span
        $order = Order::create($dto);

        return $order;
    }
}
```

### Span API

Create spans programmatically:

```php
use ZeroBoiler\Observability\Span;

Span::trace('order.process', function (Span $span) use ($order) {
    $span->setAttribute('order.id', $order->id);
    $span->setAttribute('order.total', $order->total);

    // Add events
    $span->addEvent('payment_started');

    PaymentService::charge($order);

    $span->addEvent('payment_completed');
});
```

Or with more control:

```php
$span = Span::start('database.query', 'client', [
    'db.system' => 'mysql',
    'db.name' => 'app',
]);

try {
    $result = DB::table('orders')->find($id);
    $span->setAttribute('db.found', $result !== null);
    return $result;
} catch (\Throwable $e) {
    $span->recordException($e);
    $span->setStatus(\OpenTelemetry\API\Trace\StatusCode::STATUS_ERROR);
    throw $e;
} finally {
    $span->end();
}
```

### Metrics

Create and track metrics:

```php
use ZeroBoiler\Observability\MetricsRegistry;

$registry = app(MetricsRegistry::class);

// Counter
$ordersCounter = $registry->counter('orders.created', 'Total orders created')
    ->withAttributes(['plan' => 'pro']);

$ordersCounter->increment();

// Gauge
$activeUsers = $registry->gauge('users.active', 'Active users');
$activeUsers->set(42);

// Histogram
$latency = $registry->histogram('http.request.duration', 'Request duration');
$latency->record(123.45, ['endpoint' => '/api/orders']);
```

### Health Checks

Use the CLI commands:

```bash
# Liveness check
php artisan zeroboiler:observability:health liveness

# Readiness check
php artisan zeroboiler:observability:health readiness

# Startup check
php artisan zeroboiler:observability:health startup
```

Or use programmatically:

```php
use ZeroBoiler\Observability\HealthChecker;

$checker = app(HealthChecker::class);

$liveness = $checker->liveness();
if (! $liveness->isHealthy()) {
    // Handle failure
}

$readiness = $checker->readiness();
$readiness->checks['database']['status']; // 'pass' or 'fail'
```

### Testing Tracing

Send test traces to verify your OTLP backend:

```bash
php artisan zeroboiler:observability:trace-test 10
```

## Auto-instrumentation

The package automatically instruments:

- **HTTP** — All incoming requests with method, URL, status code, duration
- **Database** — All queries with SQL, bindings, duration, slow query detection
- **Queue** — All job processing with queue name, job ID, attempts
- **Redis** — All Redis commands with command name and arguments
- **Mail** — All email sends with recipients, subject
- **Cache** — All cache operations (get, set, delete) with hit/miss tracking

## Integrations

### Laravel Pulse

The health checks integrate seamlessly with Laravel Pulse. Add to your Pulse config:

```php
'listen' => [
    ZeroBoiler\Observability\HealthChecker::class,
],
```

### Structured Logging

All log events are automatically attached to the current span as events:

```php
Log::info('Order placed', ['order_id' => 123]);
```

This creates a span event with `level`, `message`, and all context attributes.

## Backend Setup

### Jaeger

```bash
docker run -d --name jaeger \
  -e COLLECTOR_OTLP_ENABLED=true \
  -p 4318:4318 \
  -p 16686:16686 \
  jaegertracing/all-in-one:latest
```

### Grafana Tempo

```bash
docker run -d --name tempo \
  -v ./config/tempo.yaml:/etc/tempo.yaml \
  -p 4318:4318 \
  -p 3200:3200 \
  grafana/tempo:latest
```

## API Reference

### `#[Trace]` Attribute

```php
#[Trace(operation: 'span.name', kind: 'server', attributes: [])]
```

- `operation` — Span name (required)
- `kind` — Span kind: `server`, `client`, `producer`, `consumer`, `internal`
- `attributes` — Initial span attributes

### `Span` Facade

- `Span::current()` — Get current active span
- `Span::start($name, $kind, $attributes)` — Start new span
- `Span::trace($name, $callback, $kind, $attributes)` — Run callback in span
- `Span::traceId()` — Get current trace ID
- `Span::spanId()` — Get current span ID

### Span Methods

- `setAttribute($key, $value)` — Set attribute
- `setAttributes($attributes)` — Set multiple attributes
- `addEvent($name, $attributes)` — Add event
- `recordException($exception, $attributes)` — Record exception
- `setStatus($status, $description)` — Set status
- `end()` — End span
- `isRecording()` — Check if span is recording
- `getTraceId()` — Get trace ID
- `getSpanId()` — Get span ID

## CLI Commands

- `zeroboiler:observability:health {type}` — Run health checks (liveness|readiness|startup)
- `zeroboiler:observability:trace-test {operations}` — Send test traces

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

## Credits

- [ZeroBoiler](https://github.com/zeroboiler)
- [OpenTelemetry PHP](https://github.com/open-telemetry/opentelemetry-php)

## Related Packages

- [zeroboiler/module](https://github.com/zeroboiler/module) — Module lifecycle management (dependency)
- [zeroboiler/config](https://github.com/zeroboiler/config) — Dynamic configuration
- [zeroboiler/persistence](https://github.com/zeroboiler/persistence) — Zero-boilerplate CRUD