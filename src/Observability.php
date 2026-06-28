<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Log;
use OpenTelemetry\API\Trace\SpanBuilderInterface;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransport;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\OtlpGrpcTransportFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;

use function OpenTelemetry\SDK\Util\shutdown;

final class Observability
{
    private TracerProviderInterface $tracerProvider;

    private OtelTracer $tracer;

    private bool $initialized = false;

    public function __construct(
        private readonly LogManager $logManager,
    ) {}

    public function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $resource = ResourceInfo::create(Attributes::create([
            'service.name' => config('zeroboiler.observability.service_name', config('app.name', 'laravel')),
            'service.version' => config('zeroboiler.observability.service_version', '1.0.0'),
            'deployment.environment' => config('app.env', 'production'),
            'telemetry.sdk.name' => 'zeroboiler-observability',
            'telemetry.sdk.version' => '1.0.0',
        ]));

        $exporter = $this->createExporter();

        $processor = config('zeroboiler.observability.exporter.batch_enabled', true)
            ? new BatchSpanProcessor($exporter)
            : new SimpleSpanProcessor($exporter);

        $this->tracerProvider = TracerProvider::builder()
            ->addSpanProcessor($processor)
            ->setResource($resource)
            ->build();

        $this->tracer = new OtelTracer($this->tracerProvider->getTracer('zeroboiler-observability'));

        register_shutdown_function(function () use ($processor): void {
            $processor->shutdown();
        });

        $this->initialized = true;
    }

    private function createExporter(): object
    {
        $exporterType = config('zeroboiler.observability.exporter.type', 'otlp');

        return match ($exporterType) {
            'otlp' => $this->createOtlpExporter(),
            'console' => new ConsoleSpanExporter(new StreamTransport(fopen('php://stdout', 'w'), 'application/x-protobuf')),
            default => throw new \InvalidArgumentException('Unsupported exporter type: ' . $exporterType),
        };
    }

    private function createOtlpExporter(): SpanExporter
    {
        $endpoint = config('zeroboiler.observability.exporter.otlp.endpoint', 'http://localhost:4318/v1/traces');
        $protocol = config('zeroboiler.observability.exporter.otlp.protocol', 'http/protobuf');
        $headers = config('zeroboiler.observability.exporter.otlp.headers', []);
        $timeout = config('zeroboiler.observability.exporter.otlp.timeout', 10);

        // Use modern transport factories instead of deprecated OtlpUtil
        if (str_starts_with((string) $protocol, 'grpc')) {
            $transport = new OtlpGrpcTransportFactory()
                ->withEndpoint($endpoint)
                ->withHeaders($headers)
                ->withTimeout($timeout)
                ->create();
        } else {
            // Default: http/protobuf or http/json
            $transport = new OtlpHttpTransportFactory()
                ->withEndpoint($endpoint)
                ->withHeaders($headers)
                ->withTimeout($timeout)
                ->withContentType(
                    $protocol === 'http/json'
                        ? 'application/json'
                        : 'application/x-protobuf'
                )
                ->create();
        }

        return new SpanExporter($transport);
    }

    public function getTracer(): OtelTracer
    {
        if (! $this->initialized) {
            $this->initialize();
        }

        return $this->tracer;
    }

    public function registerLoggingBridge(): void
    {
        if (! $this->initialized) {
            $this->initialize();
        }

        $this->logManager->listen(function ($level, $message, $context): void {
            $span = Span::current();

            if ($span->isRecording()) {
                $span->addEvent('log', [
                    'level' => $level,
                    'message' => $message,
                    ...$context,
                ]);
            }
        });
    }

    public function health(): HealthResult
    {
        $checks = [];

        $checks['otel_initialized'] = [
            'status' => $this->initialized ? 'pass' : 'fail',
            'output' => $this->initialized ? 'OpenTelemetry initialized' : 'OpenTelemetry not initialized',
        ];

        $endpoint = config('zeroboiler.observability.exporter.otlp.endpoint');
        if ($endpoint) {
            $checks['otel_endpoint'] = [
                'status' => 'pass',
                'output' => 'OTLP endpoint configured: ' . $endpoint,
            ];
        }

        return new HealthResult(
            status: collect($checks)->every(fn ($check): bool => $check['status'] === 'pass') ? 'pass' : 'degraded',
            checks: $checks,
        );
    }

    public function shutdown(): void
    {
        if ($this->initialized) {
            $this->tracerProvider->shutdown();
            $this->initialized = false;
        }
    }
}