<?php

namespace App\Services;

use App\Models\Business;
use App\Services\SentryLogger;
use Illuminate\Support\Facades\Session;
use Sentry\SentrySdk;
use Sentry\Severity;
use function Sentry\captureException;
use function Sentry\configureScope;

/**
 * Focused tracking for critical user experiences only
 * 
 * This service replaces noisy tracking with focused metrics on:
 * 1. Business Discovery (Consumer Journey)
 * 2. Business Onboarding (Business Owner Journey)  
 * 3. Admin Operations (Admin Journey)
 * 
 * Uses Sentry spans/transactions for proper performance monitoring
 * and breadcrumbs for critical checkpoints
 */
class CriticalExperienceTracker
{
    /**
     * Track the start of business discovery journey
     * This creates a breadcrumb as it's a milestone, not a measurable operation
     */
    public static function trackDiscoveryStart(): void
    {
        // Only track once per session
        if (Session::has('discovery_tracked')) {
            return;
        }
        
        Session::put('discovery_tracked', true);
        
        // Add breadcrumb for discovery start
        \Sentry\addBreadcrumb(
            'critical.discovery',
            'Discovery journey started',
            [
                'checkpoint' => 'start',
                'session_id' => Session::getId(),
            ],
            'info'
        );
        
        // Add measurement to track discovery sessions
        SentryLogger::addMeasurement('critical.discovery.started', 1, 'none');
    }
    
    /**
     * Track when user views a specific business (conversion point)
     * This adds a breadcrumb and sets tags on the current transaction
     */
    public static function trackBusinessViewed(Business $business): void
    {
        // Add breadcrumb for business view
        \Sentry\addBreadcrumb(
            'critical.discovery',
            'Business viewed: ' . $business->business_name,
            [
                'checkpoint' => 'business_view',
                'business_id' => $business->id,
                'business_slug' => $business->business_slug,
                'is_featured' => $business->is_featured,
                'is_verified' => $business->is_verified,
            ],
            'info'
        );
        
        // Set span attributes directly on current span
        $span = \Sentry\SentrySdk::getCurrentHub()->getSpan();
        if ($span && method_exists($span, 'setData')) {
            $span->setData([
                'critical.checkpoint' => 'business_view',
                'critical.business_type' => $business->is_featured ? 'featured' : 'regular',
                'business.id' => $business->id,
                'business.name' => $business->business_name,
                'business.featured' => $business->is_featured,
                'business.verified' => $business->is_verified,
            ]);
        }
        
        // Still set tags on scope for filtering
        SentryLogger::setTags([
            'critical.checkpoint' => 'business_view',
            'critical.business_type' => $business->is_featured ? 'featured' : 'regular',
        ]);
        
        // Add measurements
        SentryLogger::addMeasurement('critical.business.viewed', 1, 'none');
        if ($business->is_featured) {
            SentryLogger::addMeasurement('critical.featured_business.viewed', 1, 'none');
        }
    }
    
    /**
     * Track when user takes action to contact business (final conversion)
     * This is a critical conversion event
     */
    public static function trackBusinessContact(Business $business, string $method): void
    {
        // Add breadcrumb for conversion
        \Sentry\addBreadcrumb(
            'critical.discovery',
            'Business contact initiated',
            [
                'checkpoint' => 'conversion',
                'business_id' => $business->id,
                'business_slug' => $business->business_slug,
                'contact_method' => $method, // 'website', 'phone', 'email'
            ],
            'info'
        );
        
        // Set conversion tags
        SentryLogger::setTags([
            'critical.conversion' => 'business_contact',
            'critical.contact_method' => $method,
        ]);
        
        // Add conversion measurement
        SentryLogger::addMeasurement('critical.conversion.business_contact', 1, 'none');
        SentryLogger::addMeasurement('critical.conversion.' . $method, 1, 'none');
    }
    
    /**
     * Track when user starts onboarding process
     * This creates a breadcrumb as it's a milestone
     */
    public static function trackOnboardingStart(): void
    {
        // Only track once per session
        if (Session::has('onboarding_start_time')) {
            return;
        }
        
        Session::put('onboarding_start_time', now());
        
        // Add breadcrumb for onboarding start
        \Sentry\addBreadcrumb(
            'critical.onboarding',
            'Onboarding started',
            [
                'checkpoint' => 'start',
                'timestamp' => now()->toISOString(),
            ],
            'info'
        );
    }
    
    /**
     * Track completion of onboarding step
     * This adds a breadcrumb and measures step duration
     */
    public static function trackOnboardingStepComplete(int $step): void
    {
        $startTime = Session::get('onboarding_start_time');
        $duration = $startTime ? now()->diffInSeconds($startTime) : null;
        
        // Add breadcrumb for step completion
        \Sentry\addBreadcrumb(
            'critical.onboarding',
            "Step {$step} completed",
            [
                'checkpoint' => "step_{$step}_complete",
                'step' => $step,
                'duration_seconds' => $duration,
            ],
            'info'
        );
        
        // Set tags and measurements
        SentryLogger::setTags([
            'critical.onboarding_step' => $step,
        ]);
        
        // Add step completion measurement
        SentryLogger::addMeasurement('critical.onboarding.step_completed', $step, 'none');
        if ($duration !== null) {
            SentryLogger::addMeasurement('critical.onboarding.step_duration', $duration, 'second');
        }
    }
    
    /**
     * Track when user abandons onboarding
     * This is a critical drop-off point
     */
    public static function trackOnboardingAbandoned(int $lastStep): void
    {
        $startTime = Session::get('onboarding_start_time');
        $duration = $startTime ? now()->diffInSeconds($startTime) : null;
        
        // Add breadcrumb for abandonment
        \Sentry\addBreadcrumb(
            'critical.onboarding',
            'Onboarding abandoned',
            [
                'checkpoint' => 'abandoned',
                'last_step' => $lastStep,
                'duration_seconds' => $duration,
            ],
            'warning'
        );
        
        // Set abandonment tags
        SentryLogger::setTags([
            'critical.abandoned' => 'onboarding',
            'critical.abandoned_step' => $lastStep,
        ]);
        
        // Add abandonment measurement
        SentryLogger::addMeasurement('critical.onboarding.abandoned', 1, 'none');
        SentryLogger::addMeasurement('critical.onboarding.abandoned_at_step', $lastStep, 'none');
        
        // Clear onboarding session
        self::clearOnboardingSession();
    }
    
    /**
     * Track successful onboarding completion (conversion)
     * This is a critical conversion event
     */
    public static function trackOnboardingComplete(Business $business): void
    {
        $startTime = Session::get('onboarding_start_time');
        $duration = $startTime ? now()->diffInSeconds($startTime) : null;
        
        // Add breadcrumb for conversion
        \Sentry\addBreadcrumb(
            'critical.onboarding',
            'Onboarding completed',
            [
                'checkpoint' => 'conversion',
                'business_id' => $business->id,
                'duration_seconds' => $duration,
                'industry' => $business->industry,
            ],
            'info'
        );
        
        // Set conversion tags
        SentryLogger::setTags([
            'critical.conversion' => 'onboarding_complete',
            'critical.industry' => $business->industry,
        ]);
        
        // Add conversion measurements
        SentryLogger::addMeasurement('critical.conversion.onboarding', 1, 'none');
        if ($duration !== null) {
            SentryLogger::addMeasurement('critical.onboarding.total_duration', $duration, 'second');
        }
        
        // Clear onboarding session
        self::clearOnboardingSession();
    }
    
    private static function clearOnboardingSession(): void
    {
        Session::forget('onboarding_start_time');
        Session::forget('onboarding_data'); // Assuming this stores all step data
    }
    
    /**
     * Track critical admin actions (approve/reject only)
     * These are business-critical operations
     */
    public static function trackAdminCriticalAction(string $action, Business $business, array $metadata = []): void
    {
        $criticalActions = ['approve', 'reject'];
        
        if (!in_array($action, $criticalActions)) {
            return;
        }
        
        // Add breadcrumb for admin action
        \Sentry\addBreadcrumb(
            'critical.admin',
            "Business {$action}d",
            array_merge([
                'checkpoint' => $action,
                'business_id' => $business->id,
                'business_name' => $business->business_name,
                'admin_id' => auth()->id(),
            ], $metadata),
            'info'
        );
        
        // Set admin action tags
        SentryLogger::setTags([
            'critical.admin_action' => $action,
            'critical.admin_id' => auth()->id(),
        ]);
        
        // Add admin action measurement
        SentryLogger::addMeasurement('critical.admin.' . $action, 1, 'none');
    }
    
    /**
     * Track critical errors that block user journeys
     * These errors prevent users from completing critical paths
     */
    public static function trackCriticalError(
        string $experience,
        string $checkpoint,
        \Throwable $error,
        array $context = []
    ): void {
        // Set context for the error
        configureScope(function (\Sentry\State\Scope $scope) use ($experience, $checkpoint, $context) {
            $scope->setTag('critical.experience', $experience);
            $scope->setTag('critical.checkpoint', $checkpoint);
            $scope->setTag('critical.error', 'true');
            $scope->setContext('critical_path', array_merge([
                'experience' => $experience,
                'checkpoint' => $checkpoint,
            ], $context));
        });
        
        // Capture the exception
        captureException($error);
    }
}