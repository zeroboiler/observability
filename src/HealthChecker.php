<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

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
            'status' => $otelHealth->isHealthy() ? 'pass' : 'fail',
            'output' => $otelHealth->isHealthy() ? 'Observability operational' : 'Observability degraded',
        ];

        $status = collect($checks)->every(fn ($check) => $check['status'] === 'pass') ? 'pass' : 'fail';

        return new HealthResult($status, $checks);
    }

    public function readiness(): HealthResult
    {
        $checks = $this->liveness()->checks;

        $checks['database'] = $this->checkDatabase();
        $checks['cache'] = $this->checkCache();
        $checks['queue'] = $this->checkQueue();

        $status = collect($checks)->every(fn ($check) => $check['status'] === 'pass') ? 'pass' : 'degraded';

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
        } catch (\Throwable $e) {
            $checks['otel_init'] = [
                'status' => 'fail',
                'output' => "OpenTelemetry init failed: {$e->getMessage()}",
            ];
        }

        $status = collect($checks)->every(fn ($check) => $check['status'] === 'pass') ? 'pass' : 'fail';

        return new HealthResult($status, $checks);
    }

    private function checkDatabase(): array
    {
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();

            return [
                'status' => 'pass',
                'output' => 'Database connection successful',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'fail',
                'output' => "Database connection failed: {$e->getMessage()}",
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            \Illuminate\Support\Facades\Cache::put('health-check', 'ok', 5);

            if (\Illuminate\Support\Facades\Cache::get('health-check') === 'ok') {
                return [
                    'status' => 'pass',
                    'output' => 'Cache operational',
                ];
            }

            return [
                'status' => 'fail',
                'output' => 'Cache read-back failed',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'fail',
                'output' => "Cache check failed: {$e->getMessage()}",
            ];
        }
    }

    private function checkQueue(): array
    {
        try {
            \Illuminate\Support\Facades\Queue::size();

            return [
                'status' => 'pass',
                'output' => 'Queue connection successful',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'fail',
                'output' => "Queue check failed: {$e->getMessage()}",
            ];
        }
    }
}