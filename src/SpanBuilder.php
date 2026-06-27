<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

use OpenTelemetry\API\Trace\SpanBuilderInterface as OtelSpanBuilderInterface;

final class SpanBuilder
{
    private OtelSpanBuilderInterface $innerBuilder;

    public function __construct(OtelSpanBuilderInterface $builder)
    {
        $this->innerBuilder = $builder;
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

    public function addLink(ContextInterface $context, array $attributes = []): self
    {
        $this->innerBuilder->addLink($context, $attributes);

        return $this;
    }

    public function setAttribute(string $key, string|int|float|bool|array|null $value): self
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

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
        return new Span($this->innerBuilder->startSpan());
    }
}