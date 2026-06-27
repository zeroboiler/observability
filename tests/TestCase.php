<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}