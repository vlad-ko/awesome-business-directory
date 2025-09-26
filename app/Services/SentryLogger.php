<?php

namespace App\Services;

use Psr\Log\LoggerInterface;
use Sentry\Severity;
use function Sentry\captureException;
use function Sentry\configureScope;

/**
 * Simplified Sentry integration service following SDK best practices
 * 
 * This service provides a thin wrapper around Sentry's native functions
 * to ensure consistent usage across the application
 */
class SentryLogger
{
    /**
     * Get the Sentry logger instance
     * Returns null if Sentry is not configured
     */
    public static function getLogger(): ?LoggerInterface
    {
        $client = \Sentry\SentrySdk::getCurrentHub()->getClient();
        return $client ? $client->getLogger() : null;
    }

    /**
     * Send a log message to Sentry
     * Falls back to Laravel logger if Sentry is not available
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        $logger = self::getLogger();
        
        if (!$logger) {
            // Fallback to Laravel logger
            \Log::log($level, $message, $context);
            return;
        }
        
        // Use Sentry's logger
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
     * Execute a callback within a traced span
     * Uses Sentry's native trace function for simplicity
     * 
     * @param callable $callback Function to execute
     * @param array $spanData Span configuration (op, name, tags)
     * @return mixed Result of the callback
     */
    public static function trace(callable $callback, array $spanData = []): mixed
    {
        // If we have an active transaction, create a child span
        $transaction = \Sentry\SentrySdk::getCurrentHub()->getTransaction();
        
        if ($transaction) {
            $span = $transaction->startChild(
                \Sentry\Tracing\SpanContext::make()
                    ->setOp($spanData['op'] ?? 'function')
                    ->setDescription($spanData['name'] ?? 'operation')
            );
            
            // Set tags on the scope instead of the span
            if (isset($spanData['tags'])) {
                configureScope(function (\Sentry\State\Scope $scope) use ($spanData) {
                    foreach ($spanData['tags'] as $key => $value) {
                        $scope->setTag($key, $value);
                    }
                });
            }
            
            // Set data if provided
            if (isset($spanData['data'])) {
                foreach ($spanData['data'] as $key => $value) {
                    if (method_exists($span, 'setData')) {
                        $span->setData([$key => $value]);
                    }
                }
            }
            
            try {
                $result = $callback($span);
                $span->setStatus(\Sentry\Tracing\SpanStatus::ok());
                $span->finish();
                return $result;
            } catch (\Throwable $e) {
                $span->setStatus(\Sentry\Tracing\SpanStatus::internalError());
                $span->finish();
                throw $e;
            }
        }
        
        // No active transaction, just execute the callback
        return $callback(null);
    }

    /**
     * Track a critical business operation
     * Focused on critical experiences only
     */
    public static function trackBusinessOperation(string $operation, array $data, callable $callback): mixed
    {
        return self::trace($callback, [
            'op' => 'business.' . $operation,
            'name' => 'Business Operation: ' . $operation,
            'tags' => [
                'business.operation' => $operation,
            ],
            'data' => $data,
        ]);
    }

    /**
     * Track a database operation
     * Note: Most DB operations are auto-instrumented by Sentry
     * Use this only for critical custom queries
     */
    public static function trackDatabaseOperation(string $query, callable $callback): mixed
    {
        return self::trace($callback, [
            'op' => 'db.query',
            'name' => 'Database Query',
            'data' => ['db.query' => $query],
        ]);
    }

    /**
     * Track an HTTP request
     * Note: HTTP client requests are auto-instrumented by Sentry
     * Use this only for critical external API calls
     */
    public static function trackHttpRequest(string $url, string $method, callable $callback): mixed
    {
        return self::trace($callback, [
            'op' => 'http.client',
            'name' => "$method $url",
            'data' => [
                'http.url' => $url,
                'http.method' => $method,
            ],
        ]);
    }

    /**
     * Add a performance measurement to the current transaction
     */
    public static function addMeasurement(string $name, float $value, string $unit = 'none'): void
    {
        // Measurements are not directly supported in the current SDK version
        // We'll add them as custom data on the transaction instead
        $transaction = \Sentry\SentrySdk::getCurrentHub()->getTransaction();
        if ($transaction && method_exists($transaction, 'setData')) {
            $transaction->setData([
                'measurements.' . $name => [
                    'value' => $value,
                    'unit' => $unit
                ]
            ]);
        }
    }

    /**
     * Set custom context on the current scope
     */
    public static function setContext(string $key, array $context): void
    {
        configureScope(function (\Sentry\State\Scope $scope) use ($key, $context) {
            $scope->setContext($key, $context);
        });
    }

    /**
     * Set tags on the current scope
     */
    public static function setTags(array $tags): void
    {
        configureScope(function (\Sentry\State\Scope $scope) use ($tags) {
            foreach ($tags as $key => $value) {
                $scope->setTag($key, $value);
            }
        });
    }
}