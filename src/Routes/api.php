<?php

use Illuminate\Support\Facades\Route;
use Shettyanna\LaravelInteractiveDebugger\DebugCollector;

Route::get('/__debugger_api', function () {
    return response()->json(DebugCollector::getData());
});
