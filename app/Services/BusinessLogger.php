<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Business;
use Illuminate\Support\Collection;
use Sentry\State\Scope;
use function Sentry\addBreadcrumb;
use function Sentry\captureException;
use function Sentry\configureScope;
use function Sentry\startTransaction;
use Sentry\SentrySdk;
use Sentry\Tracing\TransactionContext;
use Sentry\Severity;

class BusinessLogger
{
    /**
     * Log business onboarding started event
     */
    public static function onboardingStarted(Request $request): void
    {
        $data = [
            'event' => 'business_onboarding_started',
            'timestamp' => now()->toISOString(),
            'session_id' => session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('referer'),
        ];

        Log::info("Business onboarding form viewed", $data);

        // Add Sentry breadcrumb for tracking user journey
        addBreadcrumb(
            category: 'user.action',
            message: 'Business onboarding started',
            metadata: [
                'ip_address' => $request->ip(),
                'referrer' => $request->header('referer'),
            ]
        );

        // Set Sentry user context
        configureScope(function (Scope $scope) use ($request): void {
            $scope->setUser([
                'ip_address' => $request->ip(),
                'session_id' => session()->getId(),
            ]);
            $scope->setTag('feature', 'business_onboarding');
        });
    }

    /**
     * Log successful business creation
     */
    public static function businessCreated(Business $business, float $processingTimeMs = null): void
    {
        $data = [
            'event' => 'business_created',
            'timestamp' => now()->toISOString(),
            'business_id' => $business->id,
            'business_name' => $business->business_name,
            'business_slug' => $business->business_slug,
            'industry' => $business->industry,
            'business_type' => $business->business_type,
            'city' => $business->city,
            'state_province' => $business->state_province,
            'country' => $business->country,
            'owner_email' => $business->owner_email,
            'status' => $business->status,
            'is_verified' => $business->is_verified,
            'is_featured' => $business->is_featured,
            'processing_time_ms' => $processingTimeMs,
            'session_id' => session()->getId(),
        ];

        Log::info("Business created successfully", $data);

        // Add Sentry breadcrumb for successful business creation
        addBreadcrumb(
            category: 'business.action',
            message: 'Business created successfully',
            metadata: [
                'business_id' => $business->id,
                'business_name' => $business->business_name,
                'industry' => $business->industry,
                'processing_time_ms' => $processingTimeMs,
            ]
        );

        // Set additional Sentry context
        configureScope(function (Scope $scope) use ($business, $processingTimeMs): void {
            $scope->setTag('business_industry', $business->industry);
            $scope->setTag('business_type', $business->business_type);
            $scope->setTag('business_location', $business->city . ', ' . $business->state_province);
            $scope->setContext('business', [
                'id' => $business->id,
                'name' => $business->business_name,
                'slug' => $business->business_slug,
                'processing_time_ms' => $processingTimeMs,
            ]);
        });
    }

    /**
     * Log business validation failures
     */
    public static function validationFailed(array $errors, Request $request): void
    {
        Log::warning("Business validation failed", [
            'event' => 'business_validation_failed',
            'timestamp' => now()->toISOString(),
            'errors' => $errors,
            'error_count' => count($errors),
            'failed_fields' => array_keys($errors),
            'input_fields' => array_keys($request->except(['_token', 'password'])),
            'session_id' => session()->getId(),
            'ip_address' => $request->ip(),
        ]);
    }

    /**
     * Log business listing page views
     */
    public static function listingViewed(Collection $businesses, float $responseTimeMs = null): void
    {
        $stats = [
            'total_businesses' => $businesses->count(),
            'approved_businesses' => $businesses->where('status', 'approved')->count(),
            'pending_businesses' => $businesses->where('status', 'pending')->count(),
            'featured_businesses' => $businesses->where('is_featured', true)->count(),
            'verified_businesses' => $businesses->where('is_verified', true)->count(),
        ];

        // Get industry distribution
        $industryStats = $businesses->groupBy('industry')->map->count()->toArray();

        Log::info("Business listing viewed", [
            'event' => 'business_listing_viewed',
            'timestamp' => now()->toISOString(),
            'statistics' => $stats,
            'industry_distribution' => $industryStats,
            'response_time_ms' => $responseTimeMs,
            'session_id' => session()->getId(),
        ]);
    }

    /**
     * Log empty state display
     */
    public static function emptyStateShown(string $reason = 'no_approved_businesses'): void
    {
        $pendingCount = Business::where('status', 'pending')->count();
        $totalCount = Business::count();

        Log::info("Empty state displayed", [
            'event' => 'empty_state_shown',
            'timestamp' => now()->toISOString(),
            'reason' => $reason,
            'total_businesses_in_db' => $totalCount,
            'pending_businesses' => $pendingCount,
            'session_id' => session()->getId(),
        ]);
    }

    /**
     * Log slow query performance issues
     */
    public static function slowQuery(string $queryType, float $executionTimeMs, string $sql = null): void
    {
        Log::warning("Slow query detected", [
            'event' => 'slow_query',
            'timestamp' => now()->toISOString(),
            'query_type' => $queryType,
            'execution_time_ms' => $executionTimeMs,
            'query_sql' => $sql,
            'threshold_exceeded' => $executionTimeMs > 1000 ? 'critical' : 'warning',
        ]);
    }

    /**
     * Log business search/filter operations
     */
    public static function businessSearched(array $filters, int $resultCount, float $searchTimeMs = null): void
    {
        Log::info("Business search performed", [
            'event' => 'business_searched',
            'timestamp' => now()->toISOString(),
            'filters_applied' => $filters,
            'result_count' => $resultCount,
            'search_time_ms' => $searchTimeMs,
            'session_id' => session()->getId(),
        ]);
    }

    /**
     * Start a custom transaction for business operations
     */
    public static function startBusinessTransaction(string $operation, array $metadata = []): ?\Sentry\Tracing\Transaction
    {
        $transactionContext = new TransactionContext();
        $transactionContext->setName("business.{$operation}");
        $transactionContext->setOp('business_operation');
        
        $transaction = SentrySdk::getCurrentHub()->startTransaction($transactionContext);
        
        // Set transaction data and context
        $transaction->setData([
            'business_operation' => $operation,
            ...$metadata
        ]);
        
        return $transaction;
    }

    /**
     * Create a span for database operations
     */
    public static function createDatabaseSpan(string $operation, string $description = null): ?\Sentry\Tracing\Span
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();
        if (!$transaction) {
            return null;
        }

        $span = $transaction->startChild([
            'op' => 'db.query',
            'description' => $description ?: "Database: {$operation}",
        ]);
        
        $span->setData(['db.operation' => $operation]);
        
        return $span;
    }

    /**
     * Create a span for external API calls
     */
    public static function createExternalSpan(string $service, string $operation): ?\Sentry\Tracing\Span
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();
        if (!$transaction) {
            return null;
        }

        $span = $transaction->startChild([
            'op' => 'http.client',
            'description' => "External API: {$service} - {$operation}",
        ]);
        
        $span->setData([
            'external.service' => $service,
            'external.operation' => $operation
        ]);
        
        return $span;
    }

    /**
     * Create a span for business logic operations
     */
    public static function createBusinessSpan(string $operation, array $metadata = []): ?\Sentry\Tracing\Span
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();
        if (!$transaction) {
            return null;
        }

        $span = $transaction->startChild([
            'op' => 'business.logic',
            'description' => "Business Logic: {$operation}",
        ]);
        
        $span->setData([
            'business.operation' => $operation,
            ...$metadata
        ]);
        
        return $span;
    }

    /**
     * Log application errors with business context
     */
    public static function applicationError(\Throwable $exception, string $context = null, array $additionalData = []): void
    {
        $data = [
            'event' => 'application_error',
            'timestamp' => now()->toISOString(),
            'error_type' => get_class($exception),
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'context' => $context,
            'additional_data' => $additionalData,
            'session_id' => session()->getId(),
            'stack_trace' => $exception->getTraceAsString(),
        ];

        Log::error("Application error occurred", $data);

        // Send to Sentry with additional context
        configureScope(function (Scope $scope) use ($context, $additionalData): void {
            if ($context) {
                $scope->setTag('error_context', $context);
            }
            if (!empty($additionalData)) {
                $scope->setContext('additional_data', $additionalData);
            }
            $scope->setLevel(Severity::error());
        });

        // Capture the exception in Sentry
        captureException($exception);
    }

    /**
     * Log performance metrics for business operations
     */
    public static function performanceMetric(string $operation, float $durationMs, array $metadata = []): void
    {
        $level = $durationMs > 2000 ? 'warning' : 'info';

        Log::log($level, "Performance metric recorded", [
            'event' => 'performance_metric',
            'timestamp' => now()->toISOString(),
            'operation' => $operation,
            'duration_ms' => $durationMs,
            'performance_grade' => static::getPerformanceGrade($durationMs),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get performance grade based on duration
     */
    private static function getPerformanceGrade(float $durationMs): string
    {
        if ($durationMs < 100) return 'excellent';
        if ($durationMs < 500) return 'good';
        if ($durationMs < 1000) return 'fair';
        if ($durationMs < 2000) return 'poor';
        return 'critical';
    }

    /**
     * Log user interaction events
     */
    public static function userInteraction(string $action, array $data = []): void
    {
        Log::info("User interaction", [
            'event' => 'user_interaction',
            'timestamp' => now()->toISOString(),
            'action' => $action,
            'data' => $data,
            'session_id' => session()->getId(),
        ]);
    }
} 