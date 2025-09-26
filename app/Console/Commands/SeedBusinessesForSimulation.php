<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Business;
use Faker\Factory as Faker;

class SeedBusinessesForSimulation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:simulation-businesses 
                            {--count=20 : Number of businesses to create}
                            {--featured=5 : Number of featured businesses}
                            {--verified=8 : Number of verified businesses}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed businesses for simulation testing';

    /**
     * Industries
     */
    private const INDUSTRIES = [
        'Technology', 'Retail', 'Food & Beverage', 'Healthcare', 
        'Education', 'Entertainment', 'Real Estate', 'Finance',
        'Automotive', 'Beauty & Wellness', 'Sports & Recreation'
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
        $featuredCount = min((int) $this->option('featured'), $count);
        $verifiedCount = min((int) $this->option('verified'), $count);
        
        $this->info("Creating {$count} businesses for simulation...");
        
        $faker = Faker::create();
        $businesses = [];
        
        for ($i = 0; $i < $count; $i++) {
            $business = Business::create([
                'business_name' => $faker->company . ' ' . $faker->companySuffix,
                'business_slug' => $faker->slug,
                'description' => $faker->paragraph(3),
                'tagline' => $faker->catchPhrase,
                'industry' => $faker->randomElement(self::INDUSTRIES),
                'business_type' => $faker->randomElement(self::BUSINESS_TYPES),
                'primary_email' => $faker->companyEmail,
                'phone_number' => $faker->phoneNumber,
                'website_url' => $faker->url,
                'street_address' => $faker->streetAddress,
                'city' => $faker->city,
                'state_province' => $faker->stateAbbr,
                'postal_code' => $faker->postcode,
                'country' => 'USA',
                'owner_name' => $faker->name,
                'owner_email' => $faker->email,
                'status' => 'approved',
                'is_featured' => false,
                'is_verified' => false,
                'founded_date' => $faker->dateTimeBetween('-10 years', '-1 year'),
                'registration_number' => strtoupper($faker->bothify('REG###???')),
            ]);
            
            $businesses[] = $business;
            
            if (($i + 1) % 5 === 0) {
                $this->info("Created " . ($i + 1) . " businesses...");
            }
        }
        
        // Randomly select featured businesses
        $featuredIndices = array_rand($businesses, $featuredCount);
        if (!is_array($featuredIndices)) {
            $featuredIndices = [$featuredIndices];
        }
        
        foreach ($featuredIndices as $index) {
            $businesses[$index]->update(['is_featured' => true]);
        }
        
        // Randomly select verified businesses
        $verifiedIndices = array_rand($businesses, $verifiedCount);
        if (!is_array($verifiedIndices)) {
            $verifiedIndices = [$verifiedIndices];
        }
        
        foreach ($verifiedIndices as $index) {
            $businesses[$index]->update(['is_verified' => true]);
        }
        
        $this->newLine();
        $this->info("✅ Created {$count} businesses successfully!");
        $this->info("   - Featured: {$featuredCount}");
        $this->info("   - Verified: {$verifiedCount}");
        $this->newLine();
        
        // Show some examples
        $this->info('Sample businesses created:');
        $sampleBusinesses = array_slice($businesses, 0, 5);
        $tableData = array_map(function ($b) {
            return [
                $b->business_name,
                $b->industry,
                ($b->is_featured ? '★ ' : '') . ($b->is_verified ? '✓' : '')
            ];
        }, $sampleBusinesses);
        
        $this->table(['Name', 'Industry', 'Status'], $tableData);
        
        return 0;
    }
}
