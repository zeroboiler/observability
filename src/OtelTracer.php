<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\SpanBuilderInterface;

final class OtelTracer implements TracerInterface
{
    private \OpenTelemetry\API\Trace\TracerInterface $innerTracer;

    public function __construct(
        \OpenTelemetry\API\Trace\TracerInterface $innerTracer,
    ) {
        $this->innerTracer = $innerTracer;
    }

    #[\Override]
    public function spanBuilder(string $spanName): SpanBuilderInterface
    {
        return new SpanBuilder($this->innerTracer->spanBuilder($spanName));
    }

    public function getCurrentSpan(): Span
    {
        return new Span($this->innerTracer->getCurrentSpan());
    }

    public function traceId(): ?string
    {
        return Span::current()->getContext()->getTraceId();
    }

    public function spanId(): ?string
    {
        return Span::current()->getContext()->getSpanId();
    }
}