<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;

class BusinessOnboardingMultiStepTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    protected function getStep1Data(): array
    {
        return [
            'business_name' => $this->faker->company,
            'industry' => $this->faker->word,
            'business_type' => 'LLC',
            'description' => $this->faker->paragraph,
            'tagline' => $this->faker->sentence,
        ];
    }
    
    protected function getStep2Data(): array
    {
        return [
            'primary_email' => $this->faker->email,
            'phone_number' => $this->faker->phoneNumber,
            'website_url' => $this->faker->url,
        ];
    }
    
    protected function getStep3Data(): array
    {
        return [
            'street_address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state_province' => $this->faker->state,
            'postal_code' => $this->faker->postcode,
            'country' => $this->faker->country,
        ];
    }
    
    protected function getStep4Data(): array
    {
        return [
            'owner_name' => $this->faker->name,
            'owner_email' => $this->faker->email,
        ];
    }
    
    #[Test]
    public function user_can_view_step_1_of_onboarding_form()
    {
        $response = $this->get(route('business.onboard.step', 1));
        
        $response->assertStatus(200)
            ->assertViewIs('onboarding.steps.step1')
            ->assertSee('Tell Us About Your Amazing Business')
            ->assertSee('business_name')
            ->assertSee('industry')
            ->assertSee('business_type')
            ->assertSee('description')
            ->assertSee('tagline');
    }
    
    #[Test]
    public function user_can_submit_step_1_and_proceed_to_step_2()
    {
        $step1Data = $this->getStep1Data();
        
        $response = $this->post(route('business.onboard.step.store', 1), $step1Data);
        
        $response->assertRedirect(route('business.onboard.step', 2))
            ->assertSessionHas('onboarding_step_1', $step1Data);
    }
    
    #[Test]
    public function step_1_requires_all_required_fields()
    {
        $response = $this->post(route('business.onboard.step.store', 1), []);
        
        $response->assertSessionHasErrors([
            'business_name',
            'industry',
            'business_type',
            'description'
        ]);
    }
    
    #[Test]
    public function user_can_view_step_2_after_completing_step_1()
    {
        // Complete step 1 first
        $step1Data = $this->getStep1Data();
        $this->withSession(['onboarding_step_1' => $step1Data]);
        
        $response = $this->get(route('business.onboard.step', 2));
        
        $response->assertStatus(200)
            ->assertViewIs('onboarding.steps.step2')
            ->assertSee('How Can Customers Reach You')
            ->assertSee('primary_email')
            ->assertSee('phone_number')
            ->assertSee('website_url');
    }
    
    #[Test]
    public function user_cannot_access_step_2_without_completing_step_1()
    {
        $response = $this->get(route('business.onboard.step', 2));
        
        $response->assertRedirect(route('business.onboard.step', 1))
            ->assertSessionHas('error', 'Please complete the previous steps first.');
    }
    
    #[Test]
    public function user_can_submit_step_2_and_proceed_to_step_3()
    {
        // Set up session data from step 1
        $step1Data = $this->getStep1Data();
        $this->withSession(['onboarding_step_1' => $step1Data]);
        
        $step2Data = $this->getStep2Data();
        
        $response = $this->post(route('business.onboard.step.store', 2), $step2Data);
        
        $response->assertRedirect(route('business.onboard.step', 3))
            ->assertSessionHas('onboarding_step_2', $step2Data);
    }
    
    #[Test]
    public function step_2_requires_email_and_phone()
    {
        // Set up session data from step 1
        $step1Data = $this->getStep1Data();
        $this->withSession(['onboarding_step_1' => $step1Data]);
        
        $response = $this->post(route('business.onboard.step.store', 2), []);
        
        $response->assertSessionHasErrors([
            'primary_email',
            'phone_number'
        ]);
    }
    
    #[Test]
    public function step_2_validates_email_format()
    {
        $step1Data = $this->getStep1Data();
        $this->withSession(['onboarding_step_1' => $step1Data]);
        
        $step2Data = $this->getStep2Data();
        $step2Data['primary_email'] = 'invalid-email';
        
        $response = $this->post(route('business.onboard.step.store', 2), $step2Data);
        
        $response->assertSessionHasErrors(['primary_email']);
    }
    
    #[Test]
    public function user_can_view_step_3_after_completing_previous_steps()
    {
        $step1Data = $this->getStep1Data();
        $step2Data = $this->getStep2Data();
        
        $this->withSession([
            'onboarding_step_1' => $step1Data,
            'onboarding_step_2' => $step2Data,
        ]);
        
        $response = $this->get(route('business.onboard.step', 3));
        
        $response->assertStatus(200)
            ->assertViewIs('onboarding.steps.step3')
            ->assertSee('Where Can People Find You')
            ->assertSee('street_address')
            ->assertSee('city')
            ->assertSee('state_province');
    }
    
    #[Test]
    public function user_can_submit_step_3_and_proceed_to_step_4()
    {
        $step1Data = $this->getStep1Data();
        $step2Data = $this->getStep2Data();
        
        $this->withSession([
            'onboarding_step_1' => $step1Data,
            'onboarding_step_2' => $step2Data,
        ]);
        
        $step3Data = $this->getStep3Data();
        
        $response = $this->post(route('business.onboard.step.store', 3), $step3Data);
        
        $response->assertRedirect(route('business.onboard.step', 4))
            ->assertSessionHas('onboarding_step_3', $step3Data);
    }
    
    #[Test]
    public function user_can_view_step_4_after_completing_previous_steps()
    {
        $step1Data = $this->getStep1Data();
        $step2Data = $this->getStep2Data();
        $step3Data = $this->getStep3Data();
        
        $this->withSession([
            'onboarding_step_1' => $step1Data,
            'onboarding_step_2' => $step2Data,
            'onboarding_step_3' => $step3Data,
        ]);
        
        $response = $this->get(route('business.onboard.step', 4));
        
        $response->assertStatus(200)
            ->assertViewIs('onboarding.steps.step4')
            ->assertSee('Owner Information')
            ->assertSee('owner_name')
            ->assertSee('owner_email');
    }
    
    #[Test]
    public function user_can_submit_step_4_and_proceed_to_review()
    {
        $step1Data = $this->getStep1Data();
        $step2Data = $this->getStep2Data();
        $step3Data = $this->getStep3Data();
        
        $this->withSession([
            'onboarding_step_1' => $step1Data,
            'onboarding_step_2' => $step2Data,
            'onboarding_step_3' => $step3Data,
        ]);
        
        $step4Data = $this->getStep4Data();
        
        $response = $this->post(route('business.onboard.step.store', 4), $step4Data);
        
        $response->assertRedirect(route('business.onboard.review'))
            ->assertSessionHas('onboarding_step_4', $step4Data);
    }
    
    #[Test]
    public function user_can_view_review_page_after_completing_all_steps()
    {
        $step1Data = $this->getStep1Data();
        $step2Data = $this->getStep2Data();
        $step3Data = $this->getStep3Data();
        $step4Data = $this->getStep4Data();
        
        $this->withSession([
            'onboarding_step_1' => $step1Data,
            'onboarding_step_2' => $step2Data,
            'onboarding_step_3' => $step3Data,
            'onboarding_step_4' => $step4Data,
        ]);
        
        $response = $this->get(route('business.onboard.review'));
        
        $response->assertStatus(200)
            ->assertViewIs('onboarding.review')
            ->assertSee('Review Your Information')
            ->assertSee($step1Data['business_name'])
            ->assertSee($step2Data['primary_email'])
            ->assertSee($step3Data['city'])
            ->assertSee($step4Data['owner_name']);
    }
    
    #[Test]
    public function user_can_submit_final_form_and_create_business()
    {
        $step1Data = $this->getStep1Data();
        $step2Data = $this->getStep2Data();
        $step3Data = $this->getStep3Data();
        $step4Data = $this->getStep4Data();
        
        $this->withSession([
            'onboarding_step_1' => $step1Data,
            'onboarding_step_2' => $step2Data,
            'onboarding_step_3' => $step3Data,
            'onboarding_step_4' => $step4Data,
        ]);
        
        $response = $this->post(route('business.onboard.submit'));
        
        $response->assertRedirect(route('business.onboard.success'))
            ->assertSessionHas('success', 'Business submitted for review!');
        
        // Check that business was created in database
        $this->assertDatabaseHas('businesses', [
            'business_name' => $step1Data['business_name'],
            'industry' => $step1Data['industry'],
            'primary_email' => $step2Data['primary_email'],
            'city' => $step3Data['city'],
            'owner_name' => $step4Data['owner_name'],
            'status' => 'pending',
        ]);
        
        // Check that session data is cleared after successful submission
        $this->assertFalse(session()->has('onboarding_step_1'));
        $this->assertFalse(session()->has('onboarding_step_2'));
        $this->assertFalse(session()->has('onboarding_step_3'));
        $this->assertFalse(session()->has('onboarding_step_4'));
    }
    
    #[Test]
    public function user_can_navigate_back_to_previous_steps()
    {
        $step1Data = $this->getStep1Data();
        $step2Data = $this->getStep2Data();
        
        $this->withSession([
            'onboarding_step_1' => $step1Data,
            'onboarding_step_2' => $step2Data,
        ]);
        
        // User should be able to go back to step 1
        $response = $this->get(route('business.onboard.step', 1));
        
        $response->assertStatus(200)
            ->assertSee($step1Data['business_name']); // Should show pre-filled data
    }
    
    #[Test]
    public function user_can_edit_previous_step_data()
    {
        $step1Data = $this->getStep1Data();
        $step2Data = $this->getStep2Data();
        
        $this->withSession([
            'onboarding_step_1' => $step1Data,
            'onboarding_step_2' => $step2Data,
        ]);
        
        // Edit step 1 data
        $updatedStep1Data = $step1Data;
        $updatedStep1Data['business_name'] = 'Updated Business Name';
        
        $response = $this->post(route('business.onboard.step.store', 1), $updatedStep1Data);
        
        $response->assertRedirect(route('business.onboard.step', 2))
            ->assertSessionHas('onboarding_step_1.business_name', 'Updated Business Name');
    }
    
    #[Test]
    public function form_progress_is_tracked_correctly()
    {
        // Step 1: 25% complete
        $step1Data = $this->getStep1Data();
        $response = $this->post(route('business.onboard.step.store', 1), $step1Data);
        $response->assertSessionHas('onboarding_progress', 25);
        
        // Step 2: 50% complete
        $step2Data = $this->getStep2Data();
        $response = $this->post(route('business.onboard.step.store', 2), $step2Data);
        $response->assertSessionHas('onboarding_progress', 50);
        
        // Step 3: 75% complete
        $step3Data = $this->getStep3Data();
        $response = $this->post(route('business.onboard.step.store', 3), $step3Data);
        $response->assertSessionHas('onboarding_progress', 75);
        
        // Step 4: 100% complete
        $step4Data = $this->getStep4Data();
        $response = $this->post(route('business.onboard.step.store', 4), $step4Data);
        $response->assertSessionHas('onboarding_progress', 100);
    }
} 