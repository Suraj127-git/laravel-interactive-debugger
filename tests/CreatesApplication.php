<?php

namespace Shettyanna\LaravelInteractiveDebugger\Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    public function createApplication()
    {
        $app = require __DIR__.'/../../vendor/laravel/laravel/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        
        // Register your package's service provider
        $app->register(\Shettyanna\LaravelInteractiveDebugger\LaravelInteractiveDebuggerServiceProvider::class);

        return $app;
    }
}