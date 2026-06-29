<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability;

use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MeterProviderFactory;
use OpenTelemetry\SDK\Resource\ResourceInfo;

final class MetricsRegistry
{
    private readonly MeterProvider $meterProvider;

    private readonly MeterInterface $meter;

    private array $counters = [];

    private array $gauges = [];

    private array $histograms = [];

    public function __construct()
    {
        $resource = ResourceInfo::create(Attributes::create([
            'service.name' => config('zeroboiler.observability.service_name', config('app.name', 'laravel')),
            'service.version' => config('zeroboiler.observability.service_version', '1.0.0'),
        ]));

        $factory = new MeterProviderFactory;
        $this->meterProvider = $factory->create($resource);
        $this->meter = $this->meterProvider->getMeter('zeroboiler-metrics');
    }

    public function counter(string $name, string $description = '', array $attributes = []): Counter
    {
        if (! isset($this->counters[$name])) {
            $this->counters[$name] = $this->meter->createCounter($name, $description);
        }

        return new Counter($this->counters[$name], $attributes);
    }

    public function gauge(string $name, string $description = '', array $attributes = []): Gauge
    {
        if (! isset($this->gauges[$name])) {
            $this->gauges[$name] = $this->meter->createUpDownCounter($name, $description);
        }

        return new Gauge($this->gauges[$name], $attributes);
    }

    public function histogram(string $name, string $description = '', array $attributes = []): Histogram
    {
        if (! isset($this->histograms[$name])) {
            $this->histograms[$name] = $this->meter->createHistogram($name, $description);
        }

        return new Histogram($this->histograms[$name], $attributes);
    }

    public function flush(): void
    {
        $this->meterProvider->forceFlush();
    }

    /**
     * Reset all cached metric instances.
     *
     * In long-running processes (Octane/FrankenPHP), cached Counter/Gauge/Histogram
     * instances can become stale. This clears the local cache so subsequent calls
     * create fresh instances from the meter.
     */
    public function reset(): void
    {
        $this->counters = [];
        $this->gauges = [];
        $this->histograms = [];
    }
}
