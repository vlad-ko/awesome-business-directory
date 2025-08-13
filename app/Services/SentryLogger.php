<?php

namespace App\Services;

use Psr\Log\LoggerInterface;
use Sentry\Severity;
use function Sentry\captureException;
use function Sentry\captureMessage;
use function Sentry\configureScope;

/**
 * Modern Sentry Logger following official SDK patterns
 */
class SentryLogger
{
    /**
     * Get the Sentry logger instance
     */
    public static function getLogger(): ?LoggerInterface
    {
        $client = \Sentry\SentrySdk::getCurrentHub()->getClient();
        return $client ? $client->getLogger() : null;
    }

    /**
     * Log with structured data using Sentry's logger
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        $logger = self::getLogger();
        
        if (!$logger) {
            // Fallback to Laravel logging if Sentry is not configured
            \Log::log($level, $message, $context);
            return;
        }
        
        // Use Sentry's logger with proper severity mapping
        match ($level) {
            'debug' => $logger->debug($message, $context),
            'info' => $logger->info($message, $context),
            'notice' => $logger->info($message, $context),
            'warning' => $logger->warning($message, $context),
            'error' => $logger->error($message, $context),
            'critical', 'alert', 'emergency' => $logger->error($message, $context),
            default => $logger->info($message, $context),
        };
    }

    /**
     * Start a span using modern Sentry pattern
     * 
     * @param array{op: string, name: string} $spanData
     * @param callable $callback
     * @return mixed
     */
    public static function startSpan(array $spanData, callable $callback): mixed
    {
        $hub = \Sentry\SentrySdk::getCurrentHub();
        $parent = $hub->getSpan();
        
        if ($parent === null) {
            // No active transaction, create one
            $context = new \Sentry\Tracing\TransactionContext();
            $context->setName($spanData['name'] ?? 'transaction');
            $context->setOp($spanData['op'] ?? 'default');
            
            $transaction = $hub->startTransaction($context);
            $hub->configureScope(function ($scope) use ($transaction) {
                $scope->setSpan($transaction);
            });
            
            try {
                $result = $callback($transaction);
                $transaction->finish();
                return $result;
            } catch (\Throwable $e) {
                $transaction->setStatus(\Sentry\Tracing\SpanStatus::internalError());
                $transaction->finish();
                throw $e;
            }
        }
        
        // Create child span
        $spanContext = new \Sentry\Tracing\SpanContext();
        $spanContext->setOp($spanData['op'] ?? 'default');
        $spanContext->setDescription($spanData['name'] ?? '');
        
        $span = $parent->startChild($spanContext);
        
        try {
            $result = $callback($span);
            $span->finish();
            return $result;
        } catch (\Throwable $e) {
            $span->setStatus(\Sentry\Tracing\SpanStatus::internalError());
            $span->finish();
            throw $e;
        }
    }

    /**
     * Track business operation with proper span
     */
    public static function trackBusinessOperation(string $operation, array $data, callable $callback): mixed
    {
        return self::startSpan([
            'op' => 'business.' . $operation,
            'name' => 'Business Operation: ' . $operation,
        ], function ($span) use ($data, $callback) {
            // Set span data
            if (method_exists($span, 'setData')) {
                $span->setData($data);
            }
            
            try {
                $result = $callback($span);
                $span->setStatus(\Sentry\Tracing\SpanStatus::ok());
                return $result;
            } catch (\Throwable $e) {
                $span->setStatus(\Sentry\Tracing\SpanStatus::internalError());
                captureException($e);
                throw $e;
            }
        });
    }

    /**
     * Track database operation with proper span
     */
    public static function trackDatabaseOperation(string $query, callable $callback): mixed
    {
        return self::startSpan([
            'op' => 'db.query',
            'name' => 'Database Query',
        ], function ($span) use ($query, $callback) {
            if (method_exists($span, 'setData')) {
                $span->setData(['db.query' => $query]);
            }
            
            $startTime = microtime(true);
            try {
                $result = $callback($span);
                $duration = (microtime(true) - $startTime) * 1000;
                
                if (method_exists($span, 'setData')) {
                    $span->setData(['duration_ms' => $duration]);
                }
                $span->setStatus(\Sentry\Tracing\SpanStatus::ok());
                
                // Log slow queries
                if ($duration > 1000) {
                    self::log('warning', 'Slow database query detected', [
                        'query' => $query,
                        'duration_ms' => $duration,
                    ]);
                }
                
                return $result;
            } catch (\Throwable $e) {
                $span->setStatus(\Sentry\Tracing\SpanStatus::internalError());
                captureException($e);
                throw $e;
            }
        });
    }

    /**
     * Track HTTP client request with proper span
     */
    public static function trackHttpRequest(string $url, string $method, callable $callback): mixed
    {
        return self::startSpan([
            'op' => 'http.client',
            'name' => "$method $url",
        ], function ($span) use ($url, $method, $callback) {
            if (method_exists($span, 'setData')) {
                $span->setData([
                    'http.url' => $url,
                    'http.method' => $method,
                ]);
            }
            
            try {
                $result = $callback($span);
                $span->setStatus(\Sentry\Tracing\SpanStatus::ok());
                return $result;
            } catch (\Throwable $e) {
                $span->setStatus(\Sentry\Tracing\SpanStatus::internalError());
                captureException($e);
                throw $e;
            }
        });
    }
}