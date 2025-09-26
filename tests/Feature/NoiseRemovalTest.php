<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class NoiseRemovalTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function business_listing_works_correctly_after_noise_removal()
    {
        // Test actual production behavior
        $approvedBusinesses = Business::factory()->count(3)->create(['status' => 'approved']);
        $pendingBusiness = Business::factory()->create(['status' => 'pending']);
        
        $response = $this->get('/businesses');
        
        // Should show approved businesses
        $response->assertStatus(200);
        $response->assertViewHas('businesses');
        $businesses = $response->viewData('businesses');
        $this->assertCount(3, $businesses);
        
        // Should not show pending businesses
        $response->assertDontSee($pendingBusiness->business_name);
    }

    #[Test]
    public function business_search_functionality_still_works()
    {
        Business::factory()->create(['status' => 'approved', 'business_name' => 'Coffee Shop']);
        Business::factory()->create(['status' => 'approved', 'business_name' => 'Pizza Place']);
        Business::factory()->create(['status' => 'approved', 'business_name' => 'Coffee Roasters']);
        
        $response = $this->get('/businesses?search=coffee');
        
        $response->assertStatus(200);
        $businesses = $response->viewData('businesses');
        $this->assertCount(2, $businesses);
        $response->assertSee('Coffee Shop');
        $response->assertSee('Coffee Roasters');
        $response->assertDontSee('Pizza Place');
    }

    #[Test]
    public function business_detail_page_displays_correctly()
    {
        $business = Business::factory()->create([
            'status' => 'approved',
            'business_name' => 'Test Business',
            'description' => 'Test Description'
        ]);
        
        $response = $this->get("/business/{$business->business_slug}");
        
        $response->assertStatus(200);
        $response->assertViewHas('business');
        $response->assertSee($business->business_name);
        $response->assertSee($business->description);
    }

    #[Test]
    public function onboarding_flow_creates_business_successfully()
    {
        // Test the entire critical path
        $response = $this->get('/onboard/step/1');
        $response->assertStatus(200);
        
        // Step 1: Basic Info
        $response = $this->post('/onboard/step/1', [
            'business_name' => 'New Business',
            'description' => 'A great new business',
            'tagline' => 'We are awesome',
            'industry' => 'Technology',
            'business_type' => 'LLC',
            'founded_date' => '2023-01-01',
            'registration_number' => 'REG123'
        ]);
        $response->assertRedirect('/onboard/step/2');
        $response->assertSessionHas('onboarding_step_1');
        
        // Step 2: Contact Info
        $response = $this->post('/onboard/step/2', [
            'primary_email' => 'business@example.com',
            'phone_number' => '123-456-7890',
            'secondary_email' => 'support@example.com',
            'fax_number' => '123-456-7891',
            'toll_free_number' => '1-800-123-4567'
        ]);
        $response->assertRedirect('/onboard/step/3');
        
        // Step 3: Location
        $response = $this->post('/onboard/step/3', [
            'street_address' => '123 Main St',
            'address_line_2' => 'Suite 100',
            'city' => 'San Francisco',
            'state_province' => 'CA',
            'postal_code' => '94105',
            'country' => 'USA',
            'latitude' => '37.7749',
            'longitude' => '-122.4194'
        ]);
        $response->assertRedirect('/onboard/step/4');
        
        // Step 4: Owner Info
        $response = $this->post('/onboard/step/4', [
            'owner_name' => 'John Doe',
            'owner_email' => 'john@example.com',
            'owner_phone' => '123-456-7892',
            'owner_title' => 'CEO',
            'verification_consent' => true
        ]);
        $response->assertRedirect('/onboard/review');
        
        // Submit final form
        $response = $this->post('/onboard/submit');
        $response->assertRedirect('/onboard/success');
        
        // Verify business was created
        $this->assertDatabaseHas('businesses', [
            'business_name' => 'New Business',
            'primary_email' => 'business@example.com',
            'status' => 'pending'
        ]);
    }

    #[Test]
    public function admin_can_approve_businesses()
    {
        $admin = User::factory()->admin()->create();
        $business = Business::factory()->create(['status' => 'pending']);
        
        $response = $this->actingAs($admin)
            ->patch("/admin/businesses/{$business->business_slug}/approve");
        
        $response->assertRedirect();
        
        // Verify business was approved
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'approved'
        ]);
    }

    #[Test]
    public function validation_errors_are_shown_correctly()
    {
        $response = $this->post('/onboard/step/1', [
            // Missing required fields
        ]);
        
        $response->assertSessionHasErrors([
            'business_name',
            'description',
            'industry'
        ]);
        
        // User should stay on the same page
        $response->assertStatus(302);
    }

    #[Test]
    public function unauthenticated_users_cannot_access_admin_dashboard()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/admin/login');
    }

    #[Test]
    public function critical_paths_remain_functional()
    {
        // Test Discovery Path
        $businesses = Business::factory()->count(5)->create(['status' => 'approved']);
        $response = $this->get('/businesses');
        $response->assertStatus(200);
        
        // Test Business View
        $business = $businesses->first();
        $response = $this->get("/business/{$business->business_slug}");
        $response->assertStatus(200);
        
        // Test Onboarding Start
        $response = $this->get('/onboard');
        $response->assertStatus(302); // Redirects to step 1
        
        // Test Admin Login
        $admin = User::factory()->admin()->create();
        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password'
        ]);
        $response->assertRedirect('/admin/dashboard');
    }
}
