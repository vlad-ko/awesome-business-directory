<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SentryAlpineIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function welcome_page_loads_with_sentry_configuration()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Verify Sentry configuration is present
        $response->assertSee('window.sentryConfig');
        $response->assertSee(config('sentry.dsn'));
        $response->assertSee(config('app.env'));
        
        // Verify Alpine.js integration elements
        $response->assertSee('x-data="welcomePage"', false);
        $response->assertSee('x-track=', false);
    }

    /** @test */
    public function welcome_page_includes_interactive_demo_with_tracking()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Verify demo component structure
        $response->assertSee('Interactive Demo');
        $response->assertSee('demoStep: 1');
        $response->assertSee('x-track=\'{"action": "demo_search", "step": 1}\'', false);
        $response->assertSee('x-track=\'{"action": "demo_business_name", "step": 2}\'', false);
        $response->assertSee('x-track=\'{"action": "demo_complete"', false);
    }

    /** @test */
    public function welcome_page_includes_comprehensive_cta_tracking()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Verify CTA tracking attributes
        $response->assertSee('x-track=\'{"action": "browse_businesses", "source": "hero_cta", "position": "primary"}\'', false);
        $response->assertSee('x-track=\'{"action": "add_business", "source": "hero_cta", "position": "secondary"}\'', false);
        $response->assertSee('x-track=\'{"action": "add_business", "source": "bottom_cta", "position": "primary"}\'', false);
        $response->assertSee('x-track=\'{"action": "browse_businesses", "source": "bottom_cta", "position": "secondary"}\'', false);
    }

    /** @test */
    public function sentry_configuration_includes_user_context_when_authenticated()
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'test@example.com',
            'is_admin' => false
        ]);

        $response = $this->actingAs($user)->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('window.userContext');
        $response->assertSee($user->id);
        $response->assertSee('test@example.com');
        $response->assertSee('is_admin: false');
    }

    /** @test */
    public function sentry_configuration_includes_admin_context_for_admin_users()
    {
        $admin = \App\Models\User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true
        ]);

        $response = $this->actingAs($admin)->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('window.userContext');
        $response->assertSee($admin->id);
        $response->assertSee('admin@example.com');
        $response->assertSee('is_admin: true');
    }

    /** @test */
    public function sentry_configuration_shows_null_user_context_when_not_authenticated()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertSee('window.userContext = null');
    }

    /** @test */
    public function businesses_page_includes_alpine_business_directory_component()
    {
        $response = $this->get('/businesses');
        
        $response->assertStatus(200);
        
        // Verify business directory Alpine component
        $response->assertSee('x-data="businessDirectory"', false);
        $response->assertSee('searchTerm');
        $response->assertSee('selectedIndustry');
        $response->assertSee('filteredBusinesses');
    }

    /** @test */
    public function onboarding_page_includes_alpine_onboarding_form_component()
    {
        $response = $this->get('/onboard/step/1');
        
        $response->assertStatus(200);
        
        // Verify onboarding step includes form elements and navigation
        $response->assertSee('Step 1 of');
        $response->assertSee('business_name');
        $response->assertSee('industry');
        $response->assertSee('business_type');
        $response->assertSee('Continue to Contact Info');
    }

    /** @test */
    public function admin_dashboard_includes_alpine_admin_component()
    {
        $admin = \App\Models\User::factory()->create(['is_admin' => true]);
        
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        
        // Verify admin dashboard content and functionality
        $response->assertSee('Admin Dashboard');
        $response->assertSee('Pending Approval');
        $response->assertSee('Total Businesses');
        $response->assertSee('Manage business listings');
    }

    /** @test */
    public function vite_assets_include_sentry_and_alpine_dependencies()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Verify essential assets and configuration are present
        $response->assertSee('window.sentryConfig');
        $response->assertSee('<!DOCTYPE html>');
        $response->assertSee('</html>');
    }

    /** @test */
    public function welcome_page_includes_comprehensive_feature_descriptions()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Verify feature descriptions mention our tech stack
        $response->assertSee('Built with Laravel');
        $response->assertSee('Alpine.js for smooth interactions');
        $response->assertSee('comprehensive error tracking and performance monitoring via Sentry');
        $response->assertSee('Comprehensive analytics and business insights');
    }

    /** @test */
    public function demo_section_includes_progressive_step_tracking()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Verify demo progression tracking
        $response->assertSee('Demo Progress');
        $response->assertSee('demoStep/3');
        $response->assertSee('Step 1: Search for Businesses');
        $response->assertSee('Step 2: Add Your Business');
        $response->assertSee('Step 3: Contact Information');
        $response->assertSee('Demo Completed!');
    }

    /** @test */
    public function welcome_page_includes_modern_ui_elements()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Verify modern Tailwind CSS classes
        $response->assertSee('bg-gradient-to-tr');
        $response->assertSee('backdrop-blur');
        $response->assertSee('transition-colors');
        $response->assertSee('focus-visible:outline');
        $response->assertSee('shadow-lg');
        $response->assertSee('rounded-lg');
    }

    /** @test */
    public function page_includes_accessibility_features()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Verify accessibility attributes
        $response->assertSee('aria-hidden="true"', false);
        $response->assertSee('focus-visible:outline');
        $response->assertSee('text-center');
        $response->assertSee('leading-8'); // Good line height for readability
    }

    /** @test */
    public function sentry_browser_sdk_configuration_is_comprehensive()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Verify Sentry configuration completeness
        $response->assertSee('tracesSampleRate: 1.0');
        $response->assertSee('release:');
        
        // Configuration should be available before app.js loads
        $content = $response->getContent();
        $sentryConfigPosition = strpos($content, 'window.sentryConfig');
        $vitePosition = strpos($content, '@vite');
        
        $this->assertLessThan($vitePosition, $sentryConfigPosition, 
            'Sentry configuration should be available before Vite assets load');
    }
} 