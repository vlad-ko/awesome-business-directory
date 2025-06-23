<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Business;
use Illuminate\Support\Collection;
use Sentry\State\Scope;
use function Sentry\addBreadcrumb;
use function Sentry\captureException;
use function Sentry\captureMessage;
use function Sentry\configureScope;
use function Sentry\startTransaction;
use Sentry\SentrySdk;
use Sentry\Tracing\TransactionContext;
use Sentry\Tracing\SpanContext;
use Sentry\Severity;

class BusinessLogger
{
    /**
     * Send structured log to Sentry with enhanced context
     * Uses both traditional Sentry (for Issues) and new Sentry Logs (for Logs tab)
     */
    private static function logToSentry(string $level, string $message, array $data, array $tags = [], array $context = []): void
    {
        // Send to structured log channel (includes Sentry Logs)
        Log::channel('structured')->{$level}($message, $data);

        // Enhance Sentry context
        configureScope(function (Scope $scope) use ($tags, $context, $data): void {
            // Add tags for filtering
            foreach ($tags as $key => $value) {
                $scope->setTag($key, $value);
            }

            // Add structured context
            foreach ($context as $key => $value) {
                $scope->setContext($key, $value);
            }

            // Add event-specific context
            if (isset($data['event'])) {
                $scope->setTag('event_type', $data['event']);
            }

            // Add performance context if available
            if (isset($data['processing_time_ms']) || isset($data['response_time_ms'])) {
                $performanceTime = $data['processing_time_ms'] ?? $data['response_time_ms'];
                $scope->setContext('performance', [
                    'duration_ms' => $performanceTime,
                    'grade' => self::getPerformanceGrade($performanceTime),
                ]);
            }

            // Add user context if session exists
            if (isset($data['session_id'])) {
                $scope->setUser([
                    'session_id' => $data['session_id'],
                    'ip_address' => $data['ip_address'] ?? null,
                ]);
            }
        });

        // Send critical events to both Logs tab and Issues tab
        if (in_array($level, ['error', 'critical'])) {
            // Send to traditional Sentry (Issues tab) for alerting
            captureMessage($message, self::getSentryLevel($level));
        }
    }

    /**
     * Convert Laravel log level to Sentry severity
     */
    private static function getSentryLevel(string $level): Severity
    {
        return match ($level) {
            'debug' => Severity::debug(),
            'info' => Severity::info(),
            'notice' => Severity::info(),
            'warning' => Severity::warning(),
            'error' => Severity::error(),
            'critical' => Severity::fatal(),
            'alert' => Severity::fatal(),
            'emergency' => Severity::fatal(),
            default => Severity::info(),
        };
    }

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

        // Send to Sentry Logs with enhanced context
        self::logToSentry(
            level: 'info',
            message: 'Business onboarding form viewed',
            data: $data,
            tags: [
                'feature' => 'business_onboarding',
                'event_category' => 'user_action',
                'onboarding_stage' => 'started',
            ],
            context: [
                'user_session' => [
                    'session_id' => session()->getId(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'referrer' => $request->header('referer'),
                ],
            ]
        );

        // Add Sentry breadcrumb for tracking user journey
        addBreadcrumb(
            category: 'user.action',
            message: 'Business onboarding started',
            metadata: [
                'ip_address' => $request->ip(),
                'referrer' => $request->header('referer'),
            ]
        );
    }

    /**
     * Log successful business creation
     */
    public static function businessCreated(Business $business, ?float $processingTimeMs = null): void
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

