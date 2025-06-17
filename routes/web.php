<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusinessOnboardingController;
use App\Http\Controllers\BusinessController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/onboard', [BusinessOnboardingController::class, 'create'])->name('business.onboard');
Route::post('/onboard', [BusinessOnboardingController::class, 'store'])->name('business.store');

Route::get('/businesses', [BusinessController::class, 'index'])->name('businesses.index');
Route::get('/business/{business}', [BusinessController::class, 'show'])->name('business.show');
