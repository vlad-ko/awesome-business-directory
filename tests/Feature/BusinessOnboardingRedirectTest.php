<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class BusinessOnboardingRedirectTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function old_onboard_route_redirects_to_step_1()
    {
        $response = $this->get(route('business.onboard'));
        
        $response->assertRedirect(route('business.onboard.step', 1));
    }

    #[Test]
    public function old_onboard_post_route_redirects_to_step_1()
    {
        $response = $this->post(route('business.store'), [
            'business_name' => 'Test Business',
            'industry' => 'Technology',
            'business_type' => 'LLC',
            'description' => 'A test business description',
        ]);
        
        $response->assertRedirect(route('business.onboard.step', 1));
    }

    #[Test]
    public function welcome_page_links_to_new_multi_step_form()
    {
        $response = $this->get(route('welcome'));
        
        $response->assertStatus(200)
            ->assertSee(route('business.onboard.step', 1));
    }

    #[Test]
    public function users_starting_multi_step_flow_can_access_step_1()
    {
        $response = $this->get(route('business.onboard.step', 1));
        
        $response->assertStatus(200)
            ->assertViewIs('onboarding.steps.step1')
            ->assertSee('Tell Us About Your Amazing Business');
    }

    #[Test]
    public function step_1_form_submits_to_correct_route()
    {
        $response = $this->get(route('business.onboard.step', 1));
        
        $response->assertStatus(200)
            ->assertSee(route('business.onboard.step.store', 1));
    }

    #[Test]
    public function navigation_links_use_multi_step_routes()
    {
        // Test that any navigation or CTA buttons point to the new multi-step flow
        $response = $this->get(route('welcome'));
        
        $response->assertStatus(200);
        
        // Check that the welcome page doesn't contain old onboard links
        $content = $response->getContent();
        $this->assertStringNotContainsString('"/onboard"', $content);
        $this->assertStringNotContainsString("'/onboard'", $content);
    }

    #[Test]
    public function business_listing_page_links_to_multi_step_form()
    {
        $response = $this->get(route('businesses.index'));
        
        $response->assertStatus(200);
        
        // If there are any "Add Business" links, they should point to step 1
        $content = $response->getContent();
        if (str_contains($content, 'Add') || str_contains($content, 'List') || str_contains($content, 'Submit')) {
            $this->assertStringContainsString(route('business.onboard.step', 1), $content);
        }
    }

    #[Test]
    public function success_page_links_are_correct()
    {
        // Test that success page links work correctly
        $response = $this->get(route('business.onboard.success'));
        
        $response->assertStatus(200)
            ->assertSee(route('businesses.index'))
            ->assertSee(route('welcome'));
    }
} 