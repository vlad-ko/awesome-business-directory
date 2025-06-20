<nav class="bg-gradient-to-r from-purple-600 via-pink-500 to-yellow-400 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('welcome') }}" class="text-2xl font-bold text-white hover:text-yellow-200 transition-colors duration-200">
                        🏪 {{ config('app.name', 'Awesome Business Directory') }}
                    </a>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="hidden space-x-6 sm:-my-px sm:ml-10 sm:flex">
                <a href="{{ route('business.onboard') }}" class="inline-flex items-center px-3 py-2 rounded-full text-sm font-bold text-white hover:bg-white/20 hover:text-yellow-200 transition-all duration-200 transform hover:scale-105">
                    🚀 Add Business
                </a>
                <a href="{{ route('businesses.index') }}" class="inline-flex items-center px-3 py-2 rounded-full text-sm font-bold text-white hover:bg-white/20 hover:text-yellow-200 transition-all duration-200 transform hover:scale-105">
                    🏢 Browse Businesses
                </a>
                
                @auth
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-3 py-2 rounded-full text-sm font-bold text-yellow-300 hover:bg-white/20 hover:text-yellow-200 transition-all duration-200 transform hover:scale-105">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Admin Dashboard
                        </a>
                    @endif
                @endauth
            </div>

            <!-- Right side: Admin User Menu & Mobile menu button -->
            <div class="flex items-center">
                <!-- Admin User Menu (Desktop) -->
                @auth
                    @if(auth()->user()->is_admin)
                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <div class="relative">
                                <div class="flex items-center space-x-4">
                                    <span class="text-sm text-white font-medium">
                                        Welcome, {{ auth()->user()->name }} 👋
                                    </span>
                                    <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-4 py-2 border-2 border-white/30 text-sm font-bold rounded-full text-white bg-white/10 hover:bg-white/20 backdrop-blur-sm transition-all duration-200 transform hover:scale-105">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                @endauth

                <!-- Mobile menu button -->
                <div class="sm:hidden">
                    <button id="mobile-menu-button" type="button" class="inline-flex items-center justify-center p-2 rounded-full text-white hover:bg-white/20 transition-all duration-200" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <!-- Hamburger icon -->
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile menu (responsive) -->
    <div id="mobile-menu" class="sm:hidden hidden bg-white/10 backdrop-blur-md border-t border-white/20">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('business.onboard') }}" class="block pl-4 pr-4 py-3 text-base font-bold text-white hover:bg-white/20 hover:text-yellow-200 transition-all duration-200 rounded-lg mx-2">
                🚀 Add Business
            </a>
            <a href="{{ route('businesses.index') }}" class="block pl-4 pr-4 py-3 text-base font-bold text-white hover:bg-white/20 hover:text-yellow-200 transition-all duration-200 rounded-lg mx-2">
                🏢 Browse Businesses
            </a>
            
            @auth
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="block pl-4 pr-4 py-3 text-base font-bold text-yellow-300 hover:bg-white/20 hover:text-yellow-200 transition-all duration-200 rounded-lg mx-2">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Admin Dashboard
                    </a>
                @endif
            @endauth
        </div>
        
        @auth
            @if(auth()->user()->is_admin)
                <div class="pt-4 pb-3 border-t border-white/20">
                    <div class="flex items-center px-4">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center">
                                <span class="text-xl">👤</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-bold leading-none text-white">{{ auth()->user()->name }}</div>
                            <div class="text-sm font-medium leading-none text-yellow-200 mt-1">Administrator</div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left pl-4 pr-4 py-3 text-base font-bold text-white hover:bg-white/20 hover:text-yellow-200 transition-all duration-200 rounded-lg mx-2">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        @endauth
    </div>
</nav> 