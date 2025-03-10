<?php

namespace Shettyanna\LaravelInteractiveDebugger;

use Illuminate\Support\Facades\Cache;

class DebugCollector
{
    protected static $cacheKey = 'interactive_debugger_data';

    public static function addQuery(array $data)
    {
        self::appendData('queries', $data);
    }

    public static function addLog(array $data)
    {
        self::appendData('logs', $data);
    }

    public static function addBreakpoint(array $data)
    {
        self::appendData('breakpoints', $data);
    }

    protected static function appendData(string $type, array $data)
    {
        $debugData = Cache::get(self::$cacheKey, []);
        $debugData[$type][] = $data;
        Cache::forever(self::$cacheKey, $debugData);
    }

    public static function getData()
    {
        return Cache::get(self::$cacheKey, []);
    }

    public static function clearData()
    {
        Cache::forget(self::$cacheKey);
    }
}
