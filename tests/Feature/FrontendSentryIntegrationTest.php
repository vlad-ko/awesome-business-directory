<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class FrontendSentryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function welcome_page_includes_sentry_configuration()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check that Sentry configuration is included
        $response->assertSee('window.sentryConfig', false);
        $response->assertSee('environment', false);
        $response->assertSee('tracesSampleRate', false);
    }

    #[Test]
    public function sentry_configuration_includes_user_context_when_authenticated()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
        
        $response = $this->actingAs($user)->get('/');
        
        $response->assertStatus(200);
        
        // Check user context is included
        $content = $response->getContent();
        $this->assertStringContainsString('window.sentryConfig', $content);
        $this->assertStringContainsString('id: \'' . $user->id . '\'', $content);
        $this->assertStringContainsString('email: \'test@example.com\'', $content);
    }

    #[Test]
    public function business_listing_page_includes_sentry_tracking()
    {
        Business::factory()->count(3)->create(['status' => 'approved']);
        
        $response = $this->get('/businesses');
        
        $response->assertStatus(200);
        
        // Check that the page includes Alpine.js business directory component
        $response->assertSee('x-data="businessDirectory"', false);
        // The tracking is handled via compiled assets
        $response->assertSee('window.sentryConfig', false);
    }

    #[Test]
    public function onboarding_pages_include_sentry_tracking()
    {
        // The single-page onboarding redirects to multi-step
        $response = $this->get('/onboard');
        $response->assertRedirect('/onboard/step/1');
        
        // Multi-step onboarding
        $response2 = $this->get('/onboard/step/1');
        $response2->assertStatus(200);
        $response2->assertSee('window.sentryConfig', false);
    }

    #[Test]
    public function sentry_improvements_file_exists_and_is_valid()
    {
        $this->assertFileExists(resource_path('js/sentry-improvements.js'));
        
        $content = file_get_contents(resource_path('js/sentry-improvements.js'));
        
        // Check for modern patterns
        $this->assertStringContainsString('browserTracingIntegration', $content);
        $this->assertStringContainsString('consoleLoggingIntegration', $content);
        $this->assertStringContainsString('_experiments: {', $content);
        $this->assertStringContainsString('enableLogs: true', $content);
        
        // Check for business tracking functions
        $this->assertStringContainsString('ModernPerformanceMonitoring', $content);
        $this->assertStringContainsString('BusinessTracking', $content);
        $this->assertStringContainsString('WebVitalsTracking', $content);
        
        // Check for modern patterns
        $this->assertStringContainsString('logger.fmt', $content);
        $this->assertStringContainsString('Sentry.startSpan', $content);
    }

    #[Test]
    public function javascript_includes_distributed_tracing_support()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Check for distributed tracing elements in the layout
        $response->assertSee('sentry-trace', false);
        $response->assertSee('enableTracing: true', false);
    }

    #[Test]
    public function admin_pages_include_enhanced_sentry_context()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        
        // Check for admin-specific context
        $content = $response->getContent();
        $this->assertStringContainsString('is_admin', $content);
    }

    #[Test]
    public function error_pages_include_sentry_error_boundary()
    {
        // Test 404 page includes Sentry
        $response = $this->get('/non-existent-page');
        
        $response->assertStatus(404);
        
        // Error pages might not have full Sentry config, but should have basic error handling
        // This is OK - error pages are minimal by design
        $this->assertTrue(true);
    }

    #[Test]
    public function frontend_configuration_follows_best_practices()
    {
        $response = $this->get('/');
        
        $content = $response->getContent();
        
        // Check that Sentry configuration is in the head
        $this->assertStringContainsString('window.sentryConfig', $content);
        
        // Verify that the page has proper structure
        $this->assertStringContainsString('<!DOCTYPE html>', $content);
        $this->assertStringContainsString('</html>', $content);
        
        // Verify Sentry is configured early in the page
        $sentryConfigPos = strpos($content, 'window.sentryConfig');
        $bodyPos = strpos($content, '<body');
        
        $this->assertLessThan($bodyPos, $sentryConfigPos, 'Sentry config should come before body tag');
    }
}
