<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SimulateCriticalExperiences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulate:all 
                            {--discovery-count=200 : Number of discovery visitors}
                            {--onboarding-count=50 : Number of onboarding users}
                            {--realistic : Use realistic traffic patterns}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all critical experience simulations with realistic traffic patterns';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $discoveryCount = (int) $this->option('discovery-count');
        $onboardingCount = (int) $this->option('onboarding-count');
        $realistic = $this->option('realistic');
        
        $this->info('🚀 Starting Critical Experience Simulation');
        $this->info('This will generate realistic user traffic for Sentry visualization');
        $this->newLine();
        
        // Set realistic parameters if requested
        if ($realistic) {
            $this->info('Using realistic traffic patterns...');
            $discoveryParams = [
                '--count' => $discoveryCount,
                '--view-rate' => 0.35,      // 35% view businesses
                '--contact-rate' => 0.12,    // 12% of viewers contact
            ];
            
            $onboardingParams = [
                '--count' => $onboardingCount,
                '--drop-rate' => 0.25,       // 25% base drop rate
                '--error-rate' => 0.03,      // 3% error rate
            ];
        } else {
            $discoveryParams = ['--count' => $discoveryCount];
            $onboardingParams = ['--count' => $onboardingCount];
        }
        
        // Run discovery simulation
        $this->info('📍 Phase 1: Simulating Business Discovery Journey');
        $this->info('=========================================');
        $this->call('simulate:discovery', $discoveryParams);
        
        $this->newLine(2);
        
        // Run onboarding simulation
        $this->info('📝 Phase 2: Simulating Business Onboarding Journey');
        $this->info('===========================================');
        $this->call('simulate:onboarding', $onboardingParams);
        
        $this->newLine(2);
        $this->info('✅ Simulation Complete!');
        $this->info('Check your Sentry dashboard to see:');
        $this->info('  - Performance metrics for critical paths');
        $this->info('  - Conversion funnels');
        $this->info('  - Error patterns');
        $this->info('  - User journey breadcrumbs');
        $this->newLine();
        
        // Provide Sentry dashboard hints
        $this->info('💡 Sentry Dashboard Tips:');
        $this->info('  1. Go to Performance → Transactions');
        $this->info('  2. Filter by transaction name containing "business"');
        $this->info('  3. Check the User Feedback section for breadcrumbs');
        $this->info('  4. Use Discover to query critical.* tags');
        $this->newLine();
        
        return 0;
    }
}
