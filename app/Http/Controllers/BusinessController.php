<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use App\Services\BusinessLogger;
use App\Services\SentryLogger;
use App\Services\CriticalExperienceTracker;

class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $searchTerm = $request->get('search');
        
        // Track critical discovery start
        CriticalExperienceTracker::trackDiscoveryStart();

        // Use modern Sentry pattern for tracking business listing
        return SentryLogger::trackBusinessOperation('listing', [
            'page' => 'index',
            'search_term' => $searchTerm,
        ], function ($span) use ($request, $searchTerm) {
            $startTime = microtime(true);
            
            // Track database operations
            $result = SentryLogger::trackDatabaseOperation('business_queries', function ($dbSpan) use ($searchTerm) {
                // Get all businesses for statistics (before filtering)
                $allBusinesses = Business::all();

                // Get featured businesses for the featured section (also apply search if provided)
                $featuredBusinessesQuery = Business::approved()->where('is_featured', true);
                if ($searchTerm) {
                    $featuredBusinessesQuery->where(function($query) use ($searchTerm) {
                        $query->where('business_name', 'LIKE', '%' . $searchTerm . '%')
                              ->orWhere('description', 'LIKE', '%' . $searchTerm . '%');
                    });
                }
                $featuredBusinesses = $featuredBusinessesQuery->orderBy('business_name')->get();

                // Get approved businesses for display
                $businessesQuery = Business::approved();
                if ($searchTerm) {
                    $businessesQuery->where(function($query) use ($searchTerm) {
                        $query->where('business_name', 'LIKE', '%' . $searchTerm . '%')
                              ->orWhere('description', 'LIKE', '%' . $searchTerm . '%');
                    });
                }
                $businesses = $businessesQuery->orderedForListing()->get();

                return [
                    'allBusinesses' => $allBusinesses,
                    'featuredBusinesses' => $featuredBusinesses,
                    'businesses' => $businesses,
                ];
            });

            $allBusinesses = $result['allBusinesses'];
            $featuredBusinesses = $result['featuredBusinesses'];
            $businesses = $result['businesses'];

            $responseTime = (microtime(true) - $startTime) * 1000;

            // Only track critical errors for empty states
            if ($businesses->isEmpty() && $searchTerm) {
                // This might indicate a problem if users consistently can't find what they're looking for
                CriticalExperienceTracker::trackCriticalError(
                    'business_discovery',
                    'search_no_results',
                    new \Exception('Search returned no results'),
                    ['search_term' => $searchTerm]
                );
            }

            // Calculate statistics for view
            $statistics = [
                'total' => $allBusinesses->count(),
                'approved' => $allBusinesses->where('status', 'approved')->count(),
                'pending' => $allBusinesses->where('status', 'pending')->count(),
                'featured' => $featuredBusinesses->count(),
                'verified' => $allBusinesses->where('is_verified', true)->count(),
            ];

            // Industry distribution
            $industryDistribution = $allBusinesses->groupBy('industry')->map->count();

            // Business type distribution
            $typeDistribution = $allBusinesses->groupBy('business_type')->map->count();

            // Location distribution
            $locationDistribution = $allBusinesses->groupBy(function ($business) {
                return $business->city . ', ' . $business->state_province;
            })->map->count();

            // Only log critical performance issues
            if ($responseTime > 3000) {
                CriticalExperienceTracker::trackCriticalError(
                    'business_discovery',
                    'listing_slow',
                    new \Exception('Listing page load exceeded 3 seconds'),
                    ['response_time_ms' => $responseTime]
                );
            }

            return response()->view('businesses.index', compact(
                'businesses',
                'featuredBusinesses',
                'statistics',
                'industryDistribution',
                'typeDistribution',
                'locationDistribution',
                'searchTerm'
            ));
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Business $business)
    {
        // Use modern Sentry pattern for tracking business detail view
        return SentryLogger::trackBusinessOperation('business_detail', [
            'business_id' => $business->id,
            'business_slug' => $business->business_slug,
        ], function ($span) use ($business) {
            $startTime = microtime(true);

            // Only show approved businesses to the public
            if ($business->status !== 'approved') {
                abort(404);
            }
            
            // Track critical business view
            CriticalExperienceTracker::trackBusinessViewed($business);

            // Track related businesses query
            $relatedBusinesses = SentryLogger::trackDatabaseOperation('related_businesses_query', function ($dbSpan) use ($business) {
                return Business::approved()
                    ->where('id', '!=', $business->id)
                    ->where(function ($query) use ($business) {
                        $query->where('industry', $business->industry)
                              ->orWhere('business_type', $business->business_type);
                    })
                    ->take(4)
                    ->get();
            });

            $responseTime = (microtime(true) - $startTime) * 1000;

            // Only log critical performance issues
            if ($responseTime > 3000) {
                CriticalExperienceTracker::trackCriticalError(
                    'business_discovery',
                    'detail_slow',
                    new \Exception('Business detail page load exceeded 3 seconds'),
                    ['response_time_ms' => $responseTime, 'business_id' => $business->id]
                );
            }

            return response()->view('businesses.show', compact('business', 'relatedBusinesses'));
        });
    }
}