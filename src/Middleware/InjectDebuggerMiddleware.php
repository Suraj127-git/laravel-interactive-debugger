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
