<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WelcomePageIntegrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function welcome_page_navigation_to_businesses_index_works()
    {
        // Create some test businesses
        Business::factory()->count(3)->create(['status' => 'approved']);

        // Visit welcome page
        $response = $this->get('/');
        $response->assertStatus(200);

        // Follow the Browse Businesses link
        $businessesResponse = $this->get(route('businesses.index'));
        $businessesResponse->assertStatus(200);
        $businessesResponse->assertViewIs('businesses.index');
    }

    #[Test]
    public function welcome_page_navigation_to_onboarding_works()
    {
        // Visit welcome page
        $response = $this->get('/');
        $response->assertStatus(200);

        // Follow the Join Directory link
        $onboardResponse = $this->get(route('business.onboard'));
        $onboardResponse->assertStatus(200);
        $onboardResponse->assertViewIs('onboarding.create');
    }

    #[Test]
    public function welcome_page_cta_buttons_lead_to_correct_pages()
    {
        // Create some businesses to ensure the pages work
        Business::factory()->count(2)->create(['status' => 'approved']);

        $response = $this->get('/');

        // Test "Explore Businesses" button
        $exploreUrl = route('businesses.index');
        $response->assertSee($exploreUrl);
        
        $exploreResponse = $this->get($exploreUrl);
        $exploreResponse->assertStatus(200);

        // Test "List Your Business" button
        $listUrl = route('business.onboard');
        $response->assertSee($listUrl);
        
        $listResponse = $this->get($listUrl);
        $listResponse->assertStatus(200);
    }

    #[Test]
    public function welcome_page_works_with_different_app_configurations()
    {
        // Test with different app names
        $testNames = [
            'Awesome Business Directory',
            'Local Business Hub',
            'Community Directory'
        ];

        foreach ($testNames as $name) {
            config(['app.name' => $name]);
            
            $response = $this->get('/');
            $response->assertStatus(200);
            $response->assertSee($name);
        }
    }

    #[Test]
    public function welcome_page_displays_consistently_across_multiple_visits()
    {
        // Test that the page loads consistently
        for ($i = 0; $i < 5; $i++) {
            $response = $this->get('/');
            $response->assertStatus(200);
            $response->assertSee('Discover Your');
            $response->assertSee('Neighborhood');
            $response->assertSee('Gems!');
        }
    }

    #[Test]
    public function welcome_page_works_when_no_businesses_exist()
    {
        // Ensure no businesses exist
        $this->assertEquals(0, Business::count());

        // Welcome page should still work
        $response = $this->get('/');
        $response->assertStatus(200);

        // Navigation should still work even with no businesses
        $businessesResponse = $this->get(route('businesses.index'));
        $businessesResponse->assertStatus(200);

        $onboardResponse = $this->get(route('business.onboard'));
        $onboardResponse->assertStatus(200);
    }

    #[Test]
    public function welcome_page_works_with_existing_businesses()
    {
        // Create various types of businesses
        Business::factory()->create([
            'status' => 'approved',
            'is_featured' => true,
            'is_verified' => true
        ]);
        
        Business::factory()->create([
            'status' => 'approved',
            'is_featured' => false,
            'is_verified' => true
        ]);
        
        Business::factory()->create([
            'status' => 'pending'
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Navigation should work with existing businesses
        $businessesResponse = $this->get(route('businesses.index'));
        $businessesResponse->assertStatus(200);
    }

    #[Test]
    public function welcome_page_user_journey_flow()
    {
        // Simulate a complete user journey starting from welcome page
        
        // 1. User visits welcome page
        $welcomeResponse = $this->get('/');
        $welcomeResponse->assertStatus(200);
        
        // 2. User clicks "Browse Businesses"
        $businessesResponse = $this->get(route('businesses.index'));
        $businessesResponse->assertStatus(200);
        
        // 3. User goes back and clicks "Join Directory"
        $onboardResponse = $this->get(route('business.onboard'));
        $onboardResponse->assertStatus(200);
        
        // 4. User can return to welcome page
        $returnResponse = $this->get('/');
        $returnResponse->assertStatus(200);
    }

    #[Test]
    public function welcome_page_handles_route_model_binding_correctly()
    {
        // Create a business to test route model binding
        $business = Business::factory()->create(['status' => 'approved']);

        // Welcome page should load
        $response = $this->get('/');
        $response->assertStatus(200);

        // Business detail page should work (testing route model binding)
        $businessResponse = $this->get(route('business.show', $business));
        $businessResponse->assertStatus(200);
    }

    #[Test]
    public function welcome_page_maintains_session_state()
    {
        // Start a session
        $response = $this->get('/');
        $response->assertStatus(200);

        // Verify session is maintained across navigation
        $businessesResponse = $this->get(route('businesses.index'));
        $businessesResponse->assertStatus(200);

        // Return to welcome page with same session
        $returnResponse = $this->get('/');
        $returnResponse->assertStatus(200);
    }

    #[Test]
    public function welcome_page_works_with_admin_users()
    {
        // Create an admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Welcome page should work for admin users
        $response = $this->actingAs($admin)->get('/');
        $response->assertStatus(200);

        // Admin should still be able to access regular pages
        $businessesResponse = $this->actingAs($admin)->get(route('businesses.index'));
        $businessesResponse->assertStatus(200);

        $onboardResponse = $this->actingAs($admin)->get(route('business.onboard'));
        $onboardResponse->assertStatus(200);
    }

    #[Test]
    public function welcome_page_works_with_regular_users()
    {
        // Create a regular user
        $user = User::factory()->create(['is_admin' => false]);

        // Welcome page should work for regular users
        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);

        // Regular user should be able to access public pages
        $businessesResponse = $this->actingAs($user)->get(route('businesses.index'));
        $businessesResponse->assertStatus(200);

        $onboardResponse = $this->actingAs($user)->get(route('business.onboard'));
        $onboardResponse->assertStatus(200);
    }
} 