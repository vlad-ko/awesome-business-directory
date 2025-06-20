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
        $response = $this->get(route('business.onboard'));

        $response->assertStatus(200)
            ->assertViewIs('onboarding.create')
            ->assertSee('Get Your Business')
            ->assertSee('your business called')
            ->assertSee('industry are you');
    }

    #[Test]
    public function user_can_submit_business_for_onboarding()
    {
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

        $response->assertRedirect(route('business.onboard'))
            ->assertSessionHas('success', 'Business submitted for review!');

        $this->assertDatabaseHas('businesses', [
            'business_name' => $businessData['business_name'],
            'industry' => $businessData['industry'],
            'status' => 'pending',
        ]);
    }

    #[Test]
    public function business_requires_required_fields()
    {
        $response = $this->post(route('business.store'), []);

        $response->assertSessionHasErrors([
            'business_name',
            'industry',
            'business_type',
            'description',
            'primary_email',
            'phone_number',
            'street_address',
            'city',
            'state_province',
            'postal_code',
            'country',
            'owner_name',
            'owner_email',
        ]);
    }

    #[Test]
    public function business_email_must_be_valid()
    {
        $businessData = [
            'business_name' => $this->faker->company,
            'industry' => $this->faker->word,
            'business_type' => 'LLC',
            'description' => $this->faker->paragraph,
            'primary_email' => 'invalid-email',
            'phone_number' => $this->faker->phoneNumber,
            'street_address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state_province' => $this->faker->state,
            'postal_code' => $this->faker->postcode,
            'country' => $this->faker->country,
            'owner_name' => $this->faker->name,
            'owner_email' => 'invalid-email',
        ];

        $response = $this->post(route('business.store'), $businessData);

        $response->assertSessionHasErrors([
            'primary_email',
            'owner_email',
        ]);
    }

    #[Test]
    public function business_is_created_with_pending_status()
    {
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

        $this->post(route('business.store'), $businessData);

        $business = Business::first();
        $this->assertEquals('pending', $business->status);
        $this->assertFalse($business->is_verified);
        $this->assertNull($business->verified_at);
    }

    #[Test]
    public function business_slug_is_automatically_generated()
    {
        $businessData = [
            'business_name' => 'Test Company LLC',
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

        $this->post(route('business.store'), $businessData);

        $business = Business::first();
        $this->assertEquals('test-company-llc', $business->business_slug);
    }

    #[Test]
    public function business_slug_must_be_unique()
    {
        // Create first business
        $businessData = [
            'business_name' => 'Test Company',
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

        // Create first business
        $response1 = $this->post(route('business.store'), $businessData);
        $response1->assertSessionDoesntHaveErrors();

        $business1 = Business::first();
        $this->assertEquals('test-company', $business1->business_slug);

        // Create second business with same name but different emails
        $businessData['primary_email'] = $this->faker->unique()->email;
        $businessData['owner_email'] = $this->faker->unique()->email;
        
        $response2 = $this->post(route('business.store'), $businessData);
        $response2->assertSessionDoesntHaveErrors();

        $business2 = Business::orderBy('id', 'desc')->first();
        
        // Should have a different slug
        $this->assertNotEquals($business1->business_slug, $business2->business_slug);
        $this->assertEquals('test-company-2', $business2->business_slug);
    }

    #[Test]
    public function onboarding_form_renders_successfully()
    {
        $response = $this->get(route('business.onboard'));
        $response->assertStatus(200);
        $response->assertViewIs('onboarding.create');
    }

    #[Test]
    public function validation_errors_display_properly()
    {
        // Submit empty form to trigger validation errors
        $response = $this->post(route('business.store'), []);
        $response->assertSessionHasErrors([
            'business_name',
            'industry',
            'description',
            'primary_email'
        ]);
    }

    #[Test]
    public function successful_business_creation_with_technology_industry()
    {
        $businessData = [
            'business_name' => $this->faker->company,
            'industry' => 'Technology',
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
        $response->assertRedirect(route('business.onboard'))
                ->assertSessionHas('success');
        
        $this->assertDatabaseHas('businesses', [
            'business_name' => $businessData['business_name'],
            'industry' => 'Technology'
        ]);
    }

    #[Test]
    public function onboarding_form_accessible_from_welcome_page()
    {
        // Simulate coming from welcome page
        $response = $this->get(route('business.onboard'), [
            'HTTP_REFERER' => url('/')
        ]);
        
        $response->assertStatus(200);
        $response->assertViewIs('onboarding.create');
    }
}
