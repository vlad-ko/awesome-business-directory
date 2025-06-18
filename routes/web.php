<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusinessOnboardingController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/onboard', [BusinessOnboardingController::class, 'create'])->name('business.onboard');
Route::post('/onboard', [BusinessOnboardingController::class, 'store'])->name('business.store');

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
