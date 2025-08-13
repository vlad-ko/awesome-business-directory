<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\BusinessLogger;
use App\Services\SentryLogger;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Use modern Sentry pattern for tracking admin dashboard
        return SentryLogger::trackBusinessOperation('admin_dashboard', [
            'admin_user' => auth()->user()->name,
        ], function ($span) {
            $startTime = microtime(true);

            // Track database operations for admin dashboard
            $result = SentryLogger::trackDatabaseOperation('admin_queries', function ($dbSpan) {
                $pendingBusinesses = Business::where('status', 'pending')
                    ->orderBy('created_at', 'desc')
                    ->get();

                $statistics = [
                    'pending' => Business::where('status', 'pending')->count(),
                    'approved' => Business::where('status', 'approved')->count(),
                    'rejected' => Business::where('status', 'rejected')->count(),
                    'total' => Business::count(),
                ];

                return [
                    'pendingBusinesses' => $pendingBusinesses,
                    'statistics' => $statistics,
                ];
            });

            $pendingBusinesses = $result['pendingBusinesses'];
            $statistics = $result['statistics'];
            
            $responseTime = (microtime(true) - $startTime) * 1000;

            // Log admin dashboard metrics
            BusinessLogger::userInteraction('admin_dashboard_viewed', [
                'admin_user' => auth()->user()->name,
                'pending_count' => $statistics['pending'],
                'total_count' => $statistics['total'],
                'response_time_ms' => $responseTime,
                'admin_workload' => $statistics['pending'] > 10 ? 'high' : 'normal',
            ]);

            return view('admin.dashboard', compact('pendingBusinesses', 'statistics'));
        });
    }

    public function show(Business $business)
    {
        // Use modern Sentry pattern for tracking admin business view
        return SentryLogger::trackBusinessOperation('admin_business_detail', [
            'business_id' => $business->id,
            'admin_user' => auth()->user()->name,
        ], function ($span) use ($business) {
            // Log admin viewing business details
            BusinessLogger::userInteraction('admin_business_detail_viewed', [
                'business_id' => $business->id,
                'business_name' => $business->business_name,
                'business_status' => $business->status,
                'admin_user' => auth()->user()->name,
            ]);

            return view('admin.businesses.show', compact('business'));
        });
    }

    public function approve(Business $business)
    {
        // Use modern Sentry pattern for tracking business approval
        return SentryLogger::trackBusinessOperation('admin_business_approve', [
            'business_id' => $business->id,
            'admin_user' => auth()->user()->name,
        ], function ($span) use ($business) {
            $startTime = microtime(true);

            try {
                $business->update(['status' => 'approved']);

                $processingTime = (microtime(true) - $startTime) * 1000;

                // Log successful approval
                BusinessLogger::userInteraction('admin_business_approved', [
                    'business_id' => $business->id,
                    'business_name' => $business->business_name,
                    'admin_user' => auth()->user()->name,
                    'processing_time_ms' => $processingTime,
                ]);

                return redirect()->route('admin.dashboard')
                    ->with('success', 'Business approved successfully!');

            } catch (\Exception $e) {
                // Capture exception with context
                \Sentry\captureException($e, [
                    'tags' => [
                        'component' => 'admin_dashboard',
                        'action' => 'business_approval',
                    ],
                    'extra' => [
                        'business_id' => $business->id,
                        'admin_user' => auth()->user()->name,
                    ],
                ]);

                BusinessLogger::applicationError($e, 'admin_business_approval_failed', [
                    'business_id' => $business->id,
                ]);

                return redirect()->back()
                    ->with('error', 'Failed to approve business. Please try again.');
            }
        });
    }

    public function reject(Business $business)
    {
        // Use modern Sentry pattern for tracking business rejection
        return SentryLogger::trackBusinessOperation('admin_business_reject', [
            'business_id' => $business->id,
            'admin_user' => auth()->user()->name,
        ], function ($span) use ($business) {
            $business->update(['status' => 'rejected']);

            // Log rejection
            BusinessLogger::userInteraction('admin_business_rejected', [
                'business_id' => $business->id,
                'business_name' => $business->business_name,
                'admin_user' => auth()->user()->name,
            ]);

            return redirect()->route('admin.dashboard')
                ->with('success', 'Business rejected successfully!');
        });
    }

    public function toggleFeatured(Business $business)
    {
        // Use modern Sentry pattern for tracking featured toggle
        return SentryLogger::trackBusinessOperation('admin_toggle_featured', [
            'business_id' => $business->id,
            'admin_user' => auth()->user()->name,
        ], function ($span) use ($business) {
            $business->update(['is_featured' => !$business->is_featured]);

            $action = $business->is_featured ? 'featured' : 'unfeatured';

            // Log feature toggle
            BusinessLogger::userInteraction('admin_business_feature_toggled', [
                'business_id' => $business->id,
                'business_name' => $business->business_name,
                'admin_user' => auth()->user()->name,
                'action' => $action,
                'is_featured' => $business->is_featured,
            ]);

            return redirect()->back()
                ->with('success', "Business {$action} successfully!");
        });
    }

    public function toggleVerified(Business $business)
    {
        // Use modern Sentry pattern for tracking verification toggle
        return SentryLogger::trackBusinessOperation('admin_toggle_verified', [
            'business_id' => $business->id,
            'admin_user' => auth()->user()->name,
        ], function ($span) use ($business) {
            $business->update(['is_verified' => !$business->is_verified]);

            $action = $business->is_verified ? 'verified' : 'unverified';

            // Log verification toggle
            BusinessLogger::userInteraction('admin_business_verification_toggled', [
                'business_id' => $business->id,
                'business_name' => $business->business_name,
                'admin_user' => auth()->user()->name,
                'action' => $action,
                'is_verified' => $business->is_verified,
            ]);

            return redirect()->back()
                ->with('success', "Business {$action} successfully!");
        });
    }
}