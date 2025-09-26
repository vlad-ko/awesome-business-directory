<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use function Sentry\configureScope;
use App\Services\SentryLogger;

/**
 * Modern Sentry Context Middleware using latest patterns
 */
class SentryContextMiddleware
{
    /**
     * Handle an incoming request with Sentry context
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Start a span for the entire request
        return SentryLogger::trace(function ($span) use ($request, $next) {
            // Set request attributes
            if (method_exists($span, 'setData')) {
                $span->setData([
                    'http.method' => $request->method(),
                    'http.url' => $request->fullUrl(),
                    'http.scheme' => $request->getScheme(),
                    'http.host' => $request->getHost(),
                    'http.target' => $request->getRequestUri(),
                ]);
            }
            
            // Set user context
            if ($user = $request->user()) {
                configureScope(function ($scope) use ($user) {
                    $scope->setUser([
                        'id' => $user->id,
                        'email' => $user->email,
                        'username' => $user->name ?? $user->email,
                        'ip_address' => request()->ip(),
                    ]);
                    
                    $scope->setTag('user.type', $user->is_admin ? 'admin' : 'regular');
                });
            }
            
            // Set request context
            configureScope(function ($scope) use ($request) {
                $scope->setContext('request', [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'route' => $request->route()?->getName(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                
                // Set route tags
                if ($route = $request->route()) {
                    $scope->setTag('route.name', $route->getName() ?? 'unnamed');
                    $scope->setTag('route.action', $route->getActionName());
                }
                
                // Set feature tags based on route
                $scope->setTag('feature', $this->getFeatureFromRoute($request));
            });
            
            try {
                $response = $next($request);
                
                // Set response attributes
                if (method_exists($span, 'setData')) {
                    $span->setData([
                        'http.status_code' => $response->getStatusCode(),
                        'http.response_content_length' => strlen($response->getContent()),
                    ]);
                }
                
                // Set status based on response code
                if ($response->getStatusCode() >= 400) {
                    $span->setStatus(\Sentry\Tracing\SpanStatus::createFromHttpStatusCode($response->getStatusCode()));
                } else {
                    $span->setStatus(\Sentry\Tracing\SpanStatus::ok());
                }
                
                return $response;
            } catch (\Throwable $e) {
                $span->setStatus(\Sentry\Tracing\SpanStatus::internalError());
                throw $e;
            }
        }, [
            'op' => 'http.server',
            'name' => $this->getTransactionName($request),
        ]);
    }
    
    /**
     * Get transaction name from request
     */
    private function getTransactionName(Request $request): string
    {
        $route = $request->route();
        
        if ($route && $route->getName()) {
            return $route->getName();
        }
        
        if ($route) {
            return $request->method() . ' ' . $route->uri();
        }
        
        return $request->method() . ' ' . $request->getPathInfo();
    }
    
    /**
     * Determine feature from route
     */
    private function getFeatureFromRoute(Request $request): string
    {
        $path = $request->path();
        $routeName = $request->route()?->getName() ?? '';
        
        return match (true) {
            str_starts_with($routeName, 'business.onboard') => 'business_onboarding',
            str_starts_with($routeName, 'business.') => 'business_directory',
            str_starts_with($routeName, 'admin.') => 'admin_panel',
            str_starts_with($path, 'admin') => 'admin_panel',
            str_starts_with($path, 'onboard') => 'business_onboarding',
            str_starts_with($path, 'businesses') => 'business_directory',
            $path === '/' => 'homepage',
            default => 'general',
        };
    }
}