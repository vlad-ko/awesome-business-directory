<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Services\BusinessLogger;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $startTime = microtime(true);
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

        // Start custom transaction for business listing
        $transaction = BusinessLogger::startBusinessTransaction('listing', [
            'page' => 'index',
            'from_welcome_cta' => $fromWelcomeCta,
            'search_term' => $searchTerm,
        ]);

        // Create span for database queries
        $dbSpan = BusinessLogger::createDatabaseSpan('business_queries', 'Fetching businesses for listing');

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

        $dbSpan?->setData([
            'total_businesses' => $allBusinesses->count(),
            'approved_businesses' => $businesses->count(),
            'search_term' => $searchTerm,
            'has_search' => !empty($searchTerm)
        ]);
        $dbSpan?->finish();

        $responseTime = (microtime(true) - $startTime) * 1000;

        // Set transaction data
        $transaction?->setData([
            'total_businesses' => $allBusinesses->count(),
            'displayed_businesses' => $businesses->count(),
            'response_time_ms' => round($responseTime, 2),
            'is_empty' => $businesses->isEmpty(),
            'search_term' => $searchTerm,
            'has_search' => !empty($searchTerm)
        ]);

        // Create span for business logic
        $logicSpan = BusinessLogger::createBusinessSpan('statistics_calculation', [
            'businesses_count' => $businesses->count(),
            'search_term' => $searchTerm,
        ]);

        // Log the listing view with comprehensive statistics
        if ($businesses->isEmpty()) {
            $emptyReason = $searchTerm ? 'no_search_results' : 'no_approved_businesses';
            BusinessLogger::emptyStateShown($emptyReason);
            $transaction?->setData(['empty_state' => 'shown', 'empty_reason' => $emptyReason]);
        } else {
            BusinessLogger::listingViewed($allBusinesses, $responseTime);
        }

        $logicSpan?->finish();

        // Log performance if it's slow
        if ($responseTime > 500) {
            BusinessLogger::slowQuery('business_listing', $responseTime);
            $transaction?->setData(['performance_issue' => 'slow_response']);
        }

        // Log performance metric
        BusinessLogger::performanceMetric('business_listing_load', $responseTime, [
            'total_businesses' => $allBusinesses->count(),
            'displayed_businesses' => $businesses->count(),
        ]);

        $transaction?->finish();
        return view('businesses.index', compact('businesses', 'featuredBusinesses', 'searchTerm'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Business $business)
    {
        $startTime = microtime(true);

        // Start custom transaction for business detail view
        $transaction = BusinessLogger::startBusinessTransaction('business_detail', [
            'business_id' => $business->id,
            'business_slug' => $business->business_slug,
        ]);

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
                'reason' => 'not_approved',
            ]);
            abort(404);
        }

        // Create span for business logic
        $logicSpan = BusinessLogger::createBusinessSpan('business_detail_view', [
            'business_id' => $business->id,
            'business_name' => $business->business_name,
            'industry' => $business->industry,
        ]);

        $responseTime = (microtime(true) - $startTime) * 1000;

        // Set transaction data
        $transaction?->setData([
            'business_id' => $business->id,
            'business_name' => $business->business_name,
            'industry' => $business->industry,
            'is_featured' => $business->is_featured,
            'is_verified' => $business->is_verified,
            'response_time_ms' => round($responseTime, 2),
        ]);

        // Log business detail view
        BusinessLogger::userInteraction('business_detail_viewed', [
            'business_id' => $business->id,
            'business_name' => $business->business_name,
            'business_slug' => $business->business_slug,
            'industry' => $business->industry,
        ]);

        $logicSpan?->finish();
        $transaction?->finish();

        return view('businesses.show', compact('business'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Business $business)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Business $business)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Business $business)
    {
        //
    }
}
