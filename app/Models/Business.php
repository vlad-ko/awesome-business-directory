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
            // Generate unique slug
            $business->business_slug = static::generateUniqueSlug($business->business_name);
            
            // Only set status to pending if not already set
            if (empty($business->status)) {
                $business->status = 'pending';
            }
        });
    }

    protected static function generateUniqueSlug($name)
    {
        $originalSlug = Str::slug($name);
        $slug = $originalSlug;
        $count = 2; // Start from 2 for the second occurrence
        
        // Check if the original slug exists
        while (static::where('business_slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    /**
     * Scope a query to only include approved businesses.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to order businesses by featured first, then alphabetically.
     */
    public function scopeOrderedForListing($query)
    {
        return $query->orderByDesc('is_featured')->orderBy('business_name');
    }
}
