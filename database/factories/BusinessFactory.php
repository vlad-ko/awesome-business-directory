<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Business>
 */
class BusinessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $businessName = fake()->company();
        
        return [
            'business_name' => $businessName,
            'business_slug' => \Illuminate\Support\Str::slug($businessName),
            'description' => fake()->paragraph(3),
            'tagline' => fake()->catchPhrase(),
            'industry' => fake()->randomElement([
                'Technology', 'Healthcare', 'Finance', 'Retail', 'Manufacturing',
                'Education', 'Real Estate', 'Food & Beverage', 'Transportation',
                'Professional Services', 'Construction', 'Entertainment'
            ]),
            'business_type' => fake()->randomElement(['Corporation', 'LLC', 'Partnership', 'Sole Proprietorship']),
            'founded_date' => fake()->dateTimeBetween('-20 years', '-1 year')->format('Y-m-d'),
            'registration_number' => fake()->optional()->numerify('REG-#########'),
            'primary_email' => fake()->companyEmail(),
            'phone_number' => fake()->phoneNumber(),
            'website_url' => fake()->optional()->url(),
            'street_address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state_province' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => fake()->country(),
            'latitude' => fake()->optional()->latitude(),
            'longitude' => fake()->optional()->longitude(),
            'logo_path' => fake()->optional()->imageUrl(200, 200, 'business'),
            'business_hours' => json_encode([
                'monday' => ['open' => '09:00', 'close' => '17:00'],
                'tuesday' => ['open' => '09:00', 'close' => '17:00'],
                'wednesday' => ['open' => '09:00', 'close' => '17:00'],
                'thursday' => ['open' => '09:00', 'close' => '17:00'],
                'friday' => ['open' => '09:00', 'close' => '17:00'],
                'saturday' => ['open' => '10:00', 'close' => '15:00'],
                'sunday' => ['closed' => true]
            ]),
            'services_offered' => json_encode(fake()->words(rand(3, 6))),
            'employee_count' => fake()->optional()->numberBetween(1, 500),
            'facebook_url' => fake()->optional()->url(),
            'twitter_url' => fake()->optional()->url(),
            'instagram_url' => fake()->optional()->url(),
            'linkedin_url' => fake()->optional()->url(),
            'status' => 'pending',
            'is_verified' => false,
            'is_featured' => false,
            'verified_at' => null,
            'owner_name' => fake()->name(),
            'owner_email' => fake()->safeEmail(),
            'owner_phone' => fake()->optional()->phoneNumber(),
        ];
    }

    /**
     * Indicate that the business should be approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the business should be rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }

    /**
     * Indicate that the business should be featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the business should be verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }
}
