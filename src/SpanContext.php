<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

use OpenTelemetry\API\Trace\SpanContextInterface;

readonly class SpanContext
{
    public string $traceId;
    public string $spanId;
    public bool $isValid;
    public bool $isRemote;

    public function __construct(SpanContextInterface $innerContext)
    {
        $this->traceId = $innerContext->getTraceId();
        $this->spanId = $innerContext->getSpanId();
        $this->isValid = $innerContext->isValid();
        $this->isRemote = $innerContext->isRemote();
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }
}