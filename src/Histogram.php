<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

use OpenTelemetry\API\Metrics\HistogramInterface;

final class Histogram
{
    public function __construct(
        private HistogramInterface $histogram,
        private array $attributes = [],
    ) {}

    public function record(int|float $value, array $attributes = []): void
    {
        $this->histogram->record($value, [...$this->attributes, ...$attributes]);
    }

    public function withAttributes(array $attributes): self
    {
        return new self($this->histogram, [...$this->attributes, ...$attributes]);
    }
}