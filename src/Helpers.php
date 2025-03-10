<?php

if (! function_exists('debug_breakpoint')) {
    function debug_breakpoint($data = null)
    {
        Shettyanna\LaravelInteractiveDebugger\DebugCollector::addBreakpoint([
            'data' => $data,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
