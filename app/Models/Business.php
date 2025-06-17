<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_name',
        'business_slug',
        'description',
        'tagline',
        'industry',
        'business_type',
        'founded_date',
        'registration_number',
        'primary_email',
        'phone_number',
        'website_url',
        'street_address',
        'city',
        'state_province',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'logo_path',
        'business_hours',
        'services_offered',
        'employee_count',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'linkedin_url',
        'status',
        'is_verified',
        'is_featured',
        'verified_at',
        'owner_name',
        'owner_email',
        'owner_phone',
    ];

    protected $casts = [
        'founded_date' => 'date',
        'business_hours' => 'array',
        'services_offered' => 'array',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($business) {
            $baseSlug = Str::slug($business->business_name);
            $slug = $baseSlug;
            $counter = 1;

            // Get all existing slugs that start with the base slug
            $existingSlugs = static::where('business_slug', 'LIKE', $baseSlug . '%')
                ->pluck('business_slug')
                ->toArray();

            // If there are existing slugs, find the next available number
            if (!empty($existingSlugs)) {
                while (in_array($slug, $existingSlugs)) {
                    $counter++;
                    $slug = $baseSlug . '-' . $counter;
                }
            }

            $business->business_slug = $slug;
            $business->status = 'pending';
        });
    }

    protected static function generateUniqueSlug($name)
    {
        $originalSlug = Str::slug($name);
        $slug = $originalSlug;
        $count = 1;

        while (static::where('business_slug', $slug)->exists()) {
            $count++;
            $slug = $originalSlug . '-' . $count;
        }

        return $slug;
    }
}
