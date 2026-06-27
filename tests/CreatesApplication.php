<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;

class CreatesApplication
{
    public function createApplication(): Application
    {
        $app = require __DIR__ . '/../../../../../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
}