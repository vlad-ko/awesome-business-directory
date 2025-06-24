<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Awesome Business Directory') }}</title>

    <!-- Sentry Configuration -->
    <script>
        window.sentryConfig = {
            dsn: '{{ config('sentry.dsn') }}',
            environment: '{{ app()->environment() }}',
            tracesSampleRate: 1.0,
            release: '{{ config('app.version', '1.0.0') }}'
        };
        
        // User context for Sentry
        @auth
        window.userContext = {
            id: '{{ auth()->id() }}',
            email: '{{ auth()->user()->email }}',
            is_admin: {{ auth()->user()->is_admin ? 'true' : 'false' }}
        };
        @else
        window.userContext = null;
        @endauth
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
</body>
</html> 