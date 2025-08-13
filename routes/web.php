<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusinessOnboardingController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;

Route::get('/', function (Illuminate\Http\Request $request) {
    // Use modern Sentry pattern for welcome page
    return \App\Services\SentryLogger::trackBusinessOperation('welcome_page_view', [
        'referrer' => $request->header('referer'),
        'user_agent' => $request->userAgent(),
    ], function ($span) use ($request) {
        $startTime = microtime(true);
        
        // Log welcome page view
        \App\Services\BusinessLogger::welcomePageViewed($request, null);
        
        // Render the view
        $response = response()->view('welcome');
        
        // Calculate response time and log performance
        $responseTime = (microtime(true) - $startTime) * 1000;
        \App\Services\BusinessLogger::performanceMetric('welcome_page_render', $responseTime, [
            'view' => 'welcome',
            'has_referrer' => !empty($request->header('referer')),
        ]);
        
        return $response;
    });
})->name('welcome');

Route::get('/onboard', [BusinessOnboardingController::class, 'create'])->name('business.onboard');
Route::post('/onboard', [BusinessOnboardingController::class, 'store'])->name('business.store');

// Multi-step onboarding routes
Route::get('/onboard/step/{step}', [BusinessOnboardingController::class, 'showStep'])->name('business.onboard.step')->where('step', '[1-4]');
Route::post('/onboard/step/{step}', [BusinessOnboardingController::class, 'storeStep'])->name('business.onboard.step.store')->where('step', '[1-4]');
Route::get('/onboard/review', [BusinessOnboardingController::class, 'review'])->name('business.onboard.review');
Route::post('/onboard/submit', [BusinessOnboardingController::class, 'submit'])->name('business.onboard.submit');
Route::get('/onboard/success', [BusinessOnboardingController::class, 'success'])->name('business.onboard.success');

Route::get('/businesses', [BusinessController::class, 'index'])->name('businesses.index');
Route::get('/business/{business}', [BusinessController::class, 'show'])->name('business.show');

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Authentication routes (accessible to guests)
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.store');

    // Protected admin routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Business management routes
        Route::prefix('businesses')->name('businesses.')->group(function () {
            Route::get('/{business}', [AdminDashboardController::class, 'show'])->name('show');
            Route::patch('/{business}/approve', [AdminDashboardController::class, 'approve'])->name('approve');
            Route::patch('/{business}/reject', [AdminDashboardController::class, 'reject'])->name('reject');
            Route::patch('/{business}/toggle-featured', [AdminDashboardController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::patch('/{business}/toggle-verified', [AdminDashboardController::class, 'toggleVerified'])->name('toggle-verified');
        });
    });
});
