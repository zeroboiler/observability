<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

use OpenTelemetry\API\Trace\SpanBuilderInterface as OtelSpanBuilderInterface;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\Context\ContextInterface;

final class SpanBuilder
{
    private ?int $spanKind = null;

    public function __construct(private readonly OtelSpanBuilderInterface $innerBuilder) {}

    /**
     * Set the span kind (SERVER, CLIENT, INTERNAL, CONSUMER, PRODUCER).
     *
     * Uses OpenTelemetry span kind constants from SpanKind.
     */
    public function setSpanKind(int $kind): self
    {
        $this->spanKind = $kind;

        return $this;
    }

    public function setParent(ContextInterface $context): self
    {
        $this->innerBuilder->setParent($context);

        return $this;
    }

    public function setNoParent(): self
    {
        $this->innerBuilder->setNoParent();

        return $this;
    }

    public function addLink(SpanContextInterface $spanContext, array $attributes = []): self
    {
        $this->innerBuilder->addLink($spanContext, $attributes);

        return $this;
    }

    public function setAttribute(string $key, string|int|float|bool|array|null $value): self
    {
        $this->innerBuilder->setAttribute($key, $value);

        return $this;
    }

    public function setAttributes(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    public function setStartTimestamp(int $timestamp): self
    {
        $this->innerBuilder->setStartTimestamp($timestamp);

        return $this;
    }

    public function startSpan(): Span
    {
        if ($this->spanKind !== null) {
            $this->innerBuilder->setSpanKind($this->spanKind);
        }

        return new Span($this->innerBuilder->startSpan());
    }
}
