<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class CriticalExperienceTrackingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function business_listing_page_still_works_with_critical_tracking()
    {
        // Create some businesses
        Business::factory()->count(3)->create(['status' => 'approved']);
        
        $response = $this->get('/businesses');
        
        $response->assertStatus(200);
        $response->assertViewIs('businesses.index');
        $response->assertViewHas('businesses');
    }

    #[Test]
    public function business_detail_page_still_works_with_critical_tracking()
    {
        $business = Business::factory()->create([
            'status' => 'approved',
            'business_name' => 'Test Business',
            'business_slug' => 'test-business'
        ]);
        
        $response = $this->get("/business/{$business->business_slug}");
        
        $response->assertStatus(200);
        $response->assertViewIs('businesses.show');
        $response->assertViewHas('business');
    }

    #[Test]
    public function onboarding_flow_still_works_with_critical_tracking()
    {
        // Test step 1
        $response = $this->get('/onboard/step/1');
        $response->assertStatus(200);
        
        // Submit step 1
        $response = $this->post('/onboard/step/1', [
            'business_name' => 'New Business',
            'description' => 'A great new business',
            'tagline' => 'We are the best',
            'industry' => 'Technology',
            'business_type' => 'LLC',
            'founded_date' => '2023-01-01',
            'registration_number' => 'REG123456'
        ]);
        
        $response->assertRedirect('/onboard/step/2');
        $response->assertSessionHas('onboarding_step_1');
    }

    #[Test]
    public function admin_actions_still_work_with_critical_tracking()
    {
        $admin = User::factory()->admin()->create();
        $business = Business::factory()->create(['status' => 'pending']);
        
        // Test approve action
        $response = $this->actingAs($admin)
            ->patch("/admin/businesses/{$business->business_slug}/approve");
        
        $response->assertRedirect();
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'approved'
        ]);
    }

    #[Test]
    public function critical_tracking_does_not_interfere_with_validation()
    {
        // Test that validation still works properly
        $response = $this->post('/onboard/step/1', [
            'business_name' => '', // Required field
        ]);
        
        $response->assertSessionHasErrors(['business_name']);
    }

    #[Test]
    public function session_data_is_maintained_across_onboarding_steps()
    {
        // Submit step 1
        $this->post('/onboard/step/1', [
            'business_name' => 'Test Business',
            'description' => 'Description',
            'tagline' => 'Tagline',
            'industry' => 'Technology',
            'business_type' => 'LLC',
            'founded_date' => '2023-01-01',
            'registration_number' => 'REG123'
        ]);
        
        // Check session has tracking data
        $this->assertTrue(session()->has('onboarding_step_1'));
        
        // Submit step 2
        $this->post('/onboard/step/2', [
            'primary_email' => 'test@example.com',
            'phone_number' => '123-456-7890',
            'website_url' => 'https://example.com',
        ]);
        
        // Check both steps data is maintained
        $this->assertTrue(session()->has('onboarding_step_1'));
        $this->assertTrue(session()->has('onboarding_step_2'));
    }
}
