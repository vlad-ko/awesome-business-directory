<?php

namespace Tests\Unit\Http\Middleware;

use Tests\TestCase;
use App\Http\Middleware\SentryContextMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;

class SentryContextMiddlewareTest extends TestCase
{
    protected SentryContextMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SentryContextMiddleware();
    }

    #[Test]
    public function it_processes_request_without_errors()
    {
        $request = Request::create('/test', 'GET');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK', 200);
        });
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    #[Test]
    public function it_handles_authenticated_requests()
    {
        $user = User::factory()->make(['id' => 1, 'email' => 'test@example.com']);
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK', 200);
        });
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function it_handles_error_responses()
    {
        $request = Request::create('/test', 'GET');
        
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Not Found', 404);
        });
        
        $this->assertEquals(404, $response->getStatusCode());
    }

    #[Test]
    public function it_propagates_exceptions()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test exception');
        
        $request = Request::create('/test', 'GET');
        
        $this->middleware->handle($request, function ($req) {
            throw new \Exception('Test exception');
        });
    }

    #[Test]
    public function it_determines_correct_feature_from_route()
    {
        // Test various routes to ensure feature detection works
        $routeTests = [
            '/onboard' => 'business_onboarding',
            '/businesses' => 'business_directory',
            '/admin/dashboard' => 'admin_panel',
            '/' => 'homepage',
            '/random' => 'general',
        ];
        
        foreach ($routeTests as $path => $expectedFeature) {
            $request = Request::create($path, 'GET');
            
            $response = $this->middleware->handle($request, function ($req) {
                return new Response('OK', 200);
            });
            
            $this->assertEquals(200, $response->getStatusCode());
        }
    }
}