<?php

declare(strict_types=1);

namespace ZeroBoiler\Observability\Console\Commands;

use Illuminate\Console\Command;
use ZeroBoiler\Observability\Span;

final class ObservabilityTraceTestCommand extends Command
{
    protected $signature = 'zeroboiler:observability:trace-test {operations=5 : Number of test operations to run}';
    protected $description = 'Test OpenTelemetry tracing with sample spans';

    public function handle(): int
    {
        $operations = (int) $this->argument('operations');

        $this->info("Running {$operations} test trace operations...");

        for ($i = 1; $i <= $operations; $i++) {
            $this->runTestOperation($i);
            $this->output->write('.');
        }

        $this->newLine();
        $this->info("✓ Test traces sent to backend");
        $this->comment("Check your OTel backend (Jaeger, Tempo, etc.) for the traces");

        return Command::SUCCESS;
    }

    private function runTestOperation(int $index): void
    {
        Span::trace("test_operation_{$index}", function (Span $span) use ($index) {
            $span->setAttribute('test.index', $index);
            $span->setAttribute('test.timestamp', now()->toIso8601String());

            Span::trace('sub_operation_a', function (Span $span) use ($index) {
                $span->setAttribute('sub_operation', 'a');
                $span->addEvent('processing_started');

                usleep(10000);

                $span->addEvent('processing_completed');
            });

            Span::trace('sub_operation_b', function (Span $span) use ($index) {
                $span->setAttribute('sub_operation', 'b');

                usleep(5000);
            });

            $span->addEvent('test_completed', ['operations' => 2]);
        });
    }
}