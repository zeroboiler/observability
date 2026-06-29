<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use ZeroBoiler\Observability\Console\Commands\ObservabilityHealthCommand;
use ZeroBoiler\Observability\Console\Commands\ObservabilityTraceTestCommand;
use ZeroBoiler\Observability\Observability;

/**
 * Run an Artisan command, then remove any extra error/exception handlers
 * that Artisan registered during the call.
 */
function runArtisanAndCleanHandlers(string $command, array $parameters = []): int
{
    // Capture handler stacks before
    $errorBefore = set_error_handler(fn (): true => true);
    restore_error_handler();
    $exceptionBefore = set_exception_handler(fn (): true => true);
    restore_exception_handler();

    $exitCode = Artisan::call($command, $parameters);

    // Iteratively pop handlers until we're back to the pre-call state
    for ($i = 0; $i < 5; $i++) {
        $errorNow = set_error_handler(fn (): true => true);
        restore_error_handler();

        if ($errorNow === $errorBefore) {
            break;
        }

        restore_error_handler(); // pop one handler
    }

    for ($i = 0; $i < 5; $i++) {
        $exceptionNow = set_exception_handler(fn (): true => true);
        restore_exception_handler();

        if ($exceptionNow === $exceptionBefore) {
            break;
        }

        restore_exception_handler();
    }

    return $exitCode;
}

test('health command runs successfully', function (): void {
    $exitCode = runArtisanAndCleanHandlers(ObservabilityHealthCommand::class, ['type' => 'liveness']);
    expect($exitCode)->toBe(0);
});

test('health command with readiness', function (): void {
    $exitCode = runArtisanAndCleanHandlers(ObservabilityHealthCommand::class, ['type' => 'readiness']);
    expect($exitCode)->toBeIn([0, 1]);
});

test('health command with startup', function (): void {
    $exitCode = runArtisanAndCleanHandlers(ObservabilityHealthCommand::class, ['type' => 'startup']);
    expect($exitCode)->toBeIn([0, 1]);
});

test('trace test command runs successfully', function (): void {
    $observability = app(Observability::class);
    $observability->initialize();

    $exitCode = runArtisanAndCleanHandlers(ObservabilityTraceTestCommand::class, ['operations' => 3]);

    expect($exitCode)->toBe(0);
});
