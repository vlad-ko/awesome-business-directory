<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use App\Services\BusinessLogger;
use App\Services\SentryLogger;

class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $searchTerm = $request->get('search');
        
        // Track if user came from welcome page CTA
        $referrer = $request->header('referer');
        $fromWelcomeCta = false;
        if ($referrer && str_contains($referrer, request()->getSchemeAndHttpHost())) {
            $path = parse_url($referrer, PHP_URL_PATH);
            if ($path === '/') {
                BusinessLogger::welcomeCtaClicked('explore_businesses', $request);
                $fromWelcomeCta = true;
            }
        }

        // Use modern Sentry pattern for tracking business listing
        return SentryLogger::trackBusinessOperation('listing', [
            'page' => 'index',
            'from_welcome_cta' => $fromWelcomeCta,
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

            // Log listing viewed
            BusinessLogger::listingViewed($allBusinesses, $responseTime);

            // Log search if performed
            if ($searchTerm) {
                BusinessLogger::businessSearched([
                    'search_term' => $searchTerm,
                    'search_type' => 'business_name_or_description',
                ], $businesses->count(), $responseTime);
            }

            // Check if we should show empty state
            if ($businesses->isEmpty() && $allBusinesses->isEmpty()) {
                BusinessLogger::emptyStateShown('no_businesses_at_all');
            } elseif ($businesses->isEmpty() && $allBusinesses->isNotEmpty()) {
                BusinessLogger::emptyStateShown($searchTerm ? 'no_search_results' : 'no_approved_businesses');
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

            // Performance metrics
            if ($responseTime > 1000) {
                BusinessLogger::slowQuery('business_listing', $responseTime, 'Multiple queries for business listing page');
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

            // Log business detail access attempt
            BusinessLogger::userInteraction('business_detail_access_attempt', [
                'business_id' => $business->id,
                'business_slug' => $business->business_slug,
                'business_name' => $business->business_name,
                'business_status' => $business->status,
                'request_url' => request()->url(),
                'user_ip' => request()->ip(),
            ]);

            // Only show approved businesses to the public
            if ($business->status !== 'approved') {
                BusinessLogger::userInteraction('business_detail_access_denied', [
                    'business_id' => $business->id,
                    'business_slug' => $business->business_slug,
                    'business_status' => $business->status,
                    'reason' => 'business_not_approved',
                ]);

                abort(404);
            }

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

            // Log successful business detail view
            BusinessLogger::userInteraction('business_detail_viewed', [
                'business_id' => $business->id,
                'business_slug' => $business->business_slug,
                'business_name' => $business->business_name,
                'industry' => $business->industry,
                'business_type' => $business->business_type,
                'is_featured' => $business->is_featured,
                'is_verified' => $business->is_verified,
                'response_time_ms' => $responseTime,
                'related_businesses_count' => $relatedBusinesses->count(),
            ]);

            // Performance tracking
            BusinessLogger::performanceMetric('business_detail_page', $responseTime, [
                'business_id' => $business->id,
                'related_count' => $relatedBusinesses->count(),
            ]);

            return response()->view('businesses.show', compact('business', 'relatedBusinesses'));
        });
    }
}