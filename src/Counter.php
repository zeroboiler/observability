<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

use OpenTelemetry\API\Metrics\CounterInterface;

final readonly class Counter
{
    public function __construct(
        private CounterInterface $counter,
        private array $attributes = [],
    ) {}

    public function increment(int $amount = 1, array $attributes = []): void
    {
        $this->counter->add($amount, [...$this->attributes, ...$attributes]);
    }

    public function withAttributes(array $attributes): self
    {
        return new self($this->counter, [...$this->attributes, ...$attributes]);
    }
}
