<?php

use Shettyanna\LaravelInteractiveDebugger\DebugCollector;
use Illuminate\Support\Facades\Cache;
use Shettyanna\LaravelInteractiveDebugger\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    DebugCollector::clearData();
});

it('stores and retrieves query data', function () {
    DebugCollector::addQuery(['sql' => 'SELECT * FROM users']);
    $data = DebugCollector::getData();
    
    expect($data['queries'])->toHaveCount(1)
        ->and($data['queries'][0]['sql'])->toBe('SELECT * FROM users');
});

it('stores and retrieves log entries', function () {
    DebugCollector::addLog(['message' => 'Log entry']);
    $data = DebugCollector::getData();
    
    expect($data['logs'])->toHaveCount(1)
        ->and($data['logs'][0]['message'])->toBe('Log entry');
});

it('stores and retrieves breakpoints', function () {
    DebugCollector::addBreakpoint(['data' => 'Breakpoint data']);
    $data = DebugCollector::getData();
    
    expect($data['breakpoints'])->toHaveCount(1)
        ->and($data['breakpoints'][0]['data'])->toBe('Breakpoint data');
});

it('clears all stored data', function () {
    DebugCollector::addQuery(['sql' => 'test']);
    DebugCollector::clearData();
    
    expect(DebugCollector::getData())->toBeEmpty();
});