<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

use Closure;
use OpenTelemetry\API\Trace\Span as OtelSpan;
use OpenTelemetry\API\Trace\SpanInterface as OtelSpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;

final readonly class Span
{
    private function __construct(private OtelSpanInterface $innerSpan) {}

    public static function current(): self
    {
        return new self(OtelSpan::fromContext(Context::getCurrent()));
    }

    public static function start(string $name, ?string $kind = null, array $attributes = []): self
    {
        $observability = app(Observability::class);
        $tracer = $observability->getTracer();

        $spanKind = match ($kind) {
            'server' => SpanKind::KIND_SERVER,
            'client' => SpanKind::KIND_CLIENT,
            'producer' => SpanKind::KIND_PRODUCER,
            'consumer' => SpanKind::KIND_CONSUMER,
            'internal' => SpanKind::KIND_INTERNAL,
            default => null,
        };

        $builder = $tracer->spanBuilder($name);

        if ($spanKind !== null) {
            $builder->setSpanKind($spanKind);
        }

        foreach ($attributes as $key => $value) {
            $builder->setAttribute($key, $value);
        }

        return new self($builder->startSpan());
    }

    public static function trace(string $name, Closure $callback, ?string $kind = null, array $attributes = []): mixed
    {
        $span = self::start($name, $kind, $attributes);

        try {
            $result = $callback($span);

            $span->setStatus(StatusCode::STATUS_OK);

            return $result;
        } catch (\Throwable $throwable) {
            $span->recordException($throwable);
            $span->setStatus(StatusCode::STATUS_ERROR, $throwable->getMessage());

            throw $throwable;
        } finally {
            $span->end();
        }
    }

    public function getContext(): SpanContext
    {
        return new SpanContext($this->innerSpan->getContext());
    }

    public function setAttribute(string $key, string|int|float|bool|array|null $value): self
    {
        $this->innerSpan->setAttribute($key, $value);

        return $this;
    }

    public function setAttributes(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    public function addEvent(string $name, array $attributes = []): self
    {
        $this->innerSpan->addEvent($name, $attributes);

        return $this;
    }

    public function recordException(\Throwable $exception, array $attributes = []): self
    {
        $this->innerSpan->recordException($exception, $attributes);

        return $this;
    }

    public function setStatus(string|int $status, ?string $description = null): self
    {
        $this->innerSpan->setStatus($status, $description);

        return $this;
    }

    public function updateName(string $name): self
    {
        $this->innerSpan->updateName($name);

        return $this;
    }

    public function isRecording(): bool
    {
        return $this->innerSpan->isRecording();
    }

    public function end(?int $timestamp = null): void
    {
        $this->innerSpan->end($timestamp);
    }

    public function getTraceId(): string
    {
        return $this->innerSpan->getContext()->getTraceId();
    }

    public function getSpanId(): string
    {
        return $this->innerSpan->getContext()->getSpanId();
    }

    public static function traceId(): string
    {
        return self::current()->getTraceId();
    }

    public static function spanId(): string
    {
        return self::current()->getSpanId();
    }
}
