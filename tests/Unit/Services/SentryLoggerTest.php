<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\SentryLogger;
use PHPUnit\Framework\Attributes\Test;

class SentryLoggerTest extends TestCase
{
    #[Test]
    public function it_can_log_messages_at_different_levels()
    {
        // We're just testing that our log method doesn't throw exceptions
        // The actual Sentry SDK functionality is already tested by Sentry
        $levels = ['debug', 'info', 'warning', 'error', 'critical'];
        
        foreach ($levels as $level) {
            try {
                SentryLogger::log($level, "Test {$level} message", ['test' => true]);
                $this->assertTrue(true); // If we get here, it worked
            } catch (\Exception $e) {
                $this->fail("Failed to log at level {$level}: " . $e->getMessage());
            }
        }
    }

    #[Test]
    public function it_tracks_business_operations()
    {
        $result = SentryLogger::trackBusinessOperation('test_operation', ['test' => true], function ($span) {
            // Just verify the callback is called
            $this->assertNotNull($span);
            return 'operation_result';
        });
        
        $this->assertEquals('operation_result', $result);
    }

    #[Test]
    public function it_handles_exceptions_in_business_operations()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test exception');
        
        SentryLogger::trackBusinessOperation('failing_operation', [], function ($span) {
            throw new \Exception('Test exception');
        });
    }

    #[Test]
    public function it_tracks_database_operations()
    {
        $result = SentryLogger::trackDatabaseOperation('SELECT * FROM test', function ($span) {
            return ['test' => 'data'];
        });
        
        $this->assertEquals(['test' => 'data'], $result);
    }

    #[Test]
    public function it_tracks_http_requests()
    {
        $result = SentryLogger::trackHttpRequest('https://example.com', 'GET', function ($span) {
            return ['status' => 200];
        });
        
        $this->assertEquals(['status' => 200], $result);
    }
}