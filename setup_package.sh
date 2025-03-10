#!/bin/bash
# setup_package.sh
# This script creates the Laravel Interactive Debugger package structure and files.

set -e

# Create directories
mkdir -p config src/Middleware src/Routes resources/views tests/Feature tests/Unit

# Create composer.json
cat > composer.json << 'EOF'
{
  "name": "shettyanna/laravel-interactive-debugger",
  "description": "A real-time, interactive debugging panel for Laravel applications.",
  "type": "library",
  "license": "MIT",
  "require": {
    "php": "^8.1",
    "illuminate/support": "^10.0|^11.0"
  },
  "autoload": {
    "psr-4": {
      "Shettyanna\\LaravelInteractiveDebugger\\": "src/"
    },
    "files": [
      "src/Helpers.php"
    ]
  },
  "extra": {
    "laravel": {
      "providers": [
        "Shettyanna\\LaravelInteractiveDebugger\\LaravelInteractiveDebuggerServiceProvider"
      ]
    }
  },
  "require-dev": {
    "pestphp/pest": "^1.0",
    "orchestra/testbench": "^7.0"
  }
}
EOF

# Create configuration file
cat > config/interactive-debugger.php << 'EOF'
<?php
return [
    'enabled'          => env('DEBUGGER_ENABLED', true),
    'panel_position'   => 'bottom-right', // Options: bottom-right, bottom-left, top-right, top-left
    'update_interval'  => 3000, // in milliseconds
];
EOF

# Create LaravelInteractiveDebuggerServiceProvider.php
cat > src/LaravelInteractiveDebuggerServiceProvider.php << 'EOF'
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
        $this->mergeConfigFrom(__DIR__ . '/../config/interactive-debugger.php', 'interactive-debugger');
    }
}
EOF

# Create DebugCollector.php
cat > src/DebugCollector.php << 'EOF'
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
EOF

# Create InjectDebuggerMiddleware.php
cat > src/Middleware/InjectDebuggerMiddleware.php << 'EOF'
<?php

namespace Shettyanna\LaravelInteractiveDebugger\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectDebuggerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (config('interactive-debugger.enabled') && str_contains($response->headers->get('Content-Type'), 'text/html')) {
            $content = $response->getContent();
            $panel = view('interactive-debugger::panel')->render();
            $content = str_replace('</body>', $panel . '</body>', $content);
            $response->setContent($content);
        }

        return $response;
    }
}
EOF

# Create Blade view for the debugging panel
cat > resources/views/panel.blade.php << 'EOF'
<div x-data="debuggerPanel()" 
     class="fixed {{ config('interactive-debugger.panel_position') }} m-4 p-4 bg-gray-800 text-white rounded shadow-lg z-50" 
     style="width: 300px; height: 400px; overflow-y: auto;">
    <div class="flex justify-between items-center mb-2">
        <h4 class="text-lg font-bold">Debugger Panel</h4>
        <button @click="togglePanel()" class="text-sm">Minimize</button>
    </div>
    <template x-if="visible">
        <div>
            <h5 class="mt-2 font-semibold">Queries</h5>
            <ul>
                <template x-for="query in data.queries" :key="query.sql">
                    <li class="text-xs" x-text="query.sql"></li>
                </template>
            </ul>
            <h5 class="mt-2 font-semibold">Logs</h5>
            <ul>
                <template x-for="log in data.logs" :key="log.message">
                    <li class="text-xs" x-text="log.level + ': ' + log.message"></li>
                </template>
            </ul>
        </div>
    </template>
</div>

<script>
    function debuggerPanel() {
        return {
            visible: true,
            data: { queries: [], logs: [] },
            togglePanel() { this.visible = !this.visible; },
            fetchData() {
                fetch('/__debugger_api')
                  .then(response => response.json())
                  .then(json => { this.data = json; });
            },
            init() {
                this.fetchData();
                setInterval(this.fetchData, {{ config('interactive-debugger.update_interval', 3000) }});
            }
        }
    }
</script>
EOF

# Create API route file
cat > src/Routes/api.php << 'EOF'
<?php

use Illuminate\Support\Facades\Route;
use Shettyanna\LaravelInteractiveDebugger\DebugCollector;

Route::get('/__debugger_api', function () {
    return response()->json(DebugCollector::getData());
});
EOF

# Create Helpers.php
cat > src/Helpers.php << 'EOF'
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
EOF

# Create Pest Unit Test
cat > tests/Unit/DebugCollectorTest.php << 'EOF'
<?php

use Shettyanna\LaravelInteractiveDebugger\DebugCollector;
use Illuminate\Support\Facades\Cache;

it('adds and retrieves query data', function () {
    DebugCollector::clearData();
    DebugCollector::addQuery(['sql' => 'SELECT * FROM users', 'bindings' => [], 'time' => 5]);
    \$data = DebugCollector::getData();
    expect(\$data['queries'][0]['sql'])->toBe('SELECT * FROM users');
});
EOF

# Create Pest Feature Test
cat > tests/Feature/DebuggerPanelTest.php << 'EOF'
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
EOF

echo "Laravel Interactive Debugger package structure created successfully."
