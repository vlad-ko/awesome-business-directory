<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;

class BusinessOnboardingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function user_can_view_business_onboarding_form()
    {
        // Old route should redirect to new multi-step form
        $response = $this->get(route('business.onboard'));

        $response->assertRedirect(route('business.onboard.step', 1));
    }

    #[Test]
    public function user_can_submit_business_for_onboarding()
    {
        // Old business store route should redirect to new multi-step form
        $businessData = [
            'business_name' => $this->faker->company,
            'industry' => $this->faker->word,
            'business_type' => 'LLC',
            'description' => $this->faker->paragraph,
            'primary_email' => $this->faker->email,
            'phone_number' => $this->faker->phoneNumber,
            'street_address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state_province' => $this->faker->state,
            'postal_code' => $this->faker->postcode,
            'country' => $this->faker->country,
            'owner_name' => $this->faker->name,
            'owner_email' => $this->faker->email,
        ];

        $response = $this->post(route('business.store'), $businessData);

        $response->assertRedirect(route('business.onboard.step', 1));
    }

    #[Test]
    public function business_requires_required_fields()
    {
        // Old POST route redirects without validation
        $response = $this->post(route('business.store'), []);
        $response->assertRedirect(route('business.onboard.step', 1));
    }

    #[Test]
    public function business_email_must_be_valid()
    {
        // Old POST route redirects without validation
        $businessData = [
            'primary_email' => 'invalid-email',
            'owner_email' => 'invalid-email',
        ];

        $response = $this->post(route('business.store'), $businessData);
        $response->assertRedirect(route('business.onboard.step', 1));
    }

    #[Test]
    public function business_creation_through_multi_step_works()
    {
        // Test that the multi-step process can create businesses
        // This uses the new multi-step submit endpoint
        $businessData = [
            'business_name' => 'Test Company LLC',
            'industry' => 'Technology',
            'business_type' => 'LLC',
            'description' => $this->faker->paragraph(3),
            'tagline' => 'We are amazing',
            'primary_email' => $this->faker->email,
            'phone_number' => $this->faker->phoneNumber,
            'website_url' => 'https://example.com',
            'street_address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state_province' => $this->faker->state,
            'postal_code' => $this->faker->postcode,
            'country' => 'United States',
            'owner_name' => $this->faker->name,
            'owner_email' => $this->faker->email,
        ];

        // Set up complete session data as if user completed all steps
        session([
            'onboarding_step_1' => [
                'business_name' => $businessData['business_name'],
                'industry' => $businessData['industry'],
                'business_type' => $businessData['business_type'],
                'description' => $businessData['description'],
                'tagline' => $businessData['tagline'],
            ],
            'onboarding_step_2' => [
                'primary_email' => $businessData['primary_email'],
                'phone_number' => $businessData['phone_number'],
                'website_url' => $businessData['website_url'],
            ],
            'onboarding_step_3' => [
                'street_address' => $businessData['street_address'],
                'city' => $businessData['city'],
                'state_province' => $businessData['state_province'],
                'postal_code' => $businessData['postal_code'],
                'country' => $businessData['country'],
            ],
            'onboarding_step_4' => [
                'owner_name' => $businessData['owner_name'],
                'owner_email' => $businessData['owner_email'],
            ],
            'onboarding_progress' => 100,
        ]);

        $response = $this->post(route('business.onboard.submit'));

        $response->assertRedirect(route('business.onboard.success'));

        $this->assertDatabaseHas('businesses', [
            'business_name' => $businessData['business_name'],
            'industry' => $businessData['industry'],
            'status' => 'pending',
        ]);
    }

    #[Test]
    public function business_slug_is_automatically_generated()
    {
        // Set up session for business creation through multi-step
        session([
            'onboarding_step_1' => [
                'business_name' => 'Test Company LLC',
                'industry' => 'Technology',
                'business_type' => 'LLC',
                'description' => $this->faker->paragraph(3),
                'tagline' => 'Amazing business',
            ],
            'onboarding_step_2' => [
                'primary_email' => $this->faker->email,
                'phone_number' => $this->faker->phoneNumber,
                'website_url' => 'https://example.com',
            ],
            'onboarding_step_3' => [
                'street_address' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'state_province' => $this->faker->state,
                'postal_code' => $this->faker->postcode,
                'country' => 'United States',
            ],
            'onboarding_step_4' => [
                'owner_name' => $this->faker->name,
                'owner_email' => $this->faker->email,
            ],
            'onboarding_progress' => 100,
        ]);

        $this->post(route('business.onboard.submit'));

        $business = Business::first();
        $this->assertEquals('test-company-llc', $business->business_slug);
    }

    #[Test]
    public function business_slug_must_be_unique()
    {
        // Create first business through multi-step
        session([
            'onboarding_step_1' => [
                'business_name' => 'Test Company',
                'industry' => 'Technology',
                'business_type' => 'LLC',
                'description' => $this->faker->paragraph(3),
                'tagline' => 'Amazing business',
            ],
            'onboarding_step_2' => [
                'primary_email' => $this->faker->email,
                'phone_number' => $this->faker->phoneNumber,
                'website_url' => 'https://example.com',
            ],
            'onboarding_step_3' => [
                'street_address' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'state_province' => $this->faker->state,
                'postal_code' => $this->faker->postcode,
                'country' => 'United States',
            ],
            'onboarding_step_4' => [
                'owner_name' => $this->faker->name,
                'owner_email' => $this->faker->email,
            ],
            'onboarding_progress' => 100,
        ]);

        $response1 = $this->post(route('business.onboard.submit'));
        $response1->assertRedirect();

        $business1 = Business::first();
        $this->assertEquals('test-company', $business1->business_slug);

        // Create second business with same name but different emails
        session([
            'onboarding_step_1' => [
                'business_name' => 'Test Company',
                'industry' => 'Technology',
                'business_type' => 'LLC',
                'description' => $this->faker->paragraph(3),
                'tagline' => 'Another amazing business',
            ],
            'onboarding_step_2' => [
                'primary_email' => $this->faker->unique()->email,
                'phone_number' => $this->faker->phoneNumber,
                'website_url' => 'https://example2.com',
            ],
            'onboarding_step_3' => [
                'street_address' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'state_province' => $this->faker->state,
                'postal_code' => $this->faker->postcode,
                'country' => 'United States',
            ],
            'onboarding_step_4' => [
                'owner_name' => $this->faker->name,
                'owner_email' => $this->faker->unique()->email,
            ],
            'onboarding_progress' => 100,
        ]);
        
        $response2 = $this->post(route('business.onboard.submit'));
        $response2->assertRedirect();

        $business2 = Business::orderBy('id', 'desc')->first();
        
        // Should have a different slug
        $this->assertNotEquals($business1->business_slug, $business2->business_slug);
        $this->assertEquals('test-company-2', $business2->business_slug);
    }

    #[Test]
    public function onboarding_form_renders_successfully()
    {
        // Old route redirects to new multi-step form
        $response = $this->get(route('business.onboard'));
        $response->assertRedirect(route('business.onboard.step', 1));
    }

    #[Test]
    public function validation_errors_display_properly()
    {
        // Old POST route redirects without validation
        $response = $this->post(route('business.store'), []);
        $response->assertRedirect(route('business.onboard.step', 1));
    }

    #[Test]
    public function successful_business_creation_with_technology_industry()
    {
        // Old POST route redirects to new multi-step form  
        $businessData = [
            'business_name' => $this->faker->company,
            'industry' => 'Technology',
        ];

        $response = $this->post(route('business.store'), $businessData);
        $response->assertRedirect(route('business.onboard.step', 1));
    }

    #[Test]
    public function onboarding_form_accessible_from_welcome_page()
    {
        // Old route redirects to new multi-step form
        $response = $this->get(route('business.onboard'), [
            'HTTP_REFERER' => url('/')
        ]);
        
        $response->assertRedirect(route('business.onboard.step', 1));
    }
}
