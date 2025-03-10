<?php

use Shettyanna\LaravelInteractiveDebugger\DebugCollector;

beforeEach(function () {
    DebugCollector::clearData();
});

it('injects the debugging panel in HTML responses', function () {
    \$response = \$this->get('/');
    \$response->assertSee('Debugger Panel');
});

it('returns debug data via the API', function () {
    DebugCollector::addLog(['level' => 'info', 'message' => 'Test log', 'context' => []]);
    \$response = \$this->get('/__debugger_api');
    \$response->assertJsonFragment(['message' => 'Test log']);
});
