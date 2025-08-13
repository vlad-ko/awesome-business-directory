<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\BusinessLogger;
use App\Models\Business;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;

class BusinessLoggerTest extends TestCase
{
    #[Test]
    public function it_logs_onboarding_started_event()
    {
        $request = Request::create('/onboard', 'GET');
        
        // Should not throw exception
        BusinessLogger::onboardingStarted($request);
        
        $this->assertTrue(true); // If we get here, it worked
    }

    #[Test]
    public function it_logs_business_created_event()
    {
        $business = Business::factory()->make([
            'id' => 1,
            'business_name' => 'Test Business',
            'industry' => 'Technology',
        ]);
        
        // Should not throw exception
        BusinessLogger::businessCreated($business, 100.5);
        
        $this->assertTrue(true); // If we get here, it worked
    }

    #[Test]
    public function it_logs_validation_errors()
    {
        $request = Request::create('/onboard', 'POST', [
            'business_name' => '',
            'email' => 'invalid',
        ]);
        
        $errors = [
            'business_name' => ['The business name field is required.'],
            'email' => ['The email must be a valid email address.'],
        ];
        
        // Should not throw exception
        BusinessLogger::validationFailed($errors, $request);
        
        $this->assertTrue(true); // If we get here, it worked
    }

    #[Test]
    public function it_tracks_multi_step_onboarding_progress()
    {
        // Test step started
        BusinessLogger::multiStepStepStarted(1, ['source' => 'test']);
        
        // Test step completed
        BusinessLogger::multiStepStepCompleted(1, ['business_name' => 'Test'], 5000);
        
        // Test validation error
        BusinessLogger::multiStepValidationError(1, ['email' => ['Required']], ['email' => '']);
        
        $this->assertTrue(true); // If we get here, all logging worked
    }

    #[Test]
    public function it_handles_application_errors()
    {
        $exception = new \Exception('Test error');
        
        // Should not throw exception when logging
        BusinessLogger::applicationError($exception, 'test_context', ['extra' => 'data']);
        
        $this->assertTrue(true); // If we get here, it worked
    }
}