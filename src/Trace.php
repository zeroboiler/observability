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
    public function __construct(public string $operation, public ?string $kind = null, public array $attributes = [])
    {
    }
}