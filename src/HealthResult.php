<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

readonly class HealthResult
{
    public function __construct(public string $status, public array $checks = []) {}

    public function isHealthy(): bool
    {
        return $this->status === 'pass';
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'checks' => $this->checks,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
