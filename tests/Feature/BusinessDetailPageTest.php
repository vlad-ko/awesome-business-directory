<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class BusinessDetailPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    #[Test]
    public function it_can_display_business_detail_page_using_slug()
    {
        // Arrange: Get an approved business from the database
        $business = Business::where('status', 'approved')->first();
        
        if (!$business) {
            $business = Business::factory()->create(['status' => 'approved']);
        }
        
        // Act: Visit the business detail page using the slug
        $response = $this->get("/business/{$business->business_slug}");
        
        // Assert: Page loads successfully
        $response->assertStatus(200);
        
        // Assert: Business information is displayed
        $response->assertSee($business->business_name);
        $response->assertSee($business->description);
        $response->assertSee($business->primary_email);
        $response->assertSee($business->industry);
        $response->assertSee($business->business_type);
    }

    #[Test]
    public function it_returns_404_for_non_existent_business_slug()
    {
        // Act: Try to visit a non-existent business
        $response = $this->get('/business/non-existent-business');
        
        // Assert: Returns 404
        $response->assertStatus(404);
    }

    #[Test]
    public function it_displays_business_hours_correctly()
    {
        // Arrange: Get an approved business with business hours
        $business = Business::where('status', 'approved')->whereNotNull('business_hours')->first();
        
        if (!$business) {
            $business = Business::factory()->create([
                'status' => 'approved',
                'business_hours' => json_encode([
                    'monday' => ['open' => '9:00 AM', 'close' => '5:00 PM'],
                    'tuesday' => ['open' => '9:00 AM', 'close' => '5:00 PM'],
                ])
            ]);
        }
        
        // Act: Visit the business detail page
        $response = $this->get("/business/{$business->business_slug}");
        
        // Assert: Business hours section is displayed
        $response->assertSee('Business Hours');
        
        // Parse the business hours and check they're displayed correctly
        $hours = json_decode($business->business_hours, true);
        foreach ($hours as $day => $dayHours) {
            $response->assertSee(ucfirst($day));
            if (isset($dayHours['closed']) && $dayHours['closed']) {
                $response->assertSee('Closed');
            } elseif (isset($dayHours['open']) && isset($dayHours['close'])) {
                $response->assertSee($dayHours['open']);
                $response->assertSee($dayHours['close']);
            }
        }
    }

    #[Test]
    public function it_displays_services_offered_when_available()
    {
        // Arrange: Get an approved business with services
        $business = Business::where('status', 'approved')->whereNotNull('services_offered')->first();
        
        if (!$business) {
            $business = Business::factory()->create([
                'status' => 'approved',
                'services_offered' => json_encode(['Web Design', 'SEO Services', 'Digital Marketing'])
            ]);
        }
        
        // Act: Visit the business detail page
        $response = $this->get("/business/{$business->business_slug}");
        
        // Assert: Services section is displayed
        $response->assertSee('Services Offered');
        
        // Check that individual services are displayed
        $services = json_decode($business->services_offered, true);
        foreach ($services as $service) {
            $response->assertSee($service);
        }
    }

    #[Test]
    public function it_displays_contact_information()
    {
        // Arrange: Get an approved business
        $business = Business::where('status', 'approved')->first();
        
        if (!$business) {
            $business = Business::factory()->create(['status' => 'approved']);
        }
        
        // Act: Visit the business detail page
        $response = $this->get("/business/{$business->business_slug}");
        
        // Assert: Contact information is displayed
        $response->assertSee('Contact Information');
        $response->assertSee($business->primary_email);
        
        if ($business->phone_number) {
            $response->assertSee($business->phone_number);
        }
        
        if ($business->website_url) {
            $response->assertSee('Visit Website');
        }
    }

    #[Test]
    public function it_displays_location_information()
    {
        // Arrange: Get an approved business
        $business = Business::where('status', 'approved')->first();
        
        if (!$business) {
            $business = Business::factory()->create(['status' => 'approved']);
        }
        
        // Act: Visit the business detail page
        $response = $this->get("/business/{$business->business_slug}");
        
        // Assert: Location information is displayed
        $response->assertSee('Location');
        $response->assertSee($business->street_address);
        $response->assertSee($business->city);
        $response->assertSee($business->state_province);
        $response->assertSee($business->postal_code);
        $response->assertSee($business->country);
    }

    #[Test]
    public function it_shows_verified_badge_for_verified_businesses()
    {
        // Arrange: Get an approved and verified business
        $business = Business::where('status', 'approved')->where('is_verified', true)->first();
        
        if (!$business) {
            // Create an approved and verified business if none exists
            $business = Business::factory()->create(['status' => 'approved', 'is_verified' => true]);
        }
        
        // Act: Visit the business detail page
        $response = $this->get("/business/{$business->business_slug}");
        
        // Assert: Verified badge is displayed
        $response->assertSee('Verified');
    }

    #[Test]
    public function it_shows_featured_badge_for_featured_businesses()
    {
        // Arrange: Get an approved and featured business
        $business = Business::where('status', 'approved')->where('is_featured', true)->first();
        
        if (!$business) {
            // Create an approved and featured business if none exists
            $business = Business::factory()->create(['status' => 'approved', 'is_featured' => true]);
        }
        
        // Act: Visit the business detail page
        $response = $this->get("/business/{$business->business_slug}");
        
        // Assert: Featured badge is displayed
        $response->assertSee('Featured');
    }

    #[Test]
    public function it_displays_breadcrumb_navigation()
    {
        // Arrange: Get an approved business
        $business = Business::where('status', 'approved')->first();
        
        if (!$business) {
            $business = Business::factory()->create(['status' => 'approved']);
        }
        
        // Act: Visit the business detail page
        $response = $this->get("/business/{$business->business_slug}");
        
        // Assert: Breadcrumb navigation is displayed
        $response->assertSee('Home');
        $response->assertSee('Businesses');
        $response->assertSee($business->business_name);
    }

    #[Test]
    public function it_includes_back_to_directory_link()
    {
        // Arrange: Get an approved business
        $business = Business::where('status', 'approved')->first();
        
        if (!$business) {
            $business = Business::factory()->create(['status' => 'approved']);
        }
        
        // Act: Visit the business detail page
        $response = $this->get("/business/{$business->business_slug}");
        
        // Assert: Back to directory link is present
        $response->assertSee('Back to Directory');
    }
} 