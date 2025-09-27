<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
        
        // Add Sentry distributed tracing middleware
        $middleware->web(append: [
            \App\Http\Middleware\SentryTracingMiddleware::class,
        ]);
        
        // Configure auth middleware to redirect to admin login for admin routes
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('admin/*')) {
                return route('admin.login');
            }
            return route('login'); // Default login route (if we had one)
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Run realistic traffic simulation every hour
        $schedule->command('simulate:all --realistic --discovery-count=50 --onboarding-count=20')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/simulation.log'));
            
        // Optional: Run smaller simulation every 15 minutes during business hours
        $schedule->command('simulate:discovery --count=20 --view-rate=0.5 --contact-rate=0.2')
            ->everyFifteenMinutes()
            ->between('9:00', '17:00')
            ->weekdays()
            ->withoutOverlapping()
            ->runInBackground();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);
    })->create();
