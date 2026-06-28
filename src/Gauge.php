<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

use OpenTelemetry\API\Metrics\UpDownCounterInterface;

final class Gauge
{
    /** @var array<string, int|float> Tracks the current value per attribute signature */
    private array $currentValues = [];

    public function __construct(
        private UpDownCounterInterface $gauge,
        private array $attributes = [],
    ) {}

    public function increment(int $amount = 1, array $attributes = []): void
    {
        $key = $this->attributeKey($attributes);
        $this->currentValues[$key] = ($this->currentValues[$key] ?? 0) + $amount;
        $this->gauge->add($amount, [...$this->attributes, ...$attributes]);
    }

    public function decrement(int $amount = 1, array $attributes = []): void
    {
        $key = $this->attributeKey($attributes);
        $this->currentValues[$key] = ($this->currentValues[$key] ?? 0) - $amount;
        $this->gauge->sub($amount, [...$this->attributes, ...$attributes]);
    }

    public function set(int|float $value, array $attributes = []): void
    {
        $key = $this->attributeKey($attributes);
        $current = $this->currentValues[$key] ?? 0;
        $delta = $value - $current;
        $this->currentValues[$key] = $value;
        $this->gauge->add($delta, [...$this->attributes, ...$attributes]);
    }

    public function withAttributes(array $attributes): self
    {
        return new self($this->gauge, [...$this->attributes, ...$attributes]);
    }

    /**
     * Generate a cache key from the merged attribute set.
     *
     * @param array<string, mixed> $additionalAttributes
     */
    private function attributeKey(array $additionalAttributes): string
    {
        $merged = [...$this->attributes, ...$additionalAttributes];

        ksort($merged);

        return json_encode($merged) ?: 'default';
    }
}