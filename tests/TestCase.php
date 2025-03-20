<?php

namespace Shettyanna\LaravelInteractiveDebugger\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Shettyanna\LaravelInteractiveDebugger\Tests\CreatesApplication;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Load package configuration
        $this->app['config']->set('interactive-debugger', [
            'enabled' => true,
            'panel_position' => 'bottom-0 right-0',
            'update_interval' => 3000,
        ]);
    }
}