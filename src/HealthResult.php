<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

readonly class HealthResult
{
    public string $status;
    public array $checks;

    public function __construct(
        string $status,
        array $checks = [],
    ) {
        $this->status = $status;
        $this->checks = $checks;
    }

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