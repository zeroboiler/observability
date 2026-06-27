<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability\AutoInstrumentation;

abstract class BaseInstrumentation
{
    public function isEnabled(): bool
    {
        return config('zeroboiler.observability.auto_instrumentation.' . $this->getKey(), true);
    }

    abstract protected function getKey(): string;

    abstract public function register(): void;
}