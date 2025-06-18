<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard with pending businesses.
     */
    public function index()
    {
        $startTime = microtime(true);

        // Start custom transaction for admin dashboard
        $transaction = \App\Services\BusinessLogger::startBusinessTransaction('admin_dashboard', [
            'admin_user' => auth()->user()->name,
        ]);

        // Create span for database queries
        $dbSpan = \App\Services\BusinessLogger::createDatabaseSpan('admin_queries', 'Fetching admin dashboard data');

        $pendingBusinesses = Business::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $statistics = [
            'pending' => Business::where('status', 'pending')->count(),
            'approved' => Business::where('status', 'approved')->count(),
            'rejected' => Business::where('status', 'rejected')->count(),
            'total' => Business::count(),
        ];

        $dbSpan?->setData([
            'pending_count' => $statistics['pending'],
            'total_count' => $statistics['total']
        ]);
        $dbSpan?->finish();

        $responseTime = (microtime(true) - $startTime) * 1000;

        // Set transaction data
        $transaction?->setData([
            'pending_businesses' => $statistics['pending'],
            'total_businesses' => $statistics['total'],
            'response_time_ms' => round($responseTime, 2),
            'admin_workload' => $statistics['pending'] > 10 ? 'high' : 'normal'
        ]);

        $transaction?->finish();
        return view('admin.dashboard', compact('pendingBusinesses', 'statistics'));
    }

    /**
     * Display the specified business for review.
     */
    public function show(Business $business)
    {
        return view('admin.businesses.show', compact('business'));
    }

    /**
     * Approve a pending business.
     */
    public function approve(Business $business)
    {
        // Start custom transaction for business approval
        $transaction = \App\Services\BusinessLogger::startBusinessTransaction('approve_business', [
            'business_id' => $business->id,
            'admin_user' => auth()->user()->name,
        ]);

        if ($business->status !== 'pending') {
            $transaction?->setData([
                'status' => 'error',
                'error_reason' => 'not_pending'
            ]);
            $transaction?->finish();
            return redirect()->route('admin.dashboard')
                ->with('error', 'Business is not pending approval.');
        }

        // Create span for database update
        $dbSpan = \App\Services\BusinessLogger::createDatabaseSpan('business_approval', 'Updating business status to approved');

        $business->update(['status' => 'approved']);

        $dbSpan?->finish();

        $transaction?->setData([
            'status' => 'success',
            'business_name' => $business->business_name,
            'business_industry' => $business->industry
        ]);
        $transaction?->finish();

        return redirect()->route('admin.dashboard')
            ->with('success', 'Business approved successfully!');
    }

    /**
     * Reject a pending business.
     */
    public function reject(Request $request, Business $business)
    {
        if ($business->status !== 'pending') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Business is not pending approval.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $business->update([
            'status' => 'rejected',
            // Note: In a real app, you might want to add a rejection_reason field to the businesses table
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Business rejected successfully!');
    }

    /**
     * Toggle the featured status of a business.
     */
    public function toggleFeatured(Business $business)
    {
        $business->update([
            'is_featured' => !$business->is_featured,
        ]);

        $status = $business->is_featured ? 'featured' : 'unfeatured';
        
        return redirect()->back()
            ->with('success', 'Featured status updated successfully!');
    }

    /**
     * Toggle the verified status of a business.
     */
    public function toggleVerified(Business $business)
    {
        $isVerified = !$business->is_verified;
        
        $business->update([
            'is_verified' => $isVerified,
            'verified_at' => $isVerified ? now() : null,
        ]);

        return redirect()->back()
            ->with('success', 'Verified status updated successfully!');
    }
}
