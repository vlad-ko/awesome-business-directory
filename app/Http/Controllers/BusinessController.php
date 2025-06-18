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
    public function index()
    {
        $startTime = microtime(true);

        // Start custom transaction for business listing
        $transaction = BusinessLogger::startBusinessTransaction('listing', [
            'page' => 'index',
        ]);

        // Create span for database queries
        $dbSpan = BusinessLogger::createDatabaseSpan('business_queries', 'Fetching businesses for listing');

        // Get all businesses for statistics (before filtering)
        $allBusinesses = Business::all();

        // Get approved businesses for display
        $businesses = Business::approved()
            ->orderedForListing()
            ->get();

        $dbSpan?->setData([
            'total_businesses' => $allBusinesses->count(),
            'approved_businesses' => $businesses->count()
        ]);
        $dbSpan?->finish();

        $responseTime = (microtime(true) - $startTime) * 1000;

        // Set transaction data
        $transaction?->setData([
            'total_businesses' => $allBusinesses->count(),
            'displayed_businesses' => $businesses->count(),
            'response_time_ms' => round($responseTime, 2),
            'is_empty' => $businesses->isEmpty()
        ]);

        // Create span for business logic
        $logicSpan = BusinessLogger::createBusinessSpan('statistics_calculation', [
            'businesses_count' => $businesses->count(),
        ]);

        // Log the listing view with comprehensive statistics
        if ($businesses->isEmpty()) {
            BusinessLogger::emptyStateShown('no_approved_businesses');
            $transaction?->setData(['empty_state' => 'shown']);
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
        return view('businesses.index', compact('businesses'));
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
        //
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
