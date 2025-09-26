<?php

namespace App\Services;

use App\Models\Business;
use App\Services\SentryLogger;

/**
 * Focused tracking for critical user experiences only
 * 
 * This service replaces noisy tracking with focused metrics on:
 * 1. Business Discovery (Consumer Journey)
 * 2. Business Onboarding (Business Owner Journey)  
 * 3. Admin Operations (Admin Journey)
 */
class CriticalExperienceTracker
{
    /**
     * Track the start of business discovery journey
     */
    public static function trackDiscoveryStart(): void
    {
        // Only track once per session
        if (session()->has('discovery_tracked')) {
            return;
        }
        
        session(['discovery_tracked' => true]);
        
        SentryLogger::log('info', 'Critical: Discovery journey started', [
            'experience' => 'business_discovery',
            'checkpoint' => 'start',
            'session_id' => session()->getId(),
        ]);
    }
    
    /**
     * Track when user views a specific business (conversion point)
     */
    public static function trackBusinessViewed(Business $business): void
    {
        SentryLogger::log('info', 'Critical: Business viewed', [
            'experience' => 'business_discovery',
            'checkpoint' => 'business_view',
            'business_id' => $business->id,
            'business_slug' => $business->business_slug,
            'is_featured' => $business->is_featured,
            'is_verified' => $business->is_verified,
        ]);
    }
    
    /**
     * Track when user takes action to contact business (final conversion)
     */
    public static function trackBusinessContact(Business $business, string $method): void
    {
        SentryLogger::log('info', 'Critical: Business contact initiated', [
            'experience' => 'business_discovery',
            'checkpoint' => 'conversion',
            'business_id' => $business->id,
            'contact_method' => $method, // 'website', 'phone', 'email'
        ]);
    }
    
    /**
     * Track onboarding journey start
     */
    public static function trackOnboardingStart(): void
    {
        session(['onboarding_start_time' => now()]);
        
        SentryLogger::log('info', 'Critical: Onboarding started', [
            'experience' => 'business_onboarding',
            'checkpoint' => 'start',
            'session_id' => session()->getId(),
        ]);
    }
    
    /**
     * Track successful step completion (not individual fields)
     */
    public static function trackOnboardingStepComplete(int $step): void
    {
        $startTime = session('onboarding_start_time');
        $duration = $startTime ? now()->diffInSeconds($startTime) : null;
        
        SentryLogger::log('info', 'Critical: Onboarding step completed', [
            'experience' => 'business_onboarding',
            'checkpoint' => "step_{$step}_complete",
            'step' => $step,
            'duration_seconds' => $duration,
        ]);
    }
    
    /**
     * Track onboarding abandonment
     */
    public static function trackOnboardingAbandoned(int $lastStep): void
    {
        $startTime = session('onboarding_start_time');
        $duration = $startTime ? now()->diffInSeconds($startTime) : null;
        
        SentryLogger::log('warning', 'Critical: Onboarding abandoned', [
            'experience' => 'business_onboarding',
            'checkpoint' => 'abandoned',
            'last_step' => $lastStep,
            'duration_seconds' => $duration,
        ]);
    }
    
    /**
     * Track successful onboarding completion
     */
    public static function trackOnboardingComplete(Business $business): void
    {
        $startTime = session('onboarding_start_time');
        $duration = $startTime ? now()->diffInSeconds($startTime) : null;
        
        SentryLogger::log('info', 'Critical: Onboarding completed', [
            'experience' => 'business_onboarding',
            'checkpoint' => 'conversion',
            'business_id' => $business->id,
            'duration_seconds' => $duration,
            'industry' => $business->industry,
        ]);
        
        // Clear session
        session()->forget(['onboarding_start_time', 'onboarding_data']);
    }
    
    /**
     * Track critical admin actions only
     */
    public static function trackAdminCriticalAction(string $action, Business $business, array $metadata = []): void
    {
        // Only track approve/reject actions
        if (!in_array($action, ['approve', 'reject'])) {
            return;
        }
        
        SentryLogger::log('info', 'Critical: Admin action taken', [
            'experience' => 'admin_operations',
            'checkpoint' => $action,
            'business_id' => $business->id,
            'admin_id' => auth()->id(),
            ...$metadata,
        ]);
    }
    
    /**
     * Track critical errors that block user progress
     */
    public static function trackCriticalError(string $experience, string $checkpoint, \Throwable $error): void
    {
        SentryLogger::log('error', 'Critical: Experience blocked by error', [
            'experience' => $experience,
            'checkpoint' => $checkpoint,
            'error_message' => $error->getMessage(),
            'error_type' => get_class($error),
        ]);
        
        // Also capture the exception for debugging
        \Sentry\captureException($error);
    }
}
