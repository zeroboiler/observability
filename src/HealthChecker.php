<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

final class HealthChecker
{
    public function liveness(): HealthResult
    {
        $checks = [];

        $checks['app'] = [
            'status' => 'pass',
            'output' => 'Application is running',
            'timestamp' => now()->toIso8601String(),
        ];

        $observability = app(Observability::class);
        $otelHealth = $observability->health();

        $checks['observability'] = [
            'status' => $otelHealth->isHealthy() ? 'pass' : 'warn',
            'output' => $otelHealth->isHealthy() ? 'Observability operational' : 'Observability degraded',
        ];

        // Liveness only fails when the app itself is broken, not when observability is degraded
        $status = $checks['app']['status'] === 'pass' ? 'pass' : 'fail';

        return new HealthResult($status, $checks);
    }

    public function readiness(): HealthResult
    {
        $checks = $this->liveness()->checks;

        $checks['database'] = $this->checkDatabase();
        $checks['cache'] = $this->checkCache();
        $checks['queue'] = $this->checkQueue();

        $status = collect($checks)->every(fn ($check): bool => $check['status'] === 'pass') ? 'pass' : 'degraded';

        return new HealthResult($status, $checks);
    }

    public function startup(): HealthResult
    {
        $checks = $this->readiness()->checks;

        $observability = app(Observability::class);

        try {
            $observability->initialize();

            $checks['otel_init'] = [
                'status' => 'pass',
                'output' => 'OpenTelemetry initialized successfully',
            ];
        } catch (\Throwable $throwable) {
            $checks['otel_init'] = [
                'status' => 'fail',
                'output' => 'OpenTelemetry init failed: '.$throwable->getMessage(),
            ];
        }

        $status = collect($checks)->every(fn ($check): bool => $check['status'] === 'pass') ? 'pass' : 'fail';

        return new HealthResult($status, $checks);
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return [
                'status' => 'pass',
                'output' => 'Database connection successful',
            ];
        } catch (\Throwable $throwable) {
            return [
                'status' => 'fail',
                'output' => 'Database connection failed: '.$throwable->getMessage(),
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            Cache::put('health-check', 'ok', 5);

            if (Cache::get('health-check') === 'ok') {
                return [
                    'status' => 'pass',
                    'output' => 'Cache operational',
                ];
            }

            return [
                'status' => 'fail',
                'output' => 'Cache read-back failed',
            ];
        } catch (\Throwable $throwable) {
            return [
                'status' => 'fail',
                'output' => 'Cache check failed: '.$throwable->getMessage(),
            ];
        }
    }

    private function checkQueue(): array
    {
        try {
            Queue::size();

            return [
                'status' => 'pass',
                'output' => 'Queue connection successful',
            ];
        } catch (\Throwable $throwable) {
            return [
                'status' => 'fail',
                'output' => 'Queue check failed: '.$throwable->getMessage(),
            ];
        }
    }
}
