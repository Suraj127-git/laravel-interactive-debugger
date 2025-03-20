<?php

namespace Shettyanna\LaravelInteractiveDebugger\Tests\Feature;

use Shettyanna\LaravelInteractiveDebugger\Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

uses(TestCase::class);

beforeEach(function () {
    // Setup test routes
    Route::get('/test-html', function () {
        return '<html><body></body></html>';
    });

    Route::get('/test-json', function () {
        return response()->json(['foo' => 'bar']);
    });

    Config::set('interactive-debugger.enabled', true);
});

it('injects the debugger panel into HTML responses', function () {
    $response = $this->get('/test-html');
    $response->assertSee('Debugger Panel', false);
});

it('does not inject the panel into JSON responses', function () {
    $response = $this->get('/test-json');
    $response->assertDontSee('Debugger Panel');
});

it('does not inject the panel when disabled', function () {
    config(['interactive-debugger.enabled' => false]);
    $response = $this->get('/test-html');
    $response->assertDontSee('Debugger Panel');
});

it('returns debug data via API endpoint', function () {
    // Simulate adding data
    DebugCollector::addQuery(['sql' => 'test query']);
    Log::info('Test log message');
    debug_breakpoint('test data');

    $response = $this->get('/__debugger_api');
    
    $response->assertJsonFragment(['sql' => 'test query'])
        ->assertJsonFragment(['message' => 'Test log message'])
        ->assertJsonFragment(['data' => 'test data']);
});