<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

use Illuminate\Support\ServiceProvider;
use ZeroBoiler\Observability\AutoInstrumentation\CacheInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\DatabaseInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\HttpInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\MailInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\QueueInstrumentation;
use ZeroBoiler\Observability\AutoInstrumentation\RedisInstrumentation;
use ZeroBoiler\Observability\Console\Commands\ObservabilityFlushCommand;
use ZeroBoiler\Observability\Console\Commands\ObservabilityHealthCommand;
use ZeroBoiler\Observability\Console\Commands\ObservabilityTraceTestCommand;

final class ObservabilityServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/observability.php',
            'zeroboiler.observability'
        );

        $this->app->singleton(Observability::class);
        $this->app->alias(Observability::class, 'observability');

        $this->app->singleton(OtelTracer::class);
        // Bind OtelTracer concretely; do NOT alias as TracerInterface
        // to allow other tracer implementations. Consumers should type-hint
        // OtelTracer or use Observability::getTracer().

        $this->app->singleton(HealthChecker::class);
        $this->app->singleton(MetricsRegistry::class);

        $this->registerFacades();
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/observability.php' => config_path('zeroboiler/observability.php'),
        ], 'observability-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ObservabilityHealthCommand::class,
                ObservabilityTraceTestCommand::class,
                ObservabilityFlushCommand::class,
            ]);
        }

        $this->registerAutoInstrumentation();
        $this->registerLoggingBridge();
    }

   private function registerFacades(): void
   {
        // Use bind() instead of singleton() so Span::current() is resolved
        // fresh each time — the active span changes during request lifecycle.
        $this->app->bind('observability.span', fn (): Span => Span::current());
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
