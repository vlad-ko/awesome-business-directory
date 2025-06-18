<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some pending businesses
        Business::factory()->count(5)->create([
            'status' => 'pending'
        ]);

        // Create some approved businesses
        Business::factory()->count(10)->approved()->create();

        // Create some featured businesses
        Business::factory()->count(3)->approved()->featured()->create();

        // Create some verified businesses
        Business::factory()->count(2)->approved()->verified()->create();

        // Create one rejected business
        Business::factory()->count(1)->rejected()->create();

        $this->command->info('Sample businesses created successfully!');
        $this->command->info('- 5 pending businesses');
        $this->command->info('- 10 approved businesses');
        $this->command->info('- 3 featured businesses');
        $this->command->info('- 2 verified businesses');
        $this->command->info('- 1 rejected business');
    }
}