        // Send to Sentry Logs with enhanced context
        self::logToSentry(
            level: 'info',
            message: 'Business created successfully',
            data: $data,
            tags: [
                'feature' => 'business_creation',
                'event_category' => 'business_action',
                'business_industry' => $business->industry,
                'business_type' => $business->business_type,
                'business_location' => $business->city . ', ' . $business->state_province,
                'onboarding_stage' => 'completed',
            ],
            context: [
                'business' => [
                    'id' => $business->id,
                    'name' => $business->business_name,
                    'slug' => $business->business_slug,
                    'industry' => $business->industry,
                    'type' => $business->business_type,
                    'location' => [
                        'city' => $business->city,
                        'state_province' => $business->state_province,
                        'country' => $business->country,
                    ],
                    'status' => $business->status,
                    'is_verified' => $business->is_verified,
                    'is_featured' => $business->is_featured,
                ],
            ]
        );

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
    }

    /**
     * Log business validation failures
     */
    public static function validationFailed(array $errors, Request $request): void
    {
        $data = [
            'event' => 'business_validation_failed',
            'timestamp' => now()->toISOString(),
            'errors' => $errors,
            'error_count' => count($errors),
            'failed_fields' => array_keys($errors),
            'input_fields' => array_keys($request->except(['_token', 'password'])),
            'session_id' => session()->getId(),
            'ip_address' => $request->ip(),
        ];

        // Send to Sentry Logs with enhanced context
        self::logToSentry(
            level: 'warning',
            message: 'Business validation failed',
            data: $data,
            tags: [
                'feature' => 'business_validation',
                'event_category' => 'validation_error',
                'error_count' => (string) count($errors),
                'onboarding_stage' => 'validation_failed',
            ],
            context: [
                'validation_errors' => [
                    'errors' => $errors,
                    'failed_fields' => array_keys($errors),
                    'input_fields' => array_keys($request->except(['_token', 'password'])),
                    'error_count' => count($errors),
                ],
                'request_info' => [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'session_id' => session()->getId(),
                ],
            ]
        );
    }

    /**
     * Log business listing page views
     */
    public static function listingViewed(Collection $businesses, ?float $responseTimeMs = null): void
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
    public static function slowQuery(string $queryType, float $executionTimeMs, ?string $sql = null): void
    {
        $severity = $executionTimeMs > 1000 ? 'critical' : 'warning';
        $data = [
            'event' => 'slow_query',
            'timestamp' => now()->toISOString(),
            'query_type' => $queryType,
            'execution_time_ms' => $executionTimeMs,
            'query_sql' => $sql,
            'threshold_exceeded' => $severity,
        ];

        // Send to Sentry Logs with enhanced context
        self::logToSentry(
            level: $severity === 'critical' ? 'error' : 'warning',
            message: 'Slow query detected',
            data: $data,
            tags: [
                'feature' => 'database_performance',
                'event_category' => 'performance_issue',
                'query_type' => $queryType,
                'severity' => $severity,
                'performance_grade' => self::getPerformanceGrade($executionTimeMs),
            ],
            context: [
                'database_performance' => [
                    'query_type' => $queryType,
                    'execution_time_ms' => $executionTimeMs,
                    'query_sql' => $sql ? substr($sql, 0, 500) . '...' : null, // Truncate for Sentry
                    'threshold_exceeded' => $severity,
                    'performance_impact' => $executionTimeMs > 2000 ? 'high' : 'medium',
                ],
            ]
        );
    }

    /**
     * Log business search/filter operations
     */
    public static function businessSearched(array $filters, int $resultCount, ?float $searchTimeMs = null): void
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
    public static function createDatabaseSpan(string $operation, ?string $description = null): ?\Sentry\Tracing\Span
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();
        if (!$transaction) {
            return null;
        }

        $spanContext = new SpanContext();
        $spanContext->setOp('db.query');
        $spanContext->setDescription($description ?: "Database: {$operation}");
        
        $span = $transaction->startChild($spanContext);
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

        $spanContext = new SpanContext();
        $spanContext->setOp('http.client');
        $spanContext->setDescription("External API: {$service} - {$operation}");
        
        $span = $transaction->startChild($spanContext);
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

        $spanContext = new SpanContext();
        $spanContext->setOp('business.logic');
        $spanContext->setDescription("Business Logic: {$operation}");
        
        $span = $transaction->startChild($spanContext);
        $span->setData([
            'business.operation' => $operation,
            ...$metadata
        ]);
        
        return $span;
    }

    /**
     * Log application errors with business context
     */
    public static function applicationError(\Throwable $exception, ?string $context = null, array $additionalData = []): void
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

    /**
     * Log welcome page views and user engagement
     */
    public static function welcomePageViewed(Request $request, ?float $responseTimeMs = null): void
    {
        $data = [
            'event' => 'welcome_page_viewed',
            'timestamp' => now()->toISOString(),
            'session_id' => session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('referer'),
            'response_time_ms' => $responseTimeMs,
        ];

        Log::info("Welcome page viewed", $data);

        // Add Sentry breadcrumb for user journey tracking
        addBreadcrumb(
            category: 'page.view',
            message: 'Welcome page viewed',
            metadata: [
                'referrer' => $request->header('referer'),
                'response_time_ms' => $responseTimeMs,
            ]
        );

        // Set Sentry context for welcome page analytics
        configureScope(function (Scope $scope) use ($request, $responseTimeMs): void {
            $scope->setUser([
                'ip_address' => $request->ip(),
                'session_id' => session()->getId(),
            ]);
            $scope->setTag('page', 'welcome');
            $scope->setTag('feature', 'homepage');
            $scope->setContext('page_performance', [
                'response_time_ms' => $responseTimeMs,
                'referrer' => $request->header('referer'),
            ]);
        });
    }

    /**
     * Log welcome page CTA (Call-to-Action) clicks
     */
    public static function welcomeCtaClicked(string $ctaType, Request $request): void
    {
        $data = [
            'event' => 'welcome_cta_clicked', 
            'timestamp' => now()->toISOString(),
            'cta_type' => $ctaType, // 'explore_businesses', 'list_business', 'nav_browse', 'nav_join'
            'session_id' => session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        Log::info("Welcome page CTA clicked", $data);

        // Add Sentry breadcrumb for conversion tracking
        addBreadcrumb(
            category: 'user.conversion',
            message: "Welcome CTA clicked: {$ctaType}",
            metadata: [
                'cta_type' => $ctaType,
                'conversion_step' => 'homepage_to_action',
            ]
        );

        // Set conversion context for funnel analysis
        configureScope(function (Scope $scope) use ($ctaType): void {
            $scope->setTag('conversion_action', $ctaType);
            $scope->setTag('funnel_step', 'homepage_cta');
            $scope->setContext('conversion', [
                'source_page' => 'welcome',
                'action_type' => $ctaType,
                'timestamp' => now()->toISOString(),
            ]);
        });
    }

    /**
     * Log SVG rendering performance and issues
     */
    public static function svgRenderingMetrics(?float $renderTimeMs = null, array $svgData = []): void
    {
        $data = [
            'event' => 'svg_rendering_tracked',
            'timestamp' => now()->toISOString(),
            'render_time_ms' => $renderTimeMs,
            'svg_elements' => $svgData['element_count'] ?? null,
            'svg_size' => $svgData['size_bytes'] ?? null,
            'viewport_width' => $svgData['viewport_width'] ?? null,
        ];

        Log::info("SVG rendering metrics", $data);

        // Track SVG performance for optimization
        if ($renderTimeMs && $renderTimeMs > 100) {
            Log::warning("Slow SVG rendering detected", [
                'render_time_ms' => $renderTimeMs,
                'performance_impact' => 'high',
            ]);
        }

        // Add performance breadcrumb
        addBreadcrumb(
            category: 'performance.rendering',
            message: 'SVG neighborhood illustration rendered',
            metadata: [
                'render_time_ms' => $renderTimeMs,
                'performance_grade' => self::getPerformanceGrade($renderTimeMs ?? 0),
            ]
        );
    }

    /**
     * Track user engagement with welcome page features
     */
    public static function welcomeEngagement(string $engagementType, array $metadata = []): void
    {
        $data = [
            'event' => 'welcome_engagement',
            'timestamp' => now()->toISOString(),
            'engagement_type' => $engagementType, // 'scroll_to_features', 'hover_cta', 'view_svg', etc.
            'session_id' => session()->getId(),
            'metadata' => $metadata,
        ];

        Log::info("Welcome page engagement", $data);

        // Track engagement for UX optimization
        addBreadcrumb(
            category: 'user.engagement',
            message: "Welcome engagement: {$engagementType}",
            metadata: $metadata
        );

        // Set engagement context
        configureScope(function (Scope $scope) use ($engagementType, $metadata): void {
            $scope->setTag('engagement_type', $engagementType);
            $scope->setContext('engagement', [
                'type' => $engagementType,
                'metadata' => $metadata,
                'page' => 'welcome',
            ]);
        });
    }

    /**
     * Track onboarding form UI interactions
     */
    public static function onboardingFormInteraction(string $interactionType, array $metadata = []): void
    {
        $data = [
            'event' => 'onboarding_form_interaction',
            'timestamp' => now()->toISOString(),
            'interaction_type' => $interactionType, // 'section_focus', 'field_focus', 'validation_error_shown', 'emoji_hover'
            'session_id' => session()->getId(),
            'metadata' => $metadata,
        ];

        Log::info("Onboarding form interaction", $data);

        // Track form UX for optimization
        addBreadcrumb(
            category: 'form.interaction',
            message: "Form interaction: {$interactionType}",
            metadata: $metadata
        );

        // Set form interaction context
        configureScope(function (Scope $scope) use ($interactionType, $metadata): void {
            $scope->setTag('form_interaction', $interactionType);
            $scope->setContext('form_ux', [
                'interaction' => $interactionType,
                'metadata' => $metadata,
                'form_type' => 'onboarding',
            ]);
        });
    }

    /**
     * Track form completion progress and abandonment
     */
    public static function onboardingFormProgress(string $section, array $completionData = []): void
    {
        $data = [
            'event' => 'onboarding_form_progress',
            'timestamp' => now()->toISOString(),
            'current_section' => $section, // 'basic_info', 'contact', 'address', 'owner'
            'completion_percentage' => $completionData['completion_percentage'] ?? null,
            'filled_fields' => $completionData['filled_fields'] ?? [],
            'time_spent_ms' => $completionData['time_spent_ms'] ?? null,
            'session_id' => session()->getId(),
        ];

        Log::info("Onboarding form progress tracked", $data);

        // Track completion funnel
        addBreadcrumb(
            category: 'form.progress',
            message: "Form progress: {$section}",
            metadata: [
                'section' => $section,
                'completion_percentage' => $completionData['completion_percentage'] ?? 0,
            ]
        );

        // Set progress context for abandonment analysis
        configureScope(function (Scope $scope) use ($section, $completionData): void {
            $scope->setTag('form_section', $section);
            $scope->setTag('completion_stage', self::getCompletionStage($completionData['completion_percentage'] ?? 0));
            $scope->setContext('form_progress', [
                'current_section' => $section,
                'completion_data' => $completionData,
            ]);
        });
    }

    /**
     * Track form validation errors with enhanced context
     */
    public static function onboardingValidationError(string $fieldName, string $errorType, array $context = []): void
    {
        $data = [
            'event' => 'onboarding_validation_error',
            'timestamp' => now()->toISOString(),
            'field_name' => $fieldName,
            'error_type' => $errorType, // 'required', 'format', 'length', etc.
            'field_section' => self::getFieldSection($fieldName),
            'context' => $context,
            'session_id' => session()->getId(),
        ];

        Log::warning("Onboarding validation error", $data);

        // Track validation issues for UX improvement
        addBreadcrumb(
            category: 'form.validation',
            message: "Validation error: {$fieldName} - {$errorType}",
            metadata: [
                'field' => $fieldName,
                'error' => $errorType,
                'section' => self::getFieldSection($fieldName),
            ]
        );

        // Set validation context
        configureScope(function (Scope $scope) use ($fieldName, $errorType): void {
            $scope->setTag('validation_field', $fieldName);
            $scope->setTag('validation_error', $errorType);
            $scope->setContext('validation_error', [
                'field' => $fieldName,
                'error_type' => $errorType,
                'section' => self::getFieldSection($fieldName),
            ]);
        });
    }

    /**
     * Track UI performance metrics for the fun onboarding form
     */
    public static function onboardingUiPerformance(array $metrics): void
    {
        $data = [
            'event' => 'onboarding_ui_performance',
            'timestamp' => now()->toISOString(),
            'form_render_time_ms' => $metrics['form_render_time_ms'] ?? null,
            'animation_performance' => $metrics['animation_performance'] ?? null,
            'gradient_render_time_ms' => $metrics['gradient_render_time_ms'] ?? null,
            'emoji_load_time_ms' => $metrics['emoji_load_time_ms'] ?? null,
            'backdrop_blur_performance' => $metrics['backdrop_blur_performance'] ?? null,
            'session_id' => session()->getId(),
        ];

        Log::info("Onboarding UI performance metrics", $data);

        // Track UI performance for optimization
        if (($metrics['form_render_time_ms'] ?? 0) > 500) {
            Log::warning("Slow onboarding form render detected", [
                'render_time_ms' => $metrics['form_render_time_ms'],
                'performance_impact' => 'high',
            ]);
        }

        // Add performance breadcrumb
        addBreadcrumb(
            category: 'performance.ui',
            message: 'Onboarding form UI performance tracked',
            metadata: [
                'render_time_ms' => $metrics['form_render_time_ms'] ?? 0,
                'performance_grade' => self::getPerformanceGrade($metrics['form_render_time_ms'] ?? 0),
            ]
        );
    }

    /**
     * Helper method to determine completion stage
     */
    private static function getCompletionStage(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'near_complete',
            $percentage >= 75 => 'mostly_complete',
            $percentage >= 50 => 'half_complete',
            $percentage >= 25 => 'quarter_complete',
            default => 'just_started'
        };
    }

    /**
     * Helper method to map field names to form sections
     */
    private static function getFieldSection(string $fieldName): string
    {
        return match ($fieldName) {
            'business_name', 'industry', 'business_type', 'description', 'tagline' => 'basic_info',
            'primary_email', 'phone_number', 'website_url' => 'contact',
            'street_address', 'city', 'state_province', 'postal_code', 'country' => 'address',
            'owner_name', 'owner_email', 'owner_phone' => 'owner',
            default => 'unknown'
        };
    }

    /**
     * Log critical business events to Sentry
     */
    public static function criticalBusinessEvent(string $eventType, array $data = []): void
    {
        $logData = [
            'event' => 'critical_business_event',
            'event_type' => $eventType,
            'timestamp' => now()->toISOString(),
            'session_id' => session()->getId(),
            ...$data,
        ];

        self::logToSentry(
            level: 'error',
            message: "Critical business event: {$eventType}",
            data: $logData,
            tags: [
                'feature' => 'business_critical',
                'event_category' => 'critical_event',
                'event_type' => $eventType,
                'priority' => 'high',
            ],
            context: [
                'critical_event' => [
                    'type' => $eventType,
                    'data' => $data,
                    'requires_attention' => true,
                ],
            ]
        );
    }

    /**
     * Log user journey milestones to Sentry
     */
    public static function userJourneyMilestone(string $milestone, array $journeyData = []): void
    {
        $data = [
            'event' => 'user_journey_milestone',
            'milestone' => $milestone,
            'timestamp' => now()->toISOString(),
            'session_id' => session()->getId(),
            ...$journeyData,
        ];

        self::logToSentry(
            level: 'info',
            message: "User journey milestone: {$milestone}",
            data: $data,
            tags: [
                'feature' => 'user_journey',
                'event_category' => 'milestone',
                'milestone' => $milestone,
            ],
            context: [
                'user_journey' => [
                    'milestone' => $milestone,
                    'journey_data' => $journeyData,
                    'progress_tracking' => true,
                ],
            ]
        );
    }

    /**
     * Log business insights and analytics to Sentry
     */
    public static function businessInsight(string $insightType, array $metrics = []): void
    {
        $data = [
            'event' => 'business_insight',
            'insight_type' => $insightType,
            'timestamp' => now()->toISOString(),
            'metrics' => $metrics,
        ];

        self::logToSentry(
            level: 'info',
            message: "Business insight: {$insightType}",
            data: $data,
            tags: [
                'feature' => 'business_analytics',
                'event_category' => 'insight',
                'insight_type' => $insightType,
            ],
            context: [
                'business_analytics' => [
                    'insight_type' => $insightType,
                    'metrics' => $metrics,
                    'analytics_enabled' => true,
                ],
            ]
        );
    }

    /**
     * Log security-related events to Sentry
     */
    public static function securityEvent(string $eventType, array $securityData = []): void
    {
        $data = [
            'event' => 'security_event',
            'security_event_type' => $eventType,
            'timestamp' => now()->toISOString(),
            'session_id' => session()->getId(),
            'ip_address' => request()?->ip(),
            ...$securityData,
        ];

        self::logToSentry(
            level: 'warning',
            message: "Security event: {$eventType}",
            data: $data,
            tags: [
                'feature' => 'security',
                'event_category' => 'security_event',
                'security_event_type' => $eventType,
                'priority' => 'high',
            ],
            context: [
                'security' => [
                    'event_type' => $eventType,
                    'security_data' => $securityData,
                    'requires_monitoring' => true,
                ],
            ]
        );
    }

    /**
     * Log multi-step onboarding step started event
     */
    public static function multiStepStepStarted(int $step, array $context = []): void
    {
        $progress = ($step / 4) * 100; // 4 total steps
        $sessionData = session()->all();
        $previousStepsCompleted = 0;
        
        // Count completed previous steps
        for ($i = 1; $i < $step; $i++) {
            if (session()->has("onboarding_step_{$i}")) {
                $previousStepsCompleted++;
            }
        }
        
        $data = [
            'event' => 'multi_step_onboarding_step_started',
            'timestamp' => now()->toISOString(),
            'step_number' => $step,
            'progress_percentage' => $progress,
            'previous_steps_completed' => $previousStepsCompleted,
            'session_id' => session()->getId(),
            'is_returning_user' => $previousStepsCompleted > 0,
            'session_data_size' => count($sessionData),
            'has_previous_step_data' => session()->has("onboarding_step_" . ($step - 1)),
            'total_session_keys' => array_keys($sessionData),
        ];

        // Add context data
        $data = array_merge($data, $context);

        self::logToSentry(
            level: 'info',
            message: "Multi-step onboarding step {$step} started",
            data: $data,
            tags: [
                'feature' => 'multi_step_onboarding',
                'event_category' => 'step_progression',
                'step_number' => (string)$step,
                'progress_stage' => self::getProgressStage($progress),
                'user_type' => $previousStepsCompleted > 0 ? 'returning' : 'new',
            ],
            context: [
                'onboarding_funnel' => [
                    'step' => $step,
                    'progress_percentage' => $progress,
                    'previous_steps_completed' => $previousStepsCompleted,
                    'is_returning_user' => $previousStepsCompleted > 0,
                ],
            ]
        );

        // Add breadcrumb for step progression tracking
        addBreadcrumb(
            category: 'onboarding.step',
            message: "Step {$step} started",
            metadata: [
                'step' => $step,
                'progress' => $progress,
                'previous_steps' => $previousStepsCompleted,
            ]
        );
    }

    /**
     * Log multi-step onboarding step completed event
     */
    public static function multiStepStepCompleted(int $step, array $stepData = [], ?float $stepTimeMs = null): void
    {
        $progress = ($step / 4) * 100;
        $nextStep = $step + 1;
        
        $data = [
            'event' => 'multi_step_onboarding_step_completed',
            'timestamp' => now()->toISOString(),
            'step_number' => $step,
            'progress_percentage' => $progress,
            'next_step' => $nextStep <= 4 ? $nextStep : 'review',
            'step_completion_time_ms' => $stepTimeMs,
            'fields_completed' => count($stepData),
            'field_names' => array_keys($stepData),
            'session_id' => session()->getId(),
            'step_data_size' => strlen(json_encode($stepData)),
        ];

        self::logToSentry(
            level: 'info',
            message: "Multi-step onboarding step {$step} completed",
            data: $data,
            tags: [
                'feature' => 'multi_step_onboarding',
                'event_category' => 'step_completion',
                'step_number' => (string)$step,
                'progress_stage' => self::getProgressStage($progress),
                'completion_speed' => self::getCompletionSpeed($stepTimeMs),
            ],
            context: [
                'step_completion' => [
                    'step' => $step,
                    'progress_percentage' => $progress,
                    'completion_time_ms' => $stepTimeMs,
                    'fields_count' => count($stepData),
                    'next_step' => $nextStep <= 4 ? $nextStep : 'review',
                ],
            ]
        );

        // Add breadcrumb for step completion
        addBreadcrumb(
            category: 'onboarding.completion',
            message: "Step {$step} completed",
            metadata: [
                'step' => $step,
                'fields_count' => count($stepData),
                'completion_time_ms' => $stepTimeMs,
            ]
        );
    }

    /**
     * Log multi-step onboarding validation errors
     */
    public static function multiStepValidationError(int $step, array $errors, array $submittedData = []): void
    {
        $data = [
            'event' => 'multi_step_onboarding_validation_error',
            'timestamp' => now()->toISOString(),
            'step_number' => $step,
            'error_count' => count($errors),
            'error_fields' => array_keys($errors),
            'all_errors' => $errors,
            'submitted_fields' => array_keys($submittedData),
            'empty_fields' => array_keys(array_filter($submittedData, fn($value) => empty($value))),
            'session_id' => session()->getId(),
        ];

        self::logToSentry(
            level: 'warning',
            message: "Multi-step onboarding step {$step} validation failed",
            data: $data,
            tags: [
                'feature' => 'multi_step_onboarding',
                'event_category' => 'validation_error',
                'step_number' => (string)$step,
                'error_severity' => count($errors) > 3 ? 'high' : 'low',
            ],
            context: [
                'validation_failure' => [
                    'step' => $step,
                    'error_count' => count($errors),
                    'error_fields' => array_keys($errors),
                    'submitted_fields' => array_keys($submittedData),
                ],
            ]
        );
    }

    /**
     * Log review page reached (high-intent event)
     */
    public static function multiStepReviewReached(array $allStepData = [], ?float $totalJourneyTimeMs = null): void
    {
        $totalFields = 0;
        $completedSteps = 0;
        
        foreach ($allStepData as $stepKey => $stepData) {
            if (is_array($stepData)) {
                $totalFields += count($stepData);
                $completedSteps++;
            }
        }

        $data = [
            'event' => 'multi_step_onboarding_review_reached',
            'timestamp' => now()->toISOString(),
            'completed_steps' => $completedSteps,
            'total_fields_completed' => $totalFields,
            'total_journey_time_ms' => $totalJourneyTimeMs,
            'session_id' => session()->getId(),
            'conversion_likelihood' => 'high', // Users who reach review are likely to convert
            'step_data_keys' => array_keys($allStepData),
        ];

        self::logToSentry(
            level: 'info',
            message: 'Multi-step onboarding review page reached - high conversion intent',
            data: $data,
            tags: [
                'feature' => 'multi_step_onboarding',
                'event_category' => 'high_intent',
                'funnel_stage' => 'review',
                'conversion_likelihood' => 'high',
            ],
            context: [
                'conversion_funnel' => [
                    'stage' => 'review',
                    'completed_steps' => $completedSteps,
                    'total_fields' => $totalFields,
                    'journey_time_ms' => $totalJourneyTimeMs,
                    'likelihood' => 'high',
                ],
            ]
        );

        // High-value breadcrumb
        addBreadcrumb(
            category: 'onboarding.milestone',
            message: 'Review page reached - high conversion intent',
            metadata: [
                'completed_steps' => $completedSteps,
                'total_fields' => $totalFields,
                'journey_time_ms' => $totalJourneyTimeMs,
            ]
        );
    }

    /**
     * Log successful multi-step onboarding conversion
     */
    public static function multiStepConversionCompleted(Business $business, array $journeyMetrics = []): void
    {
        $data = [
            'event' => 'multi_step_onboarding_conversion_completed',
            'timestamp' => now()->toISOString(),
            'business_id' => $business->id,
            'business_name' => $business->business_name,
            'industry' => $business->industry,
            'business_type' => $business->business_type,
            'session_id' => session()->getId(),
            'conversion_status' => 'successful',
            'total_journey_time_ms' => $journeyMetrics['total_time_ms'] ?? null,
            'steps_completed' => 4,
            'review_page_visited' => $journeyMetrics['review_visited'] ?? true,
        ];

        // Add journey metrics if provided
        $data = array_merge($data, $journeyMetrics);

        self::logToSentry(
            level: 'info',
            message: 'Multi-step onboarding conversion completed successfully',
            data: $data,
            tags: [
                'feature' => 'multi_step_onboarding',
                'event_category' => 'conversion',
                'funnel_stage' => 'completed',
                'business_industry' => $business->industry,
                'business_type' => $business->business_type,
                'conversion_status' => 'successful',
            ],
            context: [
                'conversion_success' => [
                    'business_id' => $business->id,
                    'business_name' => $business->business_name,
                    'industry' => $business->industry,
                    'journey_metrics' => $journeyMetrics,
                ],
            ]
        );

        // Success breadcrumb
        addBreadcrumb(
            category: 'onboarding.success',
            message: 'Multi-step onboarding conversion completed',
            metadata: [
                'business_id' => $business->id,
                'business_name' => $business->business_name,
                'industry' => $business->industry,
            ]
        );
    }

    /**
     * Log step back navigation
     */
    public static function multiStepBackNavigation(int $fromStep, int $toStep, string $reason = 'edit'): void
    {
        $data = [
            'event' => 'multi_step_onboarding_back_navigation',
            'timestamp' => now()->toISOString(),
            'from_step' => $fromStep,
            'to_step' => $toStep,
            'steps_back' => $fromStep - $toStep,
            'navigation_reason' => $reason,
            'session_id' => session()->getId(),
        ];

        self::logToSentry(
            level: 'info',
            message: "Multi-step onboarding back navigation from step {$fromStep} to step {$toStep}",
            data: $data,
            tags: [
                'feature' => 'multi_step_onboarding',
                'event_category' => 'navigation',
                'navigation_type' => 'backward',
                'navigation_reason' => $reason,
            ],
            context: [
                'navigation_pattern' => [
                    'from_step' => $fromStep,
                    'to_step' => $toStep,
                    'direction' => 'backward',
                    'reason' => $reason,
                ],
            ]
        );
    }

    /**
     * Log potential step abandonment
     */
    public static function multiStepPotentialAbandonment(int $lastStep, array $sessionData = []): void
    {
        $completedFields = 0;
        foreach ($sessionData as $key => $value) {
            if (str_starts_with($key, 'onboarding_step_') && is_array($value)) {
                $completedFields += count($value);
            }
        }

        $data = [
            'event' => 'multi_step_onboarding_potential_abandonment',
            'timestamp' => now()->toISOString(),
            'last_completed_step' => $lastStep,
            'progress_percentage' => ($lastStep / 4) * 100,
            'completed_fields_count' => $completedFields,
            'session_id' => session()->getId(),
            'abandonment_risk' => $lastStep === 1 ? 'high' : ($lastStep >= 3 ? 'low' : 'medium'),
        ];

        self::logToSentry(
            level: 'warning',
            message: "Multi-step onboarding potential abandonment detected at step {$lastStep}",
            data: $data,
            tags: [
                'feature' => 'multi_step_onboarding',
                'event_category' => 'abandonment_risk',
                'abandonment_step' => (string)$lastStep,
                'risk_level' => $data['abandonment_risk'],
            ],
            context: [
                'abandonment_analysis' => [
                    'last_step' => $lastStep,
                    'progress_percentage' => ($lastStep / 4) * 100,
                    'completed_fields' => $completedFields,
                    'risk_level' => $data['abandonment_risk'],
                ],
            ]
        );
    }

    /**
     * Log error recovery patterns
     */
    public static function multiStepErrorRecovery(int $step, array $previousErrors, bool $recoverySuccessful): void
    {
        $data = [
            'event' => 'multi_step_onboarding_error_recovery',
            'timestamp' => now()->toISOString(),
            'step_number' => $step,
            'previous_error_count' => count($previousErrors),
            'previous_error_fields' => array_keys($previousErrors),
            'recovery_successful' => $recoverySuccessful,
            'recovery_attempt_number' => session()->get("step_{$step}_error_attempts", 0) + 1,
            'session_id' => session()->getId(),
        ];

        // Track recovery attempts
        session()->put("step_{$step}_error_attempts", $data['recovery_attempt_number']);

        self::logToSentry(
            level: $recoverySuccessful ? 'info' : 'warning',
            message: "Multi-step onboarding error recovery " . ($recoverySuccessful ? 'successful' : 'failed') . " on step {$step}",
            data: $data,
            tags: [
                'feature' => 'multi_step_onboarding',
                'event_category' => 'error_recovery',
                'step_number' => (string)$step,
                'recovery_status' => $recoverySuccessful ? 'successful' : 'failed',
                'attempt_number' => (string)$data['recovery_attempt_number'],
            ],
            context: [
                'error_recovery' => [
                    'step' => $step,
                    'previous_errors' => $previousErrors,
                    'recovery_successful' => $recoverySuccessful,
                    'attempt_number' => $data['recovery_attempt_number'],
                ],
            ]
        );
    }

    /**
     * Get progress stage label
     */
    private static function getProgressStage(float $percentage): string
    {
        return match (true) {
            $percentage <= 25 => 'early',
            $percentage <= 50 => 'mid_early',
            $percentage <= 75 => 'mid_late',
            $percentage <= 100 => 'late',
            default => 'complete',
        };
    }

    /**
     * Get completion speed classification
     */
    private static function getCompletionSpeed(?float $timeMs): string
    {
        if ($timeMs === null) return 'unknown';
        
        return match (true) {
            $timeMs < 30000 => 'fast',      // Under 30 seconds
            $timeMs < 120000 => 'normal',   // Under 2 minutes
            $timeMs < 300000 => 'slow',     // Under 5 minutes
            default => 'very_slow',         // Over 5 minutes
        };
    }
} 