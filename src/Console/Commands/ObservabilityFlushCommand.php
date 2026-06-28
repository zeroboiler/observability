<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\Observability\Console\Commands;

use Illuminate\Console\Command;
use ZeroBoiler\Observability\MetricsRegistry;

/**
 * Flush all observability state from the current process.
 *
 * Useful in long-running processes (Octane/FrankenPHP) to reset
 * metrics, spans, and traces without a full process restart.
 */
final class ObservabilityFlushCommand extends Command
{
    /** @var string */
    #[\Override]
    protected $signature = 'zeroboiler:observability:flush';

    /** @var string */
    #[\Override]
    protected $description = 'Flush all observability state (metrics, spans, traces) from the current process';

    public function handle(MetricsRegistry $metrics): int
    {
        // Reset cached metric instances (counters, gauges, histograms)
        $metrics->reset();

        // Force flush any pending telemetry data to the exporter
        $metrics->flush();

        $this->info('Observability state flushed successfully.');

        return Command::SUCCESS;
    }
}
