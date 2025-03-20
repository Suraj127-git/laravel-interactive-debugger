<?php

use function Shettyanna\LaravelInteractiveDebugger\debug_breakpoint;
use Shettyanna\LaravelInteractiveDebugger\DebugCollector;
use Shettyanna\LaravelInteractiveDebugger\Tests\TestCase;

uses(TestCase::class);

it('adds breakpoint using helper function', function () {
    debug_breakpoint(['custom' => 'data']);
    
    $data = DebugCollector::getData();
    expect($data['breakpoints'][0]['data']['custom'])->toBe('data');
});