<?php

use Shettyanna\LaravelInteractiveDebugger\DebugCollector;
use Illuminate\Support\Facades\Cache;

it('adds and retrieves query data', function () {
    DebugCollector::clearData();
    DebugCollector::addQuery(['sql' => 'SELECT * FROM users', 'bindings' => [], 'time' => 5]);
    \$data = DebugCollector::getData();
    expect(\$data['queries'][0]['sql'])->toBe('SELECT * FROM users');
});
