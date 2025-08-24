<nav class="bg-white dark:bg-secondary-800 shadow-sm border-b border-secondary-200 dark:border-secondary-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo and Brand -->
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="flex items-center">
                    <img class="h-10 w-auto" src="{{ asset('images/logo.svg') }}" alt="{{ config('app.name') }}">
                </a>
            </div>

            <!-- Desktop Navigation Links -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ url('/') }}" 
                   class="text-secondary-700 dark:text-secondary-300 hover:text-primary-600 dark:hover:text-primary-400 px-3 py-2 text-sm font-medium transition-colors duration-200">
                    Home
                </a>
                
                @if(config('boilerplate.features.demo_ui_components', true))
                    <a href="#components" 
                       class="text-secondary-700 dark:text-secondary-300 hover:text-primary-600 dark:hover:text-primary-400 px-3 py-2 text-sm font-medium transition-colors duration-200">
                        Components
                    </a>
                @endif

                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/admin') }}" 
                           class="text-secondary-700 dark:text-secondary-300 hover:text-primary-600 dark:hover:text-primary-400 px-3 py-2 text-sm font-medium transition-colors duration-200">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="text-secondary-700 dark:text-secondary-300 hover:text-primary-600 dark:hover:text-primary-400 px-3 py-2 text-sm font-medium transition-colors duration-200">
                            Login
                        </a>
                        
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" 
                               class="bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                                Register
                            </a>
                        @endif
                    @endauth
                @endif
            </div>

            <!-- Theme Toggle and Mobile Menu -->
            <div class="flex items-center space-x-4">
                <!-- Theme Toggle -->
                @if(config('boilerplate.theme.enable_theme_toggle', true))
                    <button type="button" 
                            id="theme-toggle"
                            class="p-2 text-secondary-500 dark:text-secondary-400 hover:text-secondary-700 dark:hover:text-secondary-200 hover:bg-secondary-100 dark:hover:bg-secondary-700 rounded-lg transition-colors duration-200"
                            aria-label="Toggle theme">
                        <!-- Sun Icon (Light Mode) -->
                        <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                        </svg>
                        <!-- Moon Icon (Dark Mode) -->
                        <svg class="w-5 h-5 block dark:hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                    </button>
                @endif

                <!-- Mobile menu button -->
                <button type="button" 
                        id="mobile-menu-button"
                        class="md:hidden p-2 text-secondary-500 dark:text-secondary-400 hover:text-secondary-700 dark:hover:text-secondary-200 hover:bg-secondary-100 dark:hover:bg-secondary-700 rounded-lg transition-colors duration-200"
                        aria-controls="mobile-menu" 
                        aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <!-- Hamburger icon -->
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 border-t border-secondary-200 dark:border-secondary-700">
                <a href="{{ url('/') }}" 
                   class="text-secondary-700 dark:text-secondary-300 hover:text-primary-600 dark:hover:text-primary-400 block px-3 py-2 text-base font-medium transition-colors duration-200">
                    Home
                </a>
                
                @if(config('boilerplate.features.demo_ui_components', true))
                    <a href="#components" 
                       class="text-secondary-700 dark:text-secondary-300 hover:text-primary-600 dark:hover:text-primary-400 block px-3 py-2 text-base font-medium transition-colors duration-200">
                        Components
                    </a>
                @endif

                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/admin') }}" 
                           class="text-secondary-700 dark:text-secondary-300 hover:text-primary-600 dark:hover:text-primary-400 block px-3 py-2 text-base font-medium transition-colors duration-200">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="text-secondary-700 dark:text-secondary-300 hover:text-primary-600 dark:hover:text-primary-400 block px-3 py-2 text-base font-medium transition-colors duration-200">
                            Login
                        </a>
                        
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" 
                               class="bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 text-white block px-3 py-2 rounded-lg text-base font-medium transition-colors duration-200 mt-2">
                                Register
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </div>
</nav>
