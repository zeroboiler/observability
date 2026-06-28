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
        // Global switch takes precedence - if auto instrumentation is off, nothing runs
        if (! config('zeroboiler.observability.auto_instrumentation.enabled', true)) {
            return false;
        }

        return (bool) config('zeroboiler.observability.auto_instrumentation.' . $this->getKey() . '.enabled', true);
    }

    abstract protected function getKey(): string;

    abstract public function register(): void;
}
