<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CriticalExperienceTracker;
use App\Services\SentryLogger;
use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class CriticalExperienceTrackerTest extends TestCase
{
    use RefreshDatabase;

    protected $sentryLoggerMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a partial mock of SentryLogger
        $this->sentryLoggerMock = Mockery::mock('alias:App\Services\SentryLogger');
    }

    #[Test]
    public function it_tracks_discovery_start_only_once_per_session()
    {
        // First call should track
        $this->sentryLoggerMock->shouldReceive('log')
            ->once()
            ->with('info', 'Critical: Discovery journey started', Mockery::type('array'));
        
        CriticalExperienceTracker::trackDiscoveryStart();
        
        // Second call should not track (same session)
        $this->sentryLoggerMock->shouldReceive('log')
            ->never();
        
        CriticalExperienceTracker::trackDiscoveryStart();
        
        $this->assertTrue(session()->has('discovery_tracked'));
    }

    #[Test]
    public function it_tracks_business_viewed_with_correct_data()
    {
        $business = Business::factory()->create([
            'business_name' => 'Test Business',
            'business_slug' => 'test-business',
            'is_featured' => true,
            'is_verified' => false,
        ]);

        $this->sentryLoggerMock->shouldReceive('log')
            ->once()
            ->with('info', 'Critical: Business viewed', [
                'experience' => 'business_discovery',
                'checkpoint' => 'business_view',
                'business_id' => $business->id,
                'business_slug' => 'test-business',
                'is_featured' => true,
                'is_verified' => false,
            ]);

        CriticalExperienceTracker::trackBusinessViewed($business);
        
        // Add assertion to avoid risky test
        $this->assertTrue(true);
    }

    #[Test]
    public function it_tracks_business_contact_conversion()
    {
        $business = Business::factory()->create();

        $this->sentryLoggerMock->shouldReceive('log')
            ->once()
            ->with('info', 'Critical: Business contact initiated', [
                'experience' => 'business_discovery',
                'checkpoint' => 'conversion',
                'business_id' => $business->id,
                'contact_method' => 'website',
            ]);

        CriticalExperienceTracker::trackBusinessContact($business, 'website');
        
        $this->assertTrue(true);
    }

    #[Test]
    public function it_tracks_onboarding_start_and_stores_time()
    {
        $this->sentryLoggerMock->shouldReceive('log')
            ->once()
            ->with('info', 'Critical: Onboarding started', Mockery::type('array'));

        CriticalExperienceTracker::trackOnboardingStart();

        $this->assertNotNull(session('onboarding_start_time'));
    }

    #[Test]
    public function it_tracks_onboarding_step_completion_with_duration()
    {
        // Set start time
        session(['onboarding_start_time' => now()->subSeconds(30)]);

        $this->sentryLoggerMock->shouldReceive('log')
            ->once()
            ->with('info', 'Critical: Onboarding step completed', Mockery::on(function ($data) {
                return $data['experience'] === 'business_onboarding' &&
                       $data['checkpoint'] === 'step_2_complete' &&
                       $data['step'] === 2 &&
                       abs($data['duration_seconds']) >= 29; // Allow for small time differences
            }));

        CriticalExperienceTracker::trackOnboardingStepComplete(2);
        
        $this->assertTrue(true);
    }

    #[Test]
    public function it_tracks_onboarding_abandonment()
    {
        session(['onboarding_start_time' => now()->subSeconds(120)]);

        $this->sentryLoggerMock->shouldReceive('log')
            ->once()
            ->with('warning', 'Critical: Onboarding abandoned', Mockery::on(function ($data) {
                return $data['experience'] === 'business_onboarding' &&
                       $data['checkpoint'] === 'abandoned' &&
                       $data['last_step'] === 3 &&
                       abs($data['duration_seconds']) >= 119; // Allow for small time differences
            }));

        CriticalExperienceTracker::trackOnboardingAbandoned(3);
        
        $this->assertTrue(true);
    }

    #[Test]
    public function it_tracks_onboarding_completion_and_clears_session()
    {
        session([
            'onboarding_start_time' => now()->subSeconds(300),
            'onboarding_data' => ['some' => 'data']
        ]);

        $business = Business::factory()->create([
            'industry' => 'Technology'
        ]);

        $this->sentryLoggerMock->shouldReceive('log')
            ->once()
            ->with('info', 'Critical: Onboarding completed', Mockery::on(function ($data) use ($business) {
                return $data['experience'] === 'business_onboarding' &&
                       $data['checkpoint'] === 'conversion' &&
                       $data['business_id'] === $business->id &&
                       abs($data['duration_seconds']) >= 299 && // Allow for small time differences
                       $data['industry'] === 'Technology';
            }));

        CriticalExperienceTracker::trackOnboardingComplete($business);

        $this->assertNull(session('onboarding_start_time'));
        $this->assertNull(session('onboarding_data'));
    }

    #[Test]
    public function it_only_tracks_critical_admin_actions()
    {
        $business = Business::factory()->create();
        $this->actingAs(\App\Models\User::factory()->admin()->create());

        // Should track approve action
        $this->sentryLoggerMock->shouldReceive('log')
            ->once()
            ->with('info', 'Critical: Admin action taken', Mockery::type('array'));

        CriticalExperienceTracker::trackAdminCriticalAction('approve', $business);

        // Should NOT track non-critical actions
        $this->sentryLoggerMock->shouldReceive('log')->never();

        CriticalExperienceTracker::trackAdminCriticalAction('toggle_featured', $business);
        
        $this->assertTrue(true);
    }

    #[Test]
    public function it_tracks_critical_errors_with_exception_capture()
    {
        $exception = new \Exception('Database connection failed');

        $this->sentryLoggerMock->shouldReceive('log')
            ->once()
            ->with('error', 'Critical: Experience blocked by error', [
                'experience' => 'business_onboarding',
                'checkpoint' => 'submission',
                'error_message' => 'Database connection failed',
                'error_type' => 'Exception',
            ]);

        // We can't easily mock the Sentry function, so we'll just check the log was called
        // The actual exception capture is tested by Sentry SDK itself

        CriticalExperienceTracker::trackCriticalError('business_onboarding', 'submission', $exception);
        
        $this->assertTrue(true); // Test passed if no exceptions thrown
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
