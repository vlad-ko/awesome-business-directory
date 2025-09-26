<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Business;
use App\Services\CriticalExperienceTracker;
use App\Services\SentryLogger;
use Faker\Factory as Faker;

class SimulateDiscoveryTraffic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulate:discovery 
                            {--count=200 : Number of simulated visitors}
                            {--view-rate=0.4 : Rate of users who view businesses (0.0-1.0)}
                            {--contact-rate=0.15 : Rate of viewers who contact businesses (0.0-1.0)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate user traffic for business discovery with realistic conversion rates';

    /**
     * Contact methods
     */
    private const CONTACT_METHODS = ['website', 'phone', 'email'];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');
        $viewRate = (float) $this->option('view-rate');
        $contactRate = (float) $this->option('contact-rate');
        
        $this->info("Starting discovery simulation for {$count} visitors...");
        $this->info("View rate: " . ($viewRate * 100) . "%");
        $this->info("Contact rate: " . ($contactRate * 100) . "% of viewers");
        
        // Get available businesses
        $businesses = Business::approved()->get();
        
        if ($businesses->isEmpty()) {
            $this->error('No approved businesses found. Please seed some businesses first.');
            return 1;
        }
        
        $this->info("Found {$businesses->count()} approved businesses to work with.");
        
        $faker = Faker::create();
        
        $stats = [
            'visitors' => 0,
            'discovery_started' => 0,
            'businesses_viewed' => 0,
            'contacts_made' => 0,
            'unique_viewers' => [],
            'popular_businesses' => [],
        ];
        
        for ($i = 0; $i < $count; $i++) {
            $this->simulateVisitor($faker, $businesses, $viewRate, $contactRate, $stats);
            
            // Add some delay to make it more realistic
            usleep(rand(50000, 200000)); // 0.05-0.2 seconds
            
            if (($i + 1) % 20 === 0) {
                $this->info("Processed " . ($i + 1) . " visitors...");
            }
        }
        
        $this->displayStats($stats);
    }

    /**
     * Simulate a single visitor's journey
     */
    private function simulateVisitor($faker, $businesses, float $viewRate, float $contactRate, array &$stats)
    {
        $stats['visitors']++;
        $userId = $faker->uuid;
        
        // Use SentryLogger to create a transaction for this simulation
        SentryLogger::trackBusinessOperation('simulated_discovery', [
            'simulation' => true,
            'user_id' => $userId,
        ], function ($span) use ($faker, $businesses, $viewRate, $contactRate, &$stats, $userId) {
            
            // Not everyone starts browsing
            if (!$this->shouldPerformAction(0.8)) { // 80% actually browse
                return;
            }
            
            // Track discovery start
            CriticalExperienceTracker::trackDiscoveryStart();
            $stats['discovery_started']++;
            
            // Simulate browsing time
            usleep(rand(1000000, 3000000)); // 1-3 seconds
            
            // Determine if user views any businesses
            if (!$this->shouldPerformAction($viewRate)) {
                return;
            }
            
            // User is viewing businesses
            $stats['unique_viewers'][$userId] = true;
            
            // Determine how many businesses to view (power law distribution)
            $viewCount = $this->getPowerLawNumber(1, 10, 2);
            
            for ($v = 0; $v < $viewCount; $v++) {
                // Pick a business (popular ones more likely)
                $business = $this->selectBusinessWithBias($businesses);
                
                // Track business view
                CriticalExperienceTracker::trackBusinessViewed($business);
                $stats['businesses_viewed']++;
                
                // Track popularity
                $businessKey = $business->id;
                $stats['popular_businesses'][$businessKey] = 
                    ($stats['popular_businesses'][$businessKey] ?? 0) + 1;
                
                // Simulate viewing time
                usleep(rand(2000000, 5000000)); // 2-5 seconds
                
                // Check if user contacts this business
                if ($this->shouldPerformAction($contactRate)) {
                    $method = $faker->randomElement(self::CONTACT_METHODS);
                    
                    // Track conversion
                    CriticalExperienceTracker::trackBusinessContact($business, $method);
                    $stats['contacts_made']++;
                    
                    // Usually contact only one business
                    if ($this->shouldPerformAction(0.9)) {
                        break;
                    }
                }
                
                // Inter-business browsing delay
                if ($v < $viewCount - 1) {
                    usleep(rand(500000, 1500000)); // 0.5-1.5 seconds
                }
            }
        });
    }

    /**
     * Select a business with bias towards featured/verified
     */
    private function selectBusinessWithBias($businesses)
    {
        // Create weighted selection
        $weighted = [];
        foreach ($businesses as $business) {
            $weight = 1;
            if ($business->is_featured) $weight += 3;
            if ($business->is_verified) $weight += 2;
            
            for ($i = 0; $i < $weight; $i++) {
                $weighted[] = $business;
            }
        }
        
        return $weighted[array_rand($weighted)];
    }

    /**
     * Generate a number following power law distribution
     */
    private function getPowerLawNumber(int $min, int $max, float $alpha = 2.0): int
    {
        $u = mt_rand() / mt_getrandmax();
        $value = $min * pow((1 - $u), -1 / ($alpha - 1));
        return min((int)$value, $max);
    }

    /**
     * Determine if an action should be performed
     */
    private function shouldPerformAction(float $rate): bool
    {
        return (mt_rand() / mt_getrandmax()) < $rate;
    }

    /**
     * Display simulation statistics
     */
    private function displayStats(array $stats)
    {
        $this->newLine();
        $this->info('=== Discovery Simulation Complete ===');
        
        $uniqueViewers = count($stats['unique_viewers']);
        $avgViewsPerViewer = $uniqueViewers > 0 ? 
            round($stats['businesses_viewed'] / $uniqueViewers, 1) : 0;
        
        $this->table(
            ['Metric', 'Count', 'Rate'],
            [
                ['Total Visitors', $stats['visitors'], '100%'],
                ['Started Browsing', $stats['discovery_started'], $this->percentage($stats['discovery_started'], $stats['visitors'])],
                ['Unique Viewers', $uniqueViewers, $this->percentage($uniqueViewers, $stats['visitors'])],
                ['Business Views', $stats['businesses_viewed'], $avgViewsPerViewer . ' per viewer'],
                ['Contacts Made', $stats['contacts_made'], $this->percentage($stats['contacts_made'], $uniqueViewers)],
            ]
        );
        
        // Show popular businesses
        if (!empty($stats['popular_businesses'])) {
            arsort($stats['popular_businesses']);
            $topBusinesses = array_slice($stats['popular_businesses'], 0, 5, true);
            
            $this->newLine();
            $this->info('Top 5 Most Viewed Businesses:');
            
            $rows = [];
            foreach ($topBusinesses as $businessId => $views) {
                $business = Business::find($businessId);
                if ($business) {
                    $rows[] = [
                        $business->business_name,
                        $views,
                        ($business->is_featured ? '★' : '') . ($business->is_verified ? '✓' : '')
                    ];
                }
            }
            
            $this->table(['Business', 'Views', 'Status'], $rows);
        }
        
        $this->newLine();
        $conversionRate = $uniqueViewers > 0 ? 
            round(($stats['contacts_made'] / $uniqueViewers) * 100, 1) : 0;
        $this->info("Overall Conversion Rate: {$conversionRate}%");
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
