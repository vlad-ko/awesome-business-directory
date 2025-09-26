<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SentryLogger;
use App\Services\CriticalExperienceTracker;
use App\Models\Business;

class TestCriticalExperienceVisibility extends Command
{
    protected $signature = 'test:ce-visibility';
    protected $description = 'Test Critical Experience visibility in Sentry';

    public function handle()
    {
        $this->info('Testing Critical Experience visibility in Sentry...');
        
        // Create a main transaction with visible child spans
        SentryLogger::trackBusinessOperation('test_visibility', [
            'test' => true,
            'purpose' => 'visibility_check',
        ], function ($mainSpan) {
            
            // 1. Test Discovery Path with child spans
            $this->info('Testing Discovery Path...');
            
            // Start discovery
            SentryLogger::trace(function ($span) {
                CriticalExperienceTracker::trackDiscoveryStart();
                sleep(1); // Make it visible in timeline
            }, [
                'op' => 'critical.discovery.start',
                'name' => 'Discovery Started',
                'tags' => ['critical.checkpoint' => 'start'],
            ]);
            
            // View businesses
            $businesses = Business::approved()->limit(3)->get();
            foreach ($businesses as $business) {
                SentryLogger::trace(function ($span) use ($business) {
                    CriticalExperienceTracker::trackBusinessViewed($business);
                    usleep(500000); // 0.5 seconds
                }, [
                    'op' => 'critical.discovery.view',
                    'name' => "Viewed: {$business->business_name}",
                    'tags' => [
                        'critical.checkpoint' => 'business_view',
                        'business.id' => $business->id,
                    ],
                ]);
            }
            
            // Contact simulation
            if ($businesses->isNotEmpty()) {
                $business = $businesses->first();
                SentryLogger::trace(function ($span) use ($business) {
                    CriticalExperienceTracker::trackBusinessContact($business, 'website');
                    sleep(1);
                }, [
                    'op' => 'critical.discovery.conversion',
                    'name' => 'Business Contact Conversion',
                    'tags' => [
                        'critical.checkpoint' => 'conversion',
                        'critical.conversion' => 'business_contact',
                    ],
                ]);
            }
            
            // 2. Test Onboarding Path
            $this->info('Testing Onboarding Path...');
            
            SentryLogger::trace(function ($span) {
                CriticalExperienceTracker::trackOnboardingStart();
                
                // Simulate steps
                for ($step = 1; $step <= 4; $step++) {
                    SentryLogger::trace(function ($stepSpan) use ($step) {
                        sleep(1);
                        CriticalExperienceTracker::trackOnboardingStepComplete($step);
                    }, [
                        'op' => "critical.onboarding.step_{$step}",
                        'name' => "Onboarding Step {$step}",
                        'tags' => [
                            'critical.checkpoint' => "step_{$step}_complete",
                            'critical.step' => $step,
                        ],
                    ]);
                }
            }, [
                'op' => 'critical.onboarding.flow',
                'name' => 'Onboarding Flow Test',
                'tags' => ['critical.experience' => 'business_onboarding'],
            ]);
            
            $this->info('✅ Test complete! Check Sentry Trace Explorer now.');
        });
        
        $this->newLine();
        $this->info('🔍 To find this test in Sentry:');
        $this->info('1. Go to Trace Explorer');
        $this->info('2. Search for: span.op:critical.* OR test:true');
        $this->info('3. Look for spans with op starting with "critical."');
        $this->info('4. Click on trace IDs to see the full hierarchy');
        
        return 0;
    }
}
