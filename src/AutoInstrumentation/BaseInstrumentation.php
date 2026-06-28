<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability\AutoInstrumentation;

use Illuminate\Contracts\Foundation\Application;

abstract class BaseInstrumentation
{
    public function __construct(
        protected Application $app,
    ) {}

    public function isEnabled(): bool
    {
        return config('zeroboiler.observability.auto_instrumentation.' . $this->getKey(), true);
    }

    abstract protected function getKey(): string;

    abstract public function register(): void;
}
