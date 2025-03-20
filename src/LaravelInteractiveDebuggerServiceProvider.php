<?php

namespace Shettyanna\LaravelInteractiveDebugger;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Log\Events\MessageLogged;
use Shettyanna\LaravelInteractiveDebugger\Middleware\InjectDebuggerMiddleware;

class LaravelInteractiveDebuggerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/interactive-debugger.php' => config_path('interactive-debugger.php'),
        ], 'config');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'interactive-debugger');

        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');

        $this->app['router']->pushMiddlewareToGroup('web', InjectDebuggerMiddleware::class);

        DB::listen(function ($query) {
            DebugCollector::addQuery([
                'sql'      => $query->sql,
                'bindings' => $query->bindings,
                'time'     => $query->time,
            ]);
        });

        Event::listen(MessageLogged::class, function ($log) {
            DebugCollector::addLog([
                'level'   => $log->level,
                'message' => $log->message,
                'context' => $log->context,
            ]);
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/interactive-debugger.php', 'interactive-debugger');
        
        // Register the middleware only if not running in console or in testing environment
        if (!$this->app->runningInConsole() && !$this->app->environment('testing')) {
            $this->app->register(RouteServiceProvider::class);
        }
    }
}
