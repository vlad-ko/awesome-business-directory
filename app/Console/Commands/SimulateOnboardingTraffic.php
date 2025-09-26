<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\CriticalExperienceTracker;
use App\Services\SentryLogger;
use Faker\Factory as Faker;

class SimulateOnboardingTraffic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulate:onboarding 
                            {--count=100 : Number of simulated users}
                            {--drop-rate=0.3 : Overall drop-off rate (0.0-1.0)}
                            {--error-rate=0.05 : Error rate (0.0-1.0)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate user traffic for business onboarding with realistic drop-offs';

    /**
     * Drop-off rates per step (realistic funnel)
     */
    private const STEP_DROP_RATES = [
        1 => 0.15,  // 15% drop after seeing step 1
        2 => 0.25,  // 25% drop after step 1 completion
        3 => 0.20,  // 20% drop after step 2 completion
        4 => 0.10,  // 10% drop after step 3 completion
    ];

    /**
     * Industries for random selection
     */
    private const INDUSTRIES = [
        'Technology', 'Retail', 'Food & Beverage', 'Healthcare', 
        'Education', 'Entertainment', 'Real Estate', 'Finance'
    ];

    /**
     * Business types
     */
    private const BUSINESS_TYPES = [
        'LLC', 'Corporation', 'Partnership', 'Sole Proprietorship'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');
        $baseDropRate = (float) $this->option('drop-rate');
        $errorRate = (float) $this->option('error-rate');
        
        $this->info("Starting onboarding simulation for {$count} users...");
        $this->info("Base drop-off rate: " . ($baseDropRate * 100) . "%");
        $this->info("Error rate: " . ($errorRate * 100) . "%");
        
        $faker = Faker::create();
        
        $stats = [
            'started' => 0,
            'step_1_completed' => 0,
            'step_2_completed' => 0,
            'step_3_completed' => 0,
            'step_4_completed' => 0,
            'completed' => 0,
            'abandoned' => 0,
            'errors' => 0,
        ];
        
        for ($i = 0; $i < $count; $i++) {
            $this->simulateUser($faker, $baseDropRate, $errorRate, $stats);
            
            // Add some delay to make it more realistic
            usleep(rand(100000, 500000)); // 0.1-0.5 seconds
            
            if (($i + 1) % 10 === 0) {
                $this->info("Processed " . ($i + 1) . " users...");
            }
        }
        
        $this->displayStats($stats);
    }

    /**
     * Simulate a single user's journey
     */
    private function simulateUser($faker, float $baseDropRate, float $errorRate, array &$stats)
    {
        // Start onboarding journey
        $stats['started']++;
        
        // Use SentryLogger to create a transaction for this simulation
        SentryLogger::trackBusinessOperation('simulated_onboarding', [
            'simulation' => true,
            'user_id' => $faker->uuid,
        ], function ($span) use ($faker, $baseDropRate, $errorRate, &$stats) {
            
            // Track onboarding start
            CriticalExperienceTracker::trackOnboardingStart();
            
            // Simulate step progression
            for ($step = 1; $step <= 4; $step++) {
                // Check for random error
                if ($this->shouldError($errorRate)) {
                    $stats['errors']++;
                    $this->simulateError($step);
                    $stats['abandoned']++;
                    CriticalExperienceTracker::trackOnboardingAbandoned($step);
                    return;
                }
                
                // Check for drop-off
                $dropRate = self::STEP_DROP_RATES[$step] * (1 + $baseDropRate);
                if ($this->shouldDropOff($dropRate)) {
                    $stats['abandoned']++;
                    
                    // Simulate time spent before abandoning
                    usleep(rand(1000000, 3000000)); // 1-3 seconds
                    
                    CriticalExperienceTracker::trackOnboardingAbandoned($step);
                    return;
                }
                
                // Simulate form filling time
                usleep(rand(2000000, 5000000)); // 2-5 seconds per step
                
                // Complete the step
                $this->simulateStepData($step, $faker);
                CriticalExperienceTracker::trackOnboardingStepComplete($step);
                $stats["step_{$step}_completed"]++;
                
                // Add inter-step delay
                if ($step < 4) {
                    usleep(rand(500000, 1500000)); // 0.5-1.5 seconds
                }
            }
            
            // Complete onboarding
            $business = $this->createSimulatedBusiness($faker);
            CriticalExperienceTracker::trackOnboardingComplete($business);
            $stats['completed']++;
        });
    }

    /**
     * Simulate step data in session
     */
    private function simulateStepData(int $step, $faker)
    {
        switch ($step) {
            case 1:
                session(["onboarding_step_1" => [
                    'business_name' => $faker->company,
                    'industry' => $faker->randomElement(self::INDUSTRIES),
                    'business_type' => $faker->randomElement(self::BUSINESS_TYPES),
                    'description' => $faker->paragraph,
                    'tagline' => $faker->catchPhrase,
                ]]);
                break;
                
            case 2:
                session(["onboarding_step_2" => [
                    'primary_email' => $faker->companyEmail,
                    'phone_number' => $faker->phoneNumber,
                    'website_url' => $faker->url,
                ]]);
                break;
                
            case 3:
                session(["onboarding_step_3" => [
                    'street_address' => $faker->streetAddress,
                    'city' => $faker->city,
                    'state_province' => $faker->stateAbbr,
                    'postal_code' => $faker->postcode,
                    'country' => 'USA',
                ]]);
                break;
                
            case 4:
                session(["onboarding_step_4" => [
                    'owner_name' => $faker->name,
                    'owner_email' => $faker->email,
                ]]);
                break;
        }
    }

    /**
     * Create a simulated business
     */
    private function createSimulatedBusiness($faker)
    {
        // Create a real business record for simulation
        return \App\Models\Business::create([
            'business_name' => session('onboarding_step_1')['business_name'] ?? $faker->company,
            'business_slug' => $faker->slug,
            'industry' => session('onboarding_step_1')['industry'] ?? 'Technology',
            'business_type' => session('onboarding_step_1')['business_type'] ?? 'LLC',
            'description' => session('onboarding_step_1')['description'] ?? $faker->paragraph,
            'tagline' => session('onboarding_step_1')['tagline'] ?? $faker->catchPhrase,
            'primary_email' => session('onboarding_step_2')['primary_email'] ?? $faker->companyEmail,
            'phone_number' => session('onboarding_step_2')['phone_number'] ?? $faker->phoneNumber,
            'website_url' => session('onboarding_step_2')['website_url'] ?? $faker->url,
            'street_address' => session('onboarding_step_3')['street_address'] ?? $faker->streetAddress,
            'city' => session('onboarding_step_3')['city'] ?? $faker->city,
            'state_province' => session('onboarding_step_3')['state_province'] ?? $faker->stateAbbr,
            'postal_code' => session('onboarding_step_3')['postal_code'] ?? $faker->postcode,
            'country' => session('onboarding_step_3')['country'] ?? 'USA',
            'owner_name' => session('onboarding_step_4')['owner_name'] ?? $faker->name,
            'owner_email' => session('onboarding_step_4')['owner_email'] ?? $faker->email,
            'status' => 'pending',
            'is_featured' => false,
            'is_verified' => false,
        ]);
    }

    /**
     * Simulate an error during onboarding
     */
    private function simulateError(int $step)
    {
        $errors = [
            'Database connection timeout',
            'Validation error: Invalid email format',
            'Network timeout',
            'Server error: 500',
            'Session expired',
        ];
        
        $error = new \Exception($errors[array_rand($errors)]);
        
        CriticalExperienceTracker::trackCriticalError(
            'business_onboarding',
            "step_{$step}_error",
            $error,
            ['simulated' => true]
        );
    }

    /**
     * Determine if user should drop off
     */
    private function shouldDropOff(float $rate): bool
    {
        return (mt_rand() / mt_getrandmax()) < $rate;
    }

    /**
     * Determine if an error should occur
     */
    private function shouldError(float $rate): bool
    {
        return (mt_rand() / mt_getrandmax()) < $rate;
    }

    /**
     * Display simulation statistics
     */
    private function displayStats(array $stats)
    {
        $this->newLine();
        $this->info('=== Simulation Complete ===');
        $this->table(
            ['Metric', 'Count', 'Percentage'],
            [
                ['Started', $stats['started'], '100%'],
                ['Step 1 Completed', $stats['step_1_completed'], $this->percentage($stats['step_1_completed'], $stats['started'])],
                ['Step 2 Completed', $stats['step_2_completed'], $this->percentage($stats['step_2_completed'], $stats['started'])],
                ['Step 3 Completed', $stats['step_3_completed'], $this->percentage($stats['step_3_completed'], $stats['started'])],
                ['Step 4 Completed', $stats['step_4_completed'], $this->percentage($stats['step_4_completed'], $stats['started'])],
                ['Fully Completed', $stats['completed'], $this->percentage($stats['completed'], $stats['started'])],
                ['Abandoned', $stats['abandoned'], $this->percentage($stats['abandoned'], $stats['started'])],
                ['Errors', $stats['errors'], $this->percentage($stats['errors'], $stats['started'])],
            ]
        );
        
        $this->newLine();
        $this->info('Conversion Rate: ' . $this->percentage($stats['completed'], $stats['started']));
        $this->info('Drop-off Rate: ' . $this->percentage($stats['abandoned'], $stats['started']));
    }

    /**
     * Calculate percentage
     */
    private function percentage(int $value, int $total): string
    {
        if ($total === 0) return '0%';
        return round(($value / $total) * 100, 1) . '%';
    }
}
