<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

            <!-- Sentry Distributed Tracing Meta Tag -->
        @if(config('sentry.dsn'))
            <meta name="sentry-trace" content="{{ \Sentry\SentrySdk::getCurrentHub()->getTransaction()?->toTraceparent() ?? '' }}">
        @endif

    <title>{{ config('app.name', 'Awesome Business Directory') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Sentry Configuration -->
    <script>
        window.sentryConfig = {
            dsn: '{{ config('sentry.dsn') }}',
            environment: '{{ config('sentry.environment', app()->environment()) }}',
            release: '{{ config('sentry.release') }}',
            tracesSampleRate: {{ config('sentry.traces_sample_rate', 1.0) }},
            // Enable distributed tracing
            enableTracing: true,
            // Add user context for distributed tracing
            @auth
            user: {
                id: '{{ auth()->user()->id }}',
                email: '{{ auth()->user()->email }}',
                is_admin: {{ auth()->user()->is_admin ? 'true' : 'false' }}
            },
            @else
            user: null,
            @endauth
            // Add page context
            pageContext: {
                route: '{{ request()->route()?->getName() }}',
                url: '{{ request()->fullUrl() }}',
                method: '{{ request()->method() }}',
                timestamp: '{{ now()->toISOString() }}'
            }
        };
        
        // Initialize distributed tracing early
        window.sentryTraceId = null;
        window.sentrySpanId = null;
        
        // Extract trace context from meta tag if present
        document.addEventListener('DOMContentLoaded', function() {
            const sentryTraceMeta = document.querySelector('meta[name="sentry-trace"]');
            if (sentryTraceMeta) {
                const traceValue = sentryTraceMeta.getAttribute('content');
                if (traceValue) {
                    const [traceId, spanId] = traceValue.split('-');
                    window.sentryTraceId = traceId;
                    window.sentrySpanId = spanId;
                    console.log('Distributed tracing initialized:', { traceId, spanId });
                }
            }
        });
    </script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        @include('partials.header')

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Mobile Menu Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    const isHidden = mobileMenu.classList.contains('hidden');
                    
                    if (isHidden) {
                        mobileMenu.classList.remove('hidden');
                        mobileMenuButton.setAttribute('aria-expanded', 'true');
                    } else {
                        mobileMenu.classList.add('hidden');
                        mobileMenuButton.setAttribute('aria-expanded', 'false');
                    }
                });
            }
        });
    </script>

    <!-- Sentry and Alpine.js are initialized via app.js -->
</body>
</html> 