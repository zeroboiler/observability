<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION | Attribute::TARGET_CLASS)]
readonly class Trace
{
    public function __construct(public string $operation, public ?string $kind = null, public array $attributes = []) {}
}
