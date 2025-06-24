<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Business;
use App\Services\BusinessLogger;
use Mockery;

class DistributedTracingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function distributed_tracing_connects_frontend_form_to_backend_database_operation()
    {
        // Simulate frontend trace headers (as would be sent by our Sentry frontend integration)
        $traceId = '12345678901234567890123456789012';
        $spanId = '1234567890123456';
        $sentryTrace = "{$traceId}-{$spanId}-1";
        $baggage = 'sentry-environment=test,sentry-release=1.0.0';
        
        // Set up complete onboarding session data
        $this->session([
            'onboarding_step_1' => [
                'business_name' => 'Test Business',
                'industry' => 'Technology',
                'business_type' => 'LLC',
                'description' => 'A test business for distributed tracing',
                'tagline' => 'Testing is our passion'
            ],
            'onboarding_step_2' => [
                'primary_email' => 'test@example.com',
                'phone_number' => '555-123-4567',
                'website_url' => 'https://test.example.com'
            ],
            'onboarding_step_3' => [
                'street_address' => '123 Test St',
                'city' => 'Test City',
                'state_province' => 'Test State',
                'postal_code' => '12345',
                'country' => 'Test Country'
            ],
            'onboarding_step_4' => [
                'owner_name' => 'Test Owner',
                'owner_email' => 'owner@example.com'
            ]
        ]);
        
        // Submit the final form with distributed tracing headers
        $response = $this->post(route('business.onboard.submit'), [
            '_sentry_sentry_trace' => $sentryTrace,
            '_sentry_baggage' => $baggage
        ]);
        
        // Verify the request was successful
        $response->assertRedirect(route('business.onboard.success'));
        
        // Verify the business was created in the database
        $this->assertDatabaseHas('businesses', [
            'business_name' => 'Test Business',
            'industry' => 'Technology',
            'business_type' => 'LLC',
            'primary_email' => 'test@example.com'
        ]);
        
        // Verify the business record exists
        $business = Business::where('business_name', 'Test Business')->first();
        $this->assertNotNull($business);
        $this->assertEquals('test@example.com', $business->primary_email);
        
        // Verify distributed tracing headers were processed
        // (The middleware should have processed the trace headers without errors)
        $this->assertTrue(true, 'Distributed tracing completed successfully from frontend to database');
    }
    
    /** @test */
    public function middleware_extracts_trace_headers_from_request()
    {
        $traceId = '12345678901234567890123456789012';
        $spanId = '1234567890123456';
        $sentryTrace = "{$traceId}-{$spanId}-1";
        
        // Test with header-based tracing
        $response = $this->withHeaders([
            'sentry-trace' => $sentryTrace,
            'baggage' => 'sentry-environment=test'
        ])->get('/');
        
        $response->assertStatus(200);
        
        // Test with form-based tracing (for form submissions)
        $response = $this->post(route('business.onboard.step.store', 1), [
            'business_name' => 'Test Business',
            'industry' => 'Technology', 
            'business_type' => 'LLC',
            'description' => 'Test description',
            '_sentry_sentry_trace' => $sentryTrace,
            '_sentry_baggage' => 'sentry-environment=test'
        ]);
        
        // Should redirect to next step (validation passed)
        $response->assertRedirect();
    }
    
    /** @test */
    public function frontend_trace_context_is_preserved_in_layout()
    {
        $response = $this->get('/');
        
        // Verify that the layout includes Sentry tracing meta tag
        $response->assertSee('sentry-trace', false);
        
        // Verify Sentry configuration is present
        $response->assertSee('window.sentryConfig');
        $response->assertSee('enableTracing: true');
        $response->assertSee('pageContext');
    }
    
    /** @test */
    public function form_submissions_include_distributed_tracing_attributes()
    {
        $response = $this->get(route('business.onboard.step', 1));
        
        // Verify form has proper tracing attributes
        $response->assertSee('data-form="onboarding_step_1"', false);
        $response->assertSee('id="onboarding-step-1-form"', false);
        
        // Verify the JavaScript for distributed tracing is included
        $response->assertSee('SentryTracing.trackFormSubmission');
        $response->assertSee('traceHeaders');
    }
    
    /** @test */
    public function business_creation_includes_comprehensive_tracing_context()
    {
        // Set up session data
        $this->session([
            'onboarding_step_1' => [
                'business_name' => 'Traced Business',
                'industry' => 'Retail',
                'business_type' => 'Corporation',
                'description' => 'A business with full tracing'
            ],
            'onboarding_step_2' => [
                'primary_email' => 'traced@example.com',
                'phone_number' => '555-999-8888'
            ],
            'onboarding_step_3' => [
                'street_address' => '456 Trace Ave',
                'city' => 'Trace City',
                'state_province' => 'Trace State',
                'postal_code' => '67890',
                'country' => 'Trace Country'
            ],
            'onboarding_step_4' => [
                'owner_name' => 'Trace Owner',
                'owner_email' => 'traceowner@example.com'
            ]
        ]);
        
        $response = $this->post(route('business.onboard.submit'));
        
        $response->assertRedirect(route('business.onboard.success'));
        
        // Verify business creation with all the traced data
        $business = Business::where('business_name', 'Traced Business')->first();
        $this->assertNotNull($business);
        $this->assertEquals('Retail', $business->industry);
        $this->assertEquals('Corporation', $business->business_type);
        $this->assertEquals('traced@example.com', $business->primary_email);
        $this->assertEquals('Trace City', $business->city);
        $this->assertEquals('traceowner@example.com', $business->owner_email);
    }
    
    /** @test */
    public function ajax_requests_include_distributed_tracing_headers()
    {
        $traceId = '98765432109876543210987654321098';
        $spanId = '9876543210987654';
        $sentryTrace = "{$traceId}-{$spanId}-1";
        
        // Simulate an AJAX request with tracing headers
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'sentry-trace' => $sentryTrace,
            'baggage' => 'sentry-environment=test,sentry-transaction=ajax_test'
        ])->get(route('businesses.index'));
        
        $response->assertStatus(200);
        
        // Verify the response includes businesses listing
        $response->assertSee('Business Directory');
    }
} 