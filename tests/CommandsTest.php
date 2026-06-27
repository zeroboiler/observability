<?php

declare(strict_types=1);

use ZeroBoiler\Observability\Console\Commands\ObservabilityHealthCommand;
use ZeroBoiler\Observability\Console\Commands\ObservabilityTraceTestCommand;
use ZeroBoiler\Observability\Tests\Pest;

test('health command runs successfully', function () {
    $this->artisan(ObservabilityHealthCommand::class, ['type' => 'liveness'])
        ->assertSuccessful();
});

test('health command with readiness', function () {
    $this->artisan(ObservabilityHealthCommand::class, ['type' => 'readiness'])
        ->assertSuccessful();
});

test('health command with startup', function () {
    $this->artisan(ObservabilityHealthCommand::class, ['type' => 'startup'])
        ->assertSuccessful();
});

test('trace test command runs successfully', function () {
    $this->artisan(ObservabilityTraceTestCommand::class, ['operations' => 3])
        ->assertSuccessful()
        ->expectsOutput('Running 3 test trace operations...')
        ->expectsOutput('✓ Test traces sent to backend');
});