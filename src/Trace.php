<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

use Attribute;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\ContextInterface;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION | Attribute::TARGET_CLASS)]
readonly class Trace
{
    public string $operation;
    public ?string $kind;
    public array $attributes;

    public function __construct(
        string $operation,
        ?string $kind = null,
        array $attributes = [],
    ) {
        $this->operation = $operation;
        $this->kind = $kind;
        $this->attributes = $attributes;
    }
}