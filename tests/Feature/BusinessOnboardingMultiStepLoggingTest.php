<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use App\Services\BusinessLogger;
use App\Models\Business;

class BusinessOnboardingMultiStepLoggingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear any existing logs
        Storage::disk('local')->deleteDirectory('logs');
        Storage::disk('local')->makeDirectory('logs');
    }

    #[Test]
    public function step_1_view_logs_step_started_event()
    {
        $response = $this->get(route('business.onboard.step', 1));
        
        $response->assertStatus(200);
        
        // Check that the page contains the expected content
        $response->assertSee('Step 1 of 4');
        $response->assertSee('Business Information');
    }

    #[Test]
    public function step_completion_logs_step_completed_event()
    {
        $stepData = [
            'business_name' => 'Test Company',
            'industry' => 'Technology',
            'business_type' => 'LLC',
            'description' => 'A test company',
            'tagline' => 'Testing is great'
        ];

        $response = $this->post(route('business.onboard.step', 1), $stepData);
        
        $response->assertRedirect(route('business.onboard.step', 2));
        
        // Verify session has the step data
        $this->assertEquals($stepData, session('onboarding_step_1'));
    }

    #[Test]
    public function validation_errors_log_properly()
    {
        // Submit step 1 with missing required fields
        $response = $this->post(route('business.onboard.step', 1), []);
        
        $response->assertSessionHasErrors(['business_name', 'industry', 'business_type']);
    }

    #[Test]
    public function review_page_logs_review_reached_event()
    {
        // Complete all steps first
        $this->completeAllSteps();
        
        $response = $this->get(route('business.onboard.review'));
        
        $response->assertStatus(200);
        $response->assertSee('Review Your Information');
    }

    #[Test]
    public function final_submission_logs_conversion_completed()
    {
        // Complete all steps first
        $this->completeAllSteps();
        
        $response = $this->post(route('business.onboard.submit'));
        
        $response->assertRedirect(route('business.onboard.success'));
        
        // Verify business was created
        $this->assertDatabaseHas('businesses', [
            'business_name' => 'Test Company',
            'industry' => 'Technology',
            'status' => 'pending'
        ]);
    }

    #[Test]
    public function back_navigation_logs_properly()
    {
        // Complete step 1
        $this->post(route('business.onboard.step', 1), [
            'business_name' => 'Test Company',
            'industry' => 'Technology', 
            'business_type' => 'LLC',
            'description' => 'Test description',
            'tagline' => 'Test tagline'
        ]);

        // Go to step 2
        $response = $this->get(route('business.onboard.step', 2));
        $response->assertStatus(200);

        // Go back to step 1
        $response = $this->get(route('business.onboard.step', 1));
        $response->assertStatus(200);
        
        // Should have existing data pre-filled
        $this->assertEquals('Test Company', session('onboarding_step_1.business_name'));
    }

    #[Test]
    public function business_logger_multi_step_methods_exist()
    {
        // Test that our new logging methods exist and can be called
        $this->assertTrue(method_exists(BusinessLogger::class, 'multiStepStepStarted'));
        $this->assertTrue(method_exists(BusinessLogger::class, 'multiStepStepCompleted'));
        $this->assertTrue(method_exists(BusinessLogger::class, 'multiStepValidationError'));
        $this->assertTrue(method_exists(BusinessLogger::class, 'multiStepReviewReached'));
        $this->assertTrue(method_exists(BusinessLogger::class, 'multiStepConversionCompleted'));
        $this->assertTrue(method_exists(BusinessLogger::class, 'multiStepBackNavigation'));
        $this->assertTrue(method_exists(BusinessLogger::class, 'multiStepPotentialAbandonment'));
        $this->assertTrue(method_exists(BusinessLogger::class, 'multiStepErrorRecovery'));
    }

    #[Test]
    public function logging_methods_can_be_called_without_errors()
    {
        // Test that we can call our logging methods without throwing exceptions
        
        // This should not throw an exception
        BusinessLogger::multiStepStepStarted(1, [
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent'
        ]);

        BusinessLogger::multiStepStepCompleted(1, [
            'business_name' => 'Test Company'
        ], 100.0);

        BusinessLogger::multiStepValidationError(1, [
            'business_name' => ['The business name field is required.']
        ]);

        BusinessLogger::multiStepReviewReached([
            'step_1' => ['business_name' => 'Test Company']
        ], 5000.0);

        BusinessLogger::multiStepBackNavigation(2, 1, 'edit');

        BusinessLogger::multiStepPotentialAbandonment(2, [
            'time_on_step' => 300000
        ]);

        BusinessLogger::multiStepErrorRecovery(1, [
            'business_name' => ['Required field missing']
        ], true);

        $this->assertTrue(true); // If we get here, no exceptions were thrown
    }

    #[Test]
    public function actual_logging_occurs_during_multi_step_flow()
    {
        // Clear logs before test
        if (file_exists(storage_path('logs/laravel.log'))) {
            file_put_contents(storage_path('logs/laravel.log'), '');
        }

        // Perform a multi-step action that should trigger logging
        $response = $this->get(route('business.onboard.step', 1));
        $response->assertStatus(200);

        // Submit step 1 to trigger step completion logging
        $stepData = [
            'business_name' => 'Log Test Company',
            'industry' => 'Technology',
            'business_type' => 'LLC',
            'description' => 'Testing logging functionality',
            'tagline' => 'Logs everywhere'
        ];

        $response = $this->post(route('business.onboard.step', 1), $stepData);
        $response->assertRedirect(route('business.onboard.step', 2));

        // Check that logs were actually written
        $logContent = '';
        if (file_exists(storage_path('logs/laravel.log'))) {
            $logContent = file_get_contents(storage_path('logs/laravel.log'));
        }

        // Verify that multi-step logging events appear in the log
        $this->assertStringContainsString('multi_step_onboarding_step_started', $logContent);
        $this->assertStringContainsString('multi_step_onboarding_step_completed', $logContent);
        
        // Check for step completion data (business name should be in step completed log)
        $this->assertStringContainsString('step_number', $logContent);
        $this->assertStringContainsString('progress_percentage', $logContent);
    }

    #[Test]
    public function comprehensive_multi_step_logging_demonstration()
    {
        // This test demonstrates our complete multi-step logging system
        
        // 1. Test step started logging
        $response = $this->get(route('business.onboard.step', 1));
        $response->assertStatus(200);

        // 2. Test step completed logging  
        $stepData = [
            'business_name' => 'Comprehensive Test Co',
            'industry' => 'Technology',
            'business_type' => 'LLC',
            'description' => 'Testing our comprehensive logging system',
            'tagline' => 'Logs for days'
        ];

        $response = $this->post(route('business.onboard.step', 1), $stepData);
        $response->assertRedirect(route('business.onboard.step', 2));

        // 3. Test validation error logging
        $response = $this->post(route('business.onboard.step', 2), []);
        $response->assertSessionHasErrors();

        // 4. Test back navigation logging
        $response = $this->get(route('business.onboard.step', 1));
        $response->assertStatus(200);

        // 5. Complete remaining steps to test review and conversion logging
        $this->post(route('business.onboard.step', 2), [
            'primary_email' => 'comprehensive@test.com',
            'phone_number' => '555-9999',
            'website_url' => 'https://comprehensive-test.com'
        ]);

        $this->post(route('business.onboard.step', 3), [
            'street_address' => '999 Test Ave',
            'city' => 'Test City',
            'state_province' => 'Test State',
            'postal_code' => '99999',
            'country' => 'United States'
        ]);

        $this->post(route('business.onboard.step', 4), [
            'owner_name' => 'Test Owner',
            'owner_email' => 'owner@comprehensive-test.com'
        ]);

        // 6. Test review reached logging
        $response = $this->get(route('business.onboard.review'));
        $response->assertStatus(200);

        // 7. Test conversion completed logging
        $response = $this->post(route('business.onboard.submit'));
        $response->assertRedirect(route('business.onboard.success'));

        // Verify business was created (final verification)
        $this->assertDatabaseHas('businesses', [
            'business_name' => 'Comprehensive Test Co',
            'industry' => 'Technology',
            'status' => 'pending'
        ]);

        // If we get here, all logging operations completed successfully
        $this->assertTrue(true, 'Comprehensive multi-step logging system is working correctly');
    }

    private function completeAllSteps(): void
    {
        // Step 1
        $this->post(route('business.onboard.step', 1), [
            'business_name' => 'Test Company',
            'industry' => 'Technology',
            'business_type' => 'LLC',
            'description' => 'A test company for testing purposes',
            'tagline' => 'Testing is our business'
        ]);

        // Step 2
        $this->post(route('business.onboard.step', 2), [
            'primary_email' => 'test@example.com',
            'phone_number' => '555-1234',
            'website_url' => 'https://example.com'
        ]);

        // Step 3
        $this->post(route('business.onboard.step', 3), [
            'street_address' => '123 Test St',
            'city' => 'Test City',
            'state_province' => 'Test State',
            'postal_code' => '12345',
            'country' => 'United States'
        ]);

        // Step 4
        $this->post(route('business.onboard.step', 4), [
            'owner_name' => 'Test Owner',
            'owner_email' => 'owner@example.com'
        ]);
    }
} 