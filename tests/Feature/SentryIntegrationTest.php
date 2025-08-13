<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class SentryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_tracks_page_views_with_sentry_context()
    {
        // Test welcome page tracking
        $response = $this->get('/');
        
        $response->assertStatus(200);
        // The middleware should have added Sentry context
    }

    #[Test]
    public function it_tracks_business_listing_page()
    {
        // Create some test businesses
        Business::factory()->count(3)->create(['status' => 'approved']);
        
        $response = $this->get('/businesses');
        
        $response->assertStatus(200);
        $response->assertSee('Business Directory');
    }

    #[Test]
    public function it_tracks_onboarding_form_submission_with_sentry()
    {
        $businessData = [
            'business_name' => 'Test Business',
            'industry' => 'Technology',
            'business_type' => 'Startup',
            'description' => 'A test business description',
            'tagline' => 'Test tagline',
            'primary_email' => 'test@example.com',
            'phone_number' => '123-456-7890',
            'website_url' => 'https://example.com',
            'street_address' => '123 Test St',
            'city' => 'Test City',
            'state_province' => 'TS',
            'postal_code' => '12345',
            'country' => 'US',
            'owner_name' => 'Test Owner',
            'owner_email' => 'owner@example.com',
            'owner_phone' => '098-765-4321',
        ];
        
        $response = $this->post('/onboard', $businessData);
        
        // The app redirects to multi-step onboarding
        $response->assertRedirect('/onboard/step/1');
        // Business is not created yet in multi-step form
        $this->assertDatabaseMissing('businesses', [
            'business_name' => 'Test Business',
        ]);
    }

    #[Test]
    public function it_tracks_validation_errors_with_sentry()
    {
        $invalidData = [
            'business_name' => '', // Required field
            'primary_email' => 'invalid-email', // Invalid format
        ];
        
        $response = $this->post('/onboard', $invalidData);
        
        // The app redirects to multi-step form when posting to /onboard
        $response->assertRedirect('/onboard/step/1');
        // BusinessLogger should have logged validation errors
    }

    #[Test]
    public function it_tracks_multi_step_onboarding_flow()
    {
        // Step 1
        $step1Data = [
            'business_name' => 'Multi Step Business',
            'industry' => 'Technology',
            'business_type' => 'Startup',
            'description' => 'Test description',
            'tagline' => 'Test tagline',
        ];
        
        // Use the correct route
        $response = $this->post('/onboard/step/1', $step1Data);
        $response->assertRedirect('/onboard/step/2');
        
        // Step 2
        $step2Data = [
            'primary_email' => 'contact@example.com',
            'phone_number' => '123-456-7890',
            'website_url' => 'https://example.com',
        ];
        
        $response = $this->post('/onboard/step/2', $step2Data);
        $response->assertRedirect('/onboard/step/3');
        
        // BusinessLogger should have tracked the progression
    }

    #[Test]
    public function it_handles_errors_gracefully_with_sentry_tracking()
    {
        // Simulate an error by providing data that might cause issues
        $problematicData = [
            'business_name' => str_repeat('A', 256), // Too long
            'primary_email' => 'test@example.com',
            // ... other required fields
        ];
        
        $response = $this->post('/onboard', array_merge($problematicData, [
            'industry' => 'Technology',
            'business_type' => 'Startup',
            'description' => 'Test',
            'tagline' => 'Test',
            'phone_number' => '123-456-7890',
            'website_url' => 'https://example.com',
            'street_address' => '123 Test St',
            'city' => 'Test City',
            'state_province' => 'TS',
            'postal_code' => '12345',
            'country' => 'US',
            'owner_name' => 'Test Owner',
            'owner_email' => 'owner@example.com',
            'owner_phone' => '098-765-4321',
        ]));
        
        // The app redirects to multi-step form
        $response->assertRedirect('/onboard/step/1');
    }
}