<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\SpanBuilderInterface;
use OpenTelemetry\API\Trace\Span as OtelSpan;
use OpenTelemetry\Context\Context;

final class OtelTracer implements TracerInterface
{
    private \OpenTelemetry\API\Trace\TracerInterface $innerTracer;

    public function __construct(
        \OpenTelemetry\API\Trace\TracerInterface $innerTracer,
    ) {
        $this->innerTracer = $innerTracer;
    }

    public function spanBuilder(string $spanName): SpanBuilderInterface
    {
        return $this->innerTracer->spanBuilder($spanName);
    }

    public function getCurrentSpan(): Span
    {
        return new Span(OtelSpan::fromContext(Context::getCurrent()));
    }

    public function traceId(): ?string
    {
        return Span::current()->getContext()->getTraceId();
    }

    public function spanId(): ?string
    {
        return Span::current()->getContext()->getSpanId();
    }

    public function isEnabled(): bool
    {
        return $this->innerTracer->isEnabled();
    }
}