<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

use Illuminate\Support\ServiceProvider;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\ContextInterface;
use ZeroBoiler\Observability\AutoInstrumentation\DatabaseInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\HttpInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\QueueInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\RedisInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\MailInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\CacheInstrumentation;
use ZeroBoiler\Observability\Console\Commands\ObservabilityHealthCommand;
use ZeroBoiler\Observability\Console\Commands\ObservabilityTraceTestCommand;

final class ObservabilityServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/observability.php',
            'zeroboiler.observability'
        );

        $this->app->singleton(Observability::class);
        $this->app->alias(Observability::class, 'observability');

        $this->app->singleton(OtelTracer::class);
        $this->app->alias(OtelTracer::class, TracerInterface::class);

        $this->app->singleton(HealthChecker::class);
        $this->app->singleton(MetricsRegistry::class);

        $this->registerFacades();
    }

    #[\Override]
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/observability.php' => config_path('zeroboiler/observability.php'),
        ], 'observability-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ObservabilityHealthCommand::class,
                ObservabilityTraceTestCommand::class,
            ]);
        }

        $this->registerAutoInstrumentation();
        $this->registerLoggingBridge();
    }

    private function registerFacades(): void
    {
        $this->app->singleton('observability.span', fn () => Span::current());
    }

    private function registerAutoInstrumentation(): void
    {
        if (! config('zeroboiler.observability.auto_instrumentation.enabled', true)) {
            return;
        }

        $instrumentations = [
            HttpInstrumentation::class,
            DatabaseInstrumentation::class,
            QueueInstrumentation::class,
            RedisInstrumentation::class,
            MailInstrumentation::class,
            CacheInstrumentation::class,
        ];

        foreach ($instrumentations as $instrumentation) {
            if ($this->app->make($instrumentation)->isEnabled()) {
                $this->app->make($instrumentation)->register();
            }
        }
    }

    private function registerLoggingBridge(): void
    {
        if (! config('zeroboiler.observability.logging.bridge_enabled', true)) {
            return;
        }

        $this->app->make(Observability::class)->registerLoggingBridge();
    }
}