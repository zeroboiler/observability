<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use ZeroBoiler\Observability\Console\Commands\ObservabilityHealthCommand;
use ZeroBoiler\Observability\Console\Commands\ObservabilityTraceTestCommand;

test('health command runs successfully', function () {
    $exitCode = Artisan::call(ObservabilityHealthCommand::class, ['type' => 'liveness']);
    expect($exitCode)->toBe(0);
});

test('health command with readiness', function () {
    // Readiness checks db, cache, queue which may not all be available in test env.
    // We just verify the command runs without fatal errors.
    $exitCode = Artisan::call(ObservabilityHealthCommand::class, ['type' => 'readiness']);
    expect($exitCode)->toBeIn([0, 1]);
});

test('health command with startup', function () {
    // Startup runs readiness checks (db, cache, queue) which may fail in test env.
    // We just verify the command runs without fatal errors.
    $exitCode = Artisan::call(ObservabilityHealthCommand::class, ['type' => 'startup']);
    expect($exitCode)->toBeIn([0, 1]);
});

test('trace test command runs successfully', function () {
    // Set up a clean observability instance for this test
    $observability = app(\ZeroBoiler\Observability\Observability::class);
    $observability->initialize();

    $exitCode = Artisan::call(ObservabilityTraceTestCommand::class, ['operations' => 3]);

    // Clean up any error handlers set up by Artisan
    restore_error_handler();
    restore_exception_handler();

    expect($exitCode)->toBe(0);
});
