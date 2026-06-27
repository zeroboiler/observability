<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

use OpenTelemetry\API\Metrics\UpDownCounterInterface;

final class Gauge
{
    public function __construct(
        private UpDownCounterInterface $gauge,
        private array $attributes = [],
    ) {}

    public function increment(int $amount = 1, array $attributes = []): void
    {
        $this->gauge->add($amount, [...$this->attributes, ...$attributes]);
    }

    public function decrement(int $amount = 1, array $attributes = []): void
    {
        $this->gauge->sub($amount, [...$this->attributes, ...$attributes]);
    }

    public function set(int|float $value, array $attributes = []): void
    {
        $this->gauge->add($value, [...$this->attributes, ...$attributes]);
    }

    public function withAttributes(array $attributes): self
    {
        return new self($this->gauge, [...$this->attributes, ...$attributes]);
    }
}