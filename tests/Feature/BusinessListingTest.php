<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;

class BusinessListingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    #[Test]
    public function user_can_view_business_listing_page()
    {
        $response = $this->get(route('businesses.index'));

        $response->assertStatus(200)
            ->assertViewIs('businesses.index')
            ->assertSee('Business Directory')
            ->assertSee('Businesses');
    }

    #[Test]
    public function business_listing_displays_approved_businesses()
    {
        // Create some test businesses with different statuses
        $approvedBusiness1 = Business::create([
            'business_name' => 'Approved Business 1',
            'business_slug' => 'approved-business-1',
            'description' => 'This is an approved business',
            'industry' => 'Technology',
            'business_type' => 'LLC',
            'primary_email' => 'contact@approved1.com',
            'phone_number' => '555-0101',
            'street_address' => '123 Main St',
            'city' => 'Test City',
            'state_province' => 'Test State',
            'postal_code' => '12345',
            'country' => 'Test Country',
            'owner_name' => 'John Doe',
            'owner_email' => 'john@approved1.com',
            'status' => 'approved',
            'is_verified' => true,
        ]);

        $approvedBusiness2 = Business::create([
            'business_name' => 'Approved Business 2',
            'business_slug' => 'approved-business-2',
            'description' => 'Another approved business',
            'industry' => 'Healthcare',
            'business_type' => 'Corporation',
            'primary_email' => 'contact@approved2.com',
            'phone_number' => '555-0102',
            'street_address' => '456 Oak Ave',
            'city' => 'Test City',
            'state_province' => 'Test State',
            'postal_code' => '12345',
            'country' => 'Test Country',
            'owner_name' => 'Jane Smith',
            'owner_email' => 'jane@approved2.com',
            'status' => 'approved',
        ]);

        // Create a pending business (should not appear)
        $pendingBusiness = Business::create([
            'business_name' => 'Pending Business',
            'business_slug' => 'pending-business',
            'description' => 'This business is pending approval',
            'industry' => 'Finance',
            'business_type' => 'LLC',
            'primary_email' => 'contact@pending.com',
            'phone_number' => '555-0103',
            'street_address' => '789 Pine St',
            'city' => 'Test City',
            'state_province' => 'Test State',
            'postal_code' => '12345',
            'country' => 'Test Country',
            'owner_name' => 'Bob Johnson',
            'owner_email' => 'bob@pending.com',
            'status' => 'pending',
        ]);

        $response = $this->get(route('businesses.index'));

        $response->assertStatus(200)
            ->assertSee('Approved Business 1')
            ->assertSee('Approved Business 2')
            ->assertSee('Technology')
            ->assertSee('Healthcare')
            ->assertDontSee('Pending Business');
    }

    #[Test]
    public function business_listing_shows_message_when_no_businesses()
    {
        $response = $this->get(route('businesses.index'));

        $response->assertStatus(200)
            ->assertSee('No businesses found')
            ->assertSee('Our directory is just getting started')
            ->assertSee('Add Your Business')
            ->assertSee('Why join our directory?');
    }

    #[Test]
    public function business_listing_displays_business_cards_with_key_information()
    {
        $business = Business::create([
            'business_name' => 'Tech Solutions Inc',
            'business_slug' => 'tech-solutions-inc',
            'description' => 'We provide innovative technology solutions for modern businesses.',
            'tagline' => 'Innovation at your fingertips',
            'industry' => 'Technology',
            'business_type' => 'Corporation',
            'primary_email' => 'info@techsolutions.com',
            'phone_number' => '555-TECH-SOL',
            'website_url' => 'https://techsolutions.com',
            'street_address' => '100 Innovation Drive',
            'city' => 'Tech City',
            'state_province' => 'California',
            'postal_code' => '90210',
            'country' => 'USA',
            'owner_name' => 'Alex Tech',
            'owner_email' => 'alex@techsolutions.com',
            'status' => 'approved',
            'is_verified' => true,
        ]);

        $response = $this->get(route('businesses.index'));

        $response->assertStatus(200)
            ->assertSee('Tech Solutions Inc')
            ->assertSee('Innovation at your fingertips')
            ->assertSee('Technology')
            ->assertSee('Tech City, California')
            ->assertSee('We provide innovative technology solutions');
    }

    #[Test]
    public function businesses_are_ordered_by_featured_first_then_alphabetically()
    {
        // Create a regular business
        Business::create([
            'business_name' => 'Zebra Company',
            'business_slug' => 'zebra-company',
            'description' => 'Last alphabetically but not featured',
            'industry' => 'Retail',
            'business_type' => 'LLC',
            'primary_email' => 'info@zebra.com',
            'phone_number' => '555-ZEBRA',
            'street_address' => '200 Zebra Lane',
            'city' => 'Test City',
            'state_province' => 'Test State',
            'postal_code' => '12345',
            'country' => 'Test Country',
            'owner_name' => 'Zebra Owner',
            'owner_email' => 'owner@zebra.com',
            'status' => 'approved',
            'is_featured' => false,
        ]);

        // Create a featured business
        Business::create([
            'business_name' => 'Alpha Business',
            'business_slug' => 'alpha-business',
            'description' => 'First alphabetically and featured',
            'industry' => 'Finance',
            'business_type' => 'Corporation',
            'primary_email' => 'info@alpha.com',
            'phone_number' => '555-ALPHA',
            'street_address' => '100 Alpha Street',
            'city' => 'Test City',
            'state_province' => 'Test State',
            'postal_code' => '12345',
            'country' => 'Test Country',
            'owner_name' => 'Alpha Owner',
            'owner_email' => 'owner@alpha.com',
            'status' => 'approved',
            'is_featured' => true,
        ]);

        // Create another regular business
        Business::create([
            'business_name' => 'Beta Solutions',
            'business_slug' => 'beta-solutions',
            'description' => 'Middle alphabetically, not featured',
            'industry' => 'Technology',
            'business_type' => 'LLC',
            'primary_email' => 'info@beta.com',
            'phone_number' => '555-BETA',
            'street_address' => '150 Beta Boulevard',
            'city' => 'Test City',
            'state_province' => 'Test State',
            'postal_code' => '12345',
            'country' => 'Test Country',
            'owner_name' => 'Beta Owner',
            'owner_email' => 'owner@beta.com',
            'status' => 'approved',
            'is_featured' => false,
        ]);

        $response = $this->get(route('businesses.index'));
        $content = $response->getContent();

        // Featured business should appear first, regardless of alphabetical order
        $alphaPosition = strpos($content, 'Alpha Business');
        $betaPosition = strpos($content, 'Beta Solutions');
        $zebraPosition = strpos($content, 'Zebra Company');

        $this->assertTrue($alphaPosition < $betaPosition, 'Featured Alpha Business should appear before Beta Solutions');
        $this->assertTrue($betaPosition < $zebraPosition, 'Beta Solutions should appear before Zebra Company');
    }
} 