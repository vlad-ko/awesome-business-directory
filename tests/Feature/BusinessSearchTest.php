<?php

namespace Tests\Feature;

use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BusinessSearchTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_search_businesses_by_name()
    {
        // Arrange - Create some test businesses
        $pizzaPlace = Business::factory()->create([
            'business_name' => 'Tony\'s Pizza Palace',
            'description' => 'Best pizza in town',
            'industry' => 'restaurant',
            'status' => 'approved'
        ]);

        $techCompany = Business::factory()->create([
            'business_name' => 'Tech Solutions Inc',
            'description' => 'Software development company',
            'industry' => 'technology',
            'status' => 'approved'
        ]);

        $coffeeShop = Business::factory()->create([
            'business_name' => 'Coffee Corner',
            'description' => 'Fresh coffee and pastries',
            'industry' => 'restaurant',
            'status' => 'approved'
        ]);

        // Act - Search for "pizza"
        $response = $this->get(route('businesses.index', ['search' => 'pizza']));

        // Assert
        $response->assertStatus(200)
            ->assertSee('Tony\'s Pizza Palace')
            ->assertDontSee('Tech Solutions Inc')
            ->assertDontSee('Coffee Corner');
    }

    #[Test]
    public function it_can_search_businesses_by_description()
    {
        // Arrange
        $pizzaPlace = Business::factory()->create([
            'business_name' => 'Tony\'s Place',
            'description' => 'Amazing pizza and Italian food',
            'industry' => 'restaurant',
            'status' => 'approved'
        ]);

        $techCompany = Business::factory()->create([
            'business_name' => 'Code Masters',
            'description' => 'Software development company',
            'industry' => 'technology',
            'status' => 'approved'
        ]);

        // Act - Search for "software"
        $response = $this->get(route('businesses.index', ['search' => 'software']));

        // Assert
        $response->assertStatus(200)
            ->assertSee('Code Masters')
            ->assertDontSee('Tony\'s Place');
    }

    #[Test]
    public function it_returns_all_businesses_when_no_search_term()
    {
        // Arrange
        $business1 = Business::factory()->create([
            'business_name' => 'Business One',
            'status' => 'approved'
        ]);

        $business2 = Business::factory()->create([
            'business_name' => 'Business Two',
            'status' => 'approved'
        ]);

        // Act
        $response = $this->get(route('businesses.index'));

        // Assert
        $response->assertStatus(200)
            ->assertSee('Business One')
            ->assertSee('Business Two');
    }

    #[Test]
    public function it_shows_no_results_message_when_search_has_no_matches()
    {
        // Arrange
        Business::factory()->create([
            'business_name' => 'Pizza Place',
            'description' => 'Best pizza in town',
            'status' => 'approved'
        ]);

        // Act - Search for something that doesn't exist
        $response = $this->get(route('businesses.index', ['search' => 'nonexistent']));

        // Assert
        $response->assertStatus(200)
            ->assertSee('NO RESULTS FOUND')
            ->assertDontSee('Pizza Place');
    }

    #[Test]
    public function it_only_searches_approved_businesses()
    {
        // Arrange
        $approvedBusiness = Business::factory()->create([
            'business_name' => 'Approved Pizza',
            'description' => 'Approved pizza place',
            'status' => 'approved'
        ]);

        $pendingBusiness = Business::factory()->create([
            'business_name' => 'Pending Pizza',
            'description' => 'Pending pizza place',
            'status' => 'pending'
        ]);

        $rejectedBusiness = Business::factory()->create([
            'business_name' => 'Rejected Pizza',
            'description' => 'Rejected pizza place',
            'status' => 'rejected'
        ]);

        // Act - Search for "pizza"
        $response = $this->get(route('businesses.index', ['search' => 'pizza']));

        // Assert - Only approved business should show
        $response->assertStatus(200)
            ->assertSee('Approved Pizza')
            ->assertDontSee('Pending Pizza')
            ->assertDontSee('Rejected Pizza');
    }

    #[Test]
    public function search_is_case_insensitive()
    {
        // Arrange
        Business::factory()->create([
            'business_name' => 'PIZZA Palace',
            'description' => 'Great ITALIAN food',
            'status' => 'approved'
        ]);

        // Act - Search with different cases
        $response1 = $this->get(route('businesses.index', ['search' => 'pizza']));
        $response2 = $this->get(route('businesses.index', ['search' => 'PIZZA']));
        $response3 = $this->get(route('businesses.index', ['search' => 'italian']));

        // Assert
        $response1->assertSee('PIZZA Palace');
        $response2->assertSee('PIZZA Palace');
        $response3->assertSee('PIZZA Palace');
    }

    #[Test]
    public function search_handles_partial_matches()
    {
        // Arrange
        Business::factory()->create([
            'business_name' => 'Tony\'s Restaurant',
            'description' => 'Family owned restaurant',
            'status' => 'approved'
        ]);

        // Act - Search with partial terms
        $response1 = $this->get(route('businesses.index', ['search' => 'Tony']));
        $response2 = $this->get(route('businesses.index', ['search' => 'family']));

        // Assert
        $response1->assertSee('Tony\'s Restaurant');
        $response2->assertSee('Tony\'s Restaurant');
    }
} 