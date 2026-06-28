<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability\Console\Commands;

use Illuminate\Console\Command;
use ZeroBoiler\Observability\HealthChecker;
use ZeroBoiler\Observability\HealthResult;

final class ObservabilityHealthCommand extends Command
{
    #[\Override]
    protected $signature = 'zeroboiler:observability:health {type=liveness : Health check type (liveness|readiness|startup)}';

    #[\Override]
    protected $description = 'Run observability health checks';

    public function handle(HealthChecker $checker): int
    {
        $type = $this->argument('type');

        $result = match ($type) {
            'liveness' => $checker->liveness(),
            'readiness' => $checker->readiness(),
            'startup' => $checker->startup(),
            default => $this->error('Invalid health check type: ' . $type) ?? null,
        };

        if (! $result) {
            return Command::FAILURE;
        }

        $this->displayHealthResult($result);

        return $result->isHealthy() ? Command::SUCCESS : Command::FAILURE;
    }

    private function displayHealthResult(HealthResult $result): void
    {
        $this->info('Health Status: ' . $result->status);

        $this->table(
            ['Check', 'Status', 'Output'],
            collect($result->checks)->map(fn ($check, $name): array => [
                $name,
                $check['status'],
                $check['output'] ?? '-',
            ])->toArray(),
        );

        if (! $result->isHealthy()) {
            $this->warn('Some health checks failed!');
        }
    }
}