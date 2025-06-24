<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SentryJavaScriptIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function javascript_files_are_properly_structured()
    {
        // Test that our JavaScript files exist and have proper structure
        $this->assertFileExists(resource_path('js/sentry.js'));
        $this->assertFileExists(resource_path('js/app.js'));
        
        $sentryContent = file_get_contents(resource_path('js/sentry.js'));
        $appContent = file_get_contents(resource_path('js/app.js'));
        
        // Verify Sentry.js structure
        $this->assertStringContainsString('import * as Sentry from "@sentry/browser"', $sentryContent);
        $this->assertStringContainsString('import { BrowserTracing } from "@sentry/tracing"', $sentryContent);
        $this->assertStringContainsString('export function initializeAlpineIntegration', $sentryContent);
        $this->assertStringContainsString('export const SentryPerformance', $sentryContent);
        $this->assertStringContainsString('export const BusinessDirectoryTracking', $sentryContent);
        
        // Verify App.js structure
        $this->assertStringContainsString('import Alpine from \'alpinejs\'', $appContent);
        $this->assertStringContainsString('import { initializeSentryFrontend', $appContent);
        $this->assertStringContainsString('Alpine.data(\'businessDirectory\'', $appContent);
        $this->assertStringContainsString('Alpine.data(\'onboardingForm\'', $appContent);
        $this->assertStringContainsString('Alpine.data(\'welcomePage\'', $appContent);
        $this->assertStringContainsString('Alpine.data(\'adminDashboard\'', $appContent);
    }

    /** @test */
    public function sentry_configuration_includes_all_required_options()
    {
        $sentryContent = file_get_contents(resource_path('js/sentry.js'));
        
        // Verify comprehensive Sentry configuration
        $this->assertStringContainsString('dsn: window.sentryConfig?.dsn', $sentryContent);
        $this->assertStringContainsString('environment: window.sentryConfig?.environment', $sentryContent);
        $this->assertStringContainsString('tracesSampleRate:', $sentryContent);
        $this->assertStringContainsString('autoSessionTracking: true', $sentryContent);
        $this->assertStringContainsString('beforeSend(event, hint)', $sentryContent);
        $this->assertStringContainsString('beforeBreadcrumb(breadcrumb, hint)', $sentryContent);
        $this->assertStringContainsString('new BrowserTracing', $sentryContent);
    }

    /** @test */
    public function alpine_components_have_comprehensive_functionality()
    {
        $appContent = file_get_contents(resource_path('js/app.js'));
        
        // Business Directory Component
        $this->assertStringContainsString('searchTerm: \'\'', $appContent);
        $this->assertStringContainsString('selectedIndustry: \'\'', $appContent);
        $this->assertStringContainsString('filteredBusinesses: []', $appContent);
        $this->assertStringContainsString('BusinessDirectoryTracking.trackSearchInteraction', $appContent);
        $this->assertStringContainsString('BusinessDirectoryTracking.trackBusinessCardClick', $appContent);
        
        // Onboarding Form Component
        $this->assertStringContainsString('currentStep: 1', $appContent);
        $this->assertStringContainsString('totalSteps: 4', $appContent);
        $this->assertStringContainsString('step1: {', $appContent);
        $this->assertStringContainsString('step2: {', $appContent);
        $this->assertStringContainsString('step3: {', $appContent);
        $this->assertStringContainsString('step4: {', $appContent);
        $this->assertStringContainsString('validateCurrentStep()', $appContent);
        $this->assertStringContainsString('BusinessDirectoryTracking.trackOnboardingProgress', $appContent);
        
        // Welcome Page Component
        $this->assertStringContainsString('Alpine.data(\'welcomePage\'', $appContent);
        $this->assertStringContainsString('trackCTA(action)', $appContent);
        
        // Admin Dashboard Component
        $this->assertStringContainsString('pendingBusinesses: []', $appContent);
        $this->assertStringContainsString('stats: {', $appContent);
        $this->assertStringContainsString('pending: 0', $appContent);
        $this->assertStringContainsString('approved: 0', $appContent);
        $this->assertStringContainsString('total: 0', $appContent);
    }

    /** @test */
    public function sentry_tracking_utilities_are_comprehensive()
    {
        $sentryContent = file_get_contents(resource_path('js/sentry.js'));
        
        // Alpine.js Integration
        $this->assertStringContainsString('Alpine.directive(\'sentry-track\'', $sentryContent);
        $this->assertStringContainsString('Alpine.directive(\'track\'', $sentryContent);
        $this->assertStringContainsString('Alpine.directive(\'track-change\'', $sentryContent);
        
        // Performance Tracking
        $this->assertStringContainsString('trackPageLoad()', $sentryContent);
        $this->assertStringContainsString('trackAjaxRequests()', $sentryContent);
        $this->assertStringContainsString('trackFormMetrics(formElement, formName)', $sentryContent);
        
        // Business Directory Tracking
        $this->assertStringContainsString('trackBusinessCardClick(businessId, businessName)', $sentryContent);
        $this->assertStringContainsString('trackOnboardingProgress(step, stepData)', $sentryContent);
        $this->assertStringContainsString('trackSearchInteraction(searchTerm, resultsCount)', $sentryContent);
        
        // Error Handling
        $this->assertStringContainsString('Sentry.captureException(error', $sentryContent);
        $this->assertStringContainsString('Sentry.addBreadcrumb', $sentryContent);
        $this->assertStringContainsString('Sentry.captureMessage', $sentryContent);
    }

    /** @test */
    public function package_json_includes_required_dependencies()
    {
        $packageJson = json_decode(file_get_contents(base_path('package.json')), true);
        
        // Verify Sentry dependencies
        $this->assertArrayHasKey('@sentry/browser', $packageJson['dependencies']);
        $this->assertArrayHasKey('@sentry/tracing', $packageJson['dependencies']);
        $this->assertArrayHasKey('alpinejs', $packageJson['dependencies']);
    }

    /** @test */
    public function sentry_integration_follows_best_practices()
    {
        $sentryContent = file_get_contents(resource_path('js/sentry.js'));
        
        // Error boundary patterns
        $this->assertStringContainsString('try {', $sentryContent);
        $this->assertStringContainsString('} catch (error) {', $sentryContent);
        $this->assertStringContainsString('Sentry.captureException(error', $sentryContent);
        
        // Performance monitoring
        $this->assertStringContainsString('performance.getEntriesByType', $sentryContent);
        $this->assertStringContainsString('first-paint', $sentryContent);
        $this->assertStringContainsString('first-contentful-paint', $sentryContent);
        
        // User experience tracking
        $this->assertStringContainsString('category: \'user.interaction\'', $sentryContent);
        $this->assertStringContainsString('category: \'form.progression\'', $sentryContent);
        $this->assertStringContainsString('category: \'business.interaction\'', $sentryContent);
        
        // Context enrichment
        $this->assertStringContainsString('tags: {', $sentryContent);
        $this->assertStringContainsString('extra: {', $sentryContent);
        $this->assertStringContainsString('getPageType()', $sentryContent);
    }

    /** @test */
    public function alpine_error_handling_is_implemented()
    {
        $appContent = file_get_contents(resource_path('js/app.js'));
        
        // Global error store
        $this->assertStringContainsString('Alpine.store(\'errors\'', $appContent);
        $this->assertStringContainsString('add(field, message)', $appContent);
        $this->assertStringContainsString('remove(field)', $appContent);
        $this->assertStringContainsString('clear()', $appContent);
        $this->assertStringContainsString('has(field)', $appContent);
        $this->assertStringContainsString('get(field)', $appContent);
        
        // Error handling in components
        $this->assertStringContainsString('try {', $appContent);
        $this->assertStringContainsString('} catch (error) {', $appContent);
        $this->assertStringContainsString('console.error', $appContent);
        $this->assertStringContainsString('this.errors =', $appContent);
    }

    /** @test */
    public function form_validation_follows_established_patterns()
    {
        $appContent = file_get_contents(resource_path('js/app.js'));
        
        // Validation methods
        $this->assertStringContainsString('validateCurrentStep()', $appContent);
        $this->assertStringContainsString('isRequired(field)', $appContent);
        $this->assertStringContainsString('getFieldLabel(field)', $appContent);
        
        // Required fields configuration
        $this->assertStringContainsString('requiredFields = {', $appContent);
        $this->assertStringContainsString('1: [\'business_name\', \'industry\', \'business_type\', \'description\']', $appContent);
        $this->assertStringContainsString('2: [\'primary_email\', \'phone_number\']', $appContent);
        $this->assertStringContainsString('3: [\'street_address\', \'city\', \'state_province\', \'postal_code\']', $appContent);
        $this->assertStringContainsString('4: [\'owner_name\', \'owner_email\']', $appContent);
        
        // Error handling
        $this->assertStringContainsString('this.errors = {}', $appContent);
        $this->assertStringContainsString('Object.keys(this.errors).length === 0', $appContent);
    }

    /** @test */
    public function performance_monitoring_is_comprehensive()
    {
        $sentryContent = file_get_contents(resource_path('js/sentry.js'));
        
        // Page load metrics
        $this->assertStringContainsString('navigation.loadEventEnd - navigation.loadEventStart', $sentryContent);
        $this->assertStringContainsString('navigation.domContentLoadedEventEnd', $sentryContent);
        
        // AJAX monitoring
        $this->assertStringContainsString('window.axios.interceptors.request.use', $sentryContent);
        $this->assertStringContainsString('window.axios.interceptors.response.use', $sentryContent);
        $this->assertStringContainsString('duration > 2000', $sentryContent); // Slow request threshold
        
        // Form performance
        $this->assertStringContainsString('fieldInteractions++', $sentryContent);
        $this->assertStringContainsString('completionTime = new Date() - startTime', $sentryContent);
        $this->assertStringContainsString('beforeunload', $sentryContent); // Abandonment tracking
    }

    /** @test */
    public function business_specific_tracking_is_implemented()
    {
        $sentryContent = file_get_contents(resource_path('js/sentry.js'));
        
        // Business directory specific events
        $this->assertStringContainsString('category: \'business.interaction\'', $sentryContent);
        $this->assertStringContainsString('category: \'onboarding.progress\'', $sentryContent);
        $this->assertStringContainsString('feature: \'business_discovery\'', $sentryContent);
        $this->assertStringContainsString('feature: \'business_search\'', $sentryContent);
        
        // Context data
        $this->assertStringContainsString('business_id: businessId', $sentryContent);
        $this->assertStringContainsString('business_name: businessName', $sentryContent);
        $this->assertStringContainsString('search_term: searchTerm', $sentryContent);
        $this->assertStringContainsString('results_count: resultsCount', $sentryContent);
        $this->assertStringContainsString('progress_percentage: (step / 4) * 100', $sentryContent);
    }
} 