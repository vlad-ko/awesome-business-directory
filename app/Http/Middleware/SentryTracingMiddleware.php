<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sentry\SentrySdk;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionContext;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\TraceId;
use Sentry\Tracing\SpanId;
use Sentry\Tracing\SpanStatus;
use Symfony\Component\HttpFoundation\Response;

class SentryTracingMiddleware
{
    /**
     * Handle an incoming request and set up distributed tracing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the current hub
        $hub = SentrySdk::getCurrentHub();
        
        // Extract trace context from headers (distributed tracing)
        $sentryTrace = $request->header('sentry-trace');
        $baggage = $request->header('baggage');
        
        // Also check for trace headers passed via form data (for form submissions)
        $formSentryTrace = $request->input('_sentry_sentry_trace');
        $formBaggage = $request->input('_sentry_baggage');
        
        // Use form trace headers if present (they take precedence for form submissions)
        if ($formSentryTrace) {
            $sentryTrace = $formSentryTrace;
        }
        if ($formBaggage) {
            $baggage = $formBaggage;
        }
        
        // Create transaction context
        $transactionContext = new TransactionContext();
        $transactionContext->setName($this->getTransactionName($request));
        $transactionContext->setOp('http.server.request');
        
        // Set up distributed tracing if we have trace headers
        if ($sentryTrace) {
            // Parse the sentry-trace header
            $traceData = $this->parseSentryTrace($sentryTrace);
            if ($traceData) {
                try {
                    // Convert string IDs to proper Sentry objects
                    $traceId = new TraceId($traceData['trace_id']);
                    $parentSpanId = new SpanId($traceData['span_id']);
                    
                    $transactionContext->setParentSpanId($parentSpanId);
                    $transactionContext->setTraceId($traceId);
                    
                    // Add baggage and trace metadata
                    $transactionContext->setData([
                        'distributed_tracing' => [
                            'baggage' => $baggage,
                            'sampled' => $traceData['sampled'],
                            'trace_origin' => 'frontend'
                        ]
                    ]);
                } catch (\Exception $e) {
                    // Log trace parsing error but continue without distributed tracing
                    error_log("Sentry distributed tracing setup error: " . $e->getMessage());
                }
            }
        }
        
        // Add request context
        $contextData = [
            'request_data' => [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->route()?->getName(),
                'has_frontend_trace' => !empty($sentryTrace),
            ]
        ];
        
        // Add user context if available
        if ($request->user()) {
            $contextData['user_context'] = [
                'id' => $request->user()->id,
                'email' => $request->user()->email,
                'is_admin' => $request->user()->is_admin ?? false,
            ];
        }
        
        $transactionContext->setData($contextData);
        
        // Start the transaction
        $transaction = $hub->startTransaction($transactionContext);
        $hub->configureScope(function ($scope) use ($transaction) {
            $scope->setSpan($transaction);
        });
        
        // Create a span for the controller action
        $controllerSpan = null;
        if ($request->route()) {
            $action = $request->route()->getActionName();
            $spanContext = new SpanContext();
            $spanContext->setOp('controller.action');
            $spanContext->setDescription($action);
            $spanContext->setData([
                'controller' => $this->getControllerName($action),
                'method' => $this->getMethodName($action),
                'route_name' => $request->route()->getName(),
                'route_parameters' => $request->route()->parameters(),
            ]);
            
            $controllerSpan = $transaction->startChild($spanContext);
        }
        
        try {
            // Process the request
            $response = $next($request);
            
            // Set transaction status based on response
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                $transaction->setStatus(SpanStatus::ok());
                if ($controllerSpan) {
                    $controllerSpan->setStatus(SpanStatus::ok());
                }
            } elseif ($response->getStatusCode() >= 400) {
                $transaction->setStatus(SpanStatus::createFromHttpStatusCode($response->getStatusCode()));
                if ($controllerSpan) {
                    $controllerSpan->setStatus(SpanStatus::createFromHttpStatusCode($response->getStatusCode()));
                }
            }
            
            // Add response data
            $transaction->setData([
                'response_data' => [
                    'status_code' => $response->getStatusCode(),
                    'content_type' => $response->headers->get('content-type'),
                    'content_length' => $response->headers->get('content-length'),
                ]
            ]);
            
            return $response;
            
        } catch (\Throwable $exception) {
            // Handle exceptions
            $transaction->setStatus(SpanStatus::internalError());
            if ($controllerSpan) {
                $controllerSpan->setStatus(SpanStatus::internalError());
            }
            
            // Capture the exception with tracing context
            \Sentry\captureException($exception, [
                'tags' => [
                    'component' => 'http_request',
                    'route' => $request->route()?->getName(),
                    'method' => $request->method(),
                ],
                'extra' => [
                    'transaction_id' => $transaction->getSpanId(),
                    'trace_id' => $transaction->getTraceId(),
                    'has_frontend_trace' => !empty($sentryTrace),
                ]
            ]);
            
            throw $exception;
            
        } finally {
            // Finish spans and transaction
            if ($controllerSpan) {
                $controllerSpan->finish();
            }
            $transaction->finish();
        }
    }
    
    /**
     * Generate a meaningful transaction name based on the request.
     */
    private function getTransactionName(Request $request): string
    {
        $route = $request->route();
        
        if ($route && $route->getName()) {
            return $route->getName();
        }
        
        if ($route) {
            $uri = $route->uri();
            // Replace parameters with placeholders
            $uri = preg_replace('/\{[^}]+\}/', '*', $uri);
            return $request->method() . ' /' . ltrim($uri, '/');
        }
        
        return $request->method() . ' ' . $request->getPathInfo();
    }
    
    /**
     * Parse the sentry-trace header.
     */
    private function parseSentryTrace(string $sentryTrace): ?array
    {
        // sentry-trace format: {trace_id}-{span_id}-{sampled}
        $parts = explode('-', $sentryTrace);
        
        if (count($parts) >= 2) {
            return [
                'trace_id' => $parts[0],
                'span_id' => $parts[1],
                'sampled' => $parts[2] ?? null,
            ];
        }
        
        return null;
    }
    
    /**
     * Extract controller name from action string.
     */
    private function getControllerName(string $action): string
    {
        if (str_contains($action, '@')) {
            return explode('@', $action)[0];
        }
        
        return $action;
    }
    
    /**
     * Extract method name from action string.
     */
    private function getMethodName(string $action): string
    {
        if (str_contains($action, '@')) {
            return explode('@', $action)[1];
        }
        
        return 'unknown';
    }
} 