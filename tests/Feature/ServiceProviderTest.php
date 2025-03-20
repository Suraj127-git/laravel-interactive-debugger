<?php

namespace Shettyanna\LaravelInteractiveDebugger\Tests\Feature;

use Shettyanna\LaravelInteractiveDebugger\Tests\TestCase;
use Shettyanna\LaravelInteractiveDebugger\DebugCollector;

uses(TestCase::class);

beforeEach(function () {
    config([
        'interactive-debugger.enabled' => true,
        'database.default' => 'testing'
    ]);
});