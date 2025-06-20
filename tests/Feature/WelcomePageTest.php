<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function welcome_page_loads_successfully()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('welcome');
    }

    #[Test]
    public function welcome_page_displays_correct_title()
    {
        $response = $this->get('/');

        $response->assertSee('Discover Amazing Local Businesses', false);
    }

    #[Test]
    public function welcome_page_displays_main_heading()
    {
        $response = $this->get('/');

        $response->assertSee('Discover Your');
        $response->assertSee('Neighborhood');
        $response->assertSee('Gems!');
    }

    #[Test]
    public function welcome_page_displays_navigation_links()
    {
        $response = $this->get('/');

        // Check navigation links are present
        $response->assertSee('Browse Businesses');
        $response->assertSee('Join Directory');
        
        // Check the actual route URLs are correct
        $response->assertSee(route('businesses.index'));
        $response->assertSee(route('business.onboard'));
    }

    #[Test]
    public function welcome_page_displays_hero_section_content()
    {
        $response = $this->get('/');

        $response->assertSee('Find amazing local shops, restaurants, and services');
        $response->assertSee('community awesome');
    }

    #[Test]
    public function welcome_page_displays_call_to_action_buttons()
    {
        $response = $this->get('/');

        // Check CTA button text
        $response->assertSee('🔍 Explore Businesses');
        $response->assertSee('🚀 List Your Business');
        
        // Check buttons link to correct routes
        $response->assertSee(route('businesses.index'));
        $response->assertSee(route('business.onboard'));
    }

    #[Test]
    public function welcome_page_displays_features_section()
    {
        $response = $this->get('/');

        $response->assertSee('Why Our Directory Rocks!');
        $response->assertSee('Local Businesses');
        $response->assertSee('Verified Quality');
        $response->assertSee('Easy to Use');
        
        // Check feature descriptions
        $response->assertSee('Discover amazing shops, restaurants, and services right in your neighborhood!');
        $response->assertSee('All businesses are carefully reviewed to ensure you get the best experience!');
        $response->assertSee('Simple, fast, and fun way to explore what your community has to offer!');
    }

    #[Test]
    public function welcome_page_displays_final_call_to_action()
    {
        $response = $this->get('/');

        $response->assertSee('Ready to Join the Fun?');
        $response->assertSee('Whether you\'re looking for businesses or want to list your own', false);
        $response->assertSee('Start Exploring 🔍');
        $response->assertSee('List Your Business 📝');
    }

    #[Test]
    public function welcome_page_displays_footer()
    {
        $response = $this->get('/');

        $response->assertSee('Made with 💜 for awesome local communities');
        $response->assertSee('Awesome Business Directory');
    }

    #[Test]
    public function welcome_page_contains_neighborhood_svg()
    {
        $response = $this->get('/');

        // Check for SVG elements that make up our neighborhood illustration
        $response->assertSee('<svg width="100%" height="400"', false);
        $response->assertSee('viewBox="0 0 500 400"', false);
        
        // Check for specific SVG content (buildings, roller skater, etc.)
        $response->assertSee('🍕 PIZZA', false);
        $response->assertSee('☕ CAFÉ', false);
        $response->assertSee('📚 BOOKS', false);
        $response->assertSee('🌸 FLOWERS', false);
        $response->assertSee('🥖 BAKERY', false);
    }

    #[Test]
    public function welcome_page_has_proper_css_classes()
    {
        $response = $this->get('/');

        // Check for key CSS classes that define the design
        $response->assertSee('gradient-bg');
        $response->assertSee('retro-text');
        $response->assertSee('neon-glow');
        $response->assertSee('backdrop-blur-md');
    }

    #[Test]
    public function welcome_page_navigation_links_are_functional()
    {
        // Test that clicking the Browse Businesses link works
        $response = $this->get('/');
        $response->assertSee(route('businesses.index'));
        
        // Follow the link to ensure it works
        $businessesResponse = $this->get(route('businesses.index'));
        $businessesResponse->assertStatus(200);
        
        // Test that clicking the Join Directory link works
        $onboardResponse = $this->get(route('business.onboard'));
        $onboardResponse->assertStatus(200);
    }

    #[Test]
    public function welcome_page_is_responsive_friendly()
    {
        $response = $this->get('/');

        // Check for responsive classes
        $response->assertSee('md:grid-cols-3');
        $response->assertSee('sm:flex-row');
        $response->assertSee('lg:text-7xl');
        $response->assertSee('max-w-2xl');
    }

    #[Test]
    public function welcome_page_has_proper_meta_tags()
    {
        $response = $this->get('/');

        $response->assertSee('<meta charset="utf-8">', false);
        $response->assertSee('<meta name="viewport" content="width=device-width, initial-scale=1">', false);
    }

    #[Test]
    public function welcome_page_loads_required_assets()
    {
        $response = $this->get('/');

        // Check that the page includes references to our asset files
        $response->assertSee('link', false);
        $response->assertSee('script', false);
    }

    #[Test]
    public function welcome_page_uses_app_name_from_config()
    {
        // Test with default config
        $response = $this->get('/');
        $response->assertSee(config('app.name'));

        // Test with custom app name
        config(['app.name' => 'Custom Business Directory']);
        $response = $this->get('/');
        $response->assertSee('Custom Business Directory');
    }
} 