<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ControllerSentryMigrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function business_controller_tracks_listing_page_with_sentry()
    {
        // Create test businesses
        Business::factory()->count(3)->create(['status' => 'approved']);
        Business::factory()->count(2)->create(['status' => 'approved', 'is_featured' => true]);
        
        $response = $this->get('/businesses');
        
        $response->assertStatus(200);
        $response->assertViewIs('businesses.index');
        $response->assertViewHas('businesses');
        $response->assertViewHas('featuredBusinesses');
    }

    #[Test]
    public function business_controller_tracks_search_operations()
    {
        // Create searchable businesses
        Business::factory()->create([
            'status' => 'approved',
            'business_name' => 'Tech Solutions',
            'description' => 'Technology services'
        ]);
        
        Business::factory()->create([
            'status' => 'approved',
            'business_name' => 'Food Market',
            'description' => 'Fresh food delivery'
        ]);
        
        $response = $this->get('/businesses?search=Tech');
        
        $response->assertStatus(200);
        $response->assertSee('Tech Solutions');
        $response->assertDontSee('Food Market');
    }

    #[Test]
    public function business_controller_tracks_individual_business_view()
    {
        $business = Business::factory()->create([
            'status' => 'approved',
            'business_name' => 'Test Business',
            'business_slug' => 'test-business'
        ]);
        
        // The route uses route model binding, so we need the slug or id
        $response = $this->get('/business/' . $business->business_slug);
        
        $response->assertStatus(200);
        $response->assertViewIs('businesses.show');
        $response->assertViewHas('business');
        $response->assertSee('Test Business');
    }

    #[Test]
    public function admin_dashboard_tracks_operations_with_sentry()
    {
        // Create admin user
        $admin = \App\Models\User::factory()->create(['is_admin' => true]);
        
        // Create test businesses
        Business::factory()->count(2)->create(['status' => 'pending']);
        Business::factory()->count(3)->create(['status' => 'approved']);
        
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHas('pendingBusinesses');
        $response->assertViewHas('statistics');
    }

    #[Test]
    public function onboarding_controller_tracks_multi_step_flow()
    {
        // Test step 1
        $response = $this->get('/onboard/step/1');
        $response->assertStatus(200);
        
        // Test step 1 submission
        $step1Data = [
            'business_name' => 'New Business',
            'industry' => 'Technology',
            'business_type' => 'Startup',
            'description' => 'Test description',
            'tagline' => 'Test tagline',
        ];
        
        $response = $this->post('/onboard/step/1', $step1Data);
        $response->assertRedirect('/onboard/step/2');
        $response->assertSessionHas('onboarding_step_1');
    }

    #[Test]
    public function controllers_handle_errors_gracefully()
    {
        // Test 404 for non-existent business
        $response = $this->get('/businesses/non-existent-slug');
        $response->assertStatus(404);
        
        // Test unauthorized admin access
        $regularUser = \App\Models\User::factory()->create(['is_admin' => false]);
        $response = $this->actingAs($regularUser)->get('/admin/dashboard');
        $response->assertStatus(403);
    }
}
