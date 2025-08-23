@extends('layouts.app')

@section('title', 'Welcome to Laravel Boilerplate')

@section('content')
@if(config('boilerplate.features.demo_ui_components', true))
    <!-- Hero Section -->
    <section class="bg-gradient-to-b from-primary-50 to-white dark:from-secondary-900 dark:to-secondary-800 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-6xl font-bold text-secondary-900 dark:text-secondary-100 mb-6">
                Laravel Boilerplate
                <span class="text-primary-600 dark:text-primary-400">UI Kit</span>
            </h1>
            <p class="text-xl text-secondary-600 dark:text-secondary-400 mb-8 max-w-3xl mx-auto">
                A professional Laravel boilerplate with admin panel, user management, 
                and comprehensive UI components. Built with modern technologies and 
                best practices for rapid development.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#components" class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-soft transition-all duration-200 hover:shadow-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    Explore Components
                </a>
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/admin') }}" class="inline-flex items-center px-6 py-3 border border-secondary-300 dark:border-secondary-600 text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-700 font-medium rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                            Admin Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 border border-secondary-300 dark:border-secondary-600 text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-700 font-medium rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            Get Started
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    </section>

    <!-- Features Overview -->
    <section class="py-16 bg-white dark:bg-secondary-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-secondary-900 dark:text-secondary-100 mb-4">
                    Everything You Need to Start
                </h2>
                <p class="text-lg text-secondary-600 dark:text-secondary-400 max-w-2xl mx-auto">
                    Built with Laravel 12, TailwindCSS, and Filament - this boilerplate includes everything 
                    you need for modern web application development.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/20 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100 mb-2">Modern Stack</h3>
                    <p class="text-secondary-600 dark:text-secondary-400 text-sm">Laravel 12, PHP 8.3, TailwindCSS, and Filament PHP for the best developer experience.</p>
                </div>

                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-success-100 dark:bg-success-900/20 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-success-600 dark:text-success-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100 mb-2">Quality Assured</h3>
                    <p class="text-secondary-600 dark:text-secondary-400 text-sm">Automated testing, code quality checks, and CI/CD pipeline ensure reliable code.</p>
                </div>

                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-warning-100 dark:bg-warning-900/20 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-warning-600 dark:text-warning-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100 mb-2">Admin Ready</h3>
                    <p class="text-secondary-600 dark:text-secondary-400 text-sm">Complete admin panel with user management, roles, permissions, and activity logging.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- UI Components Showcase -->
    <section id="components" class="py-16 bg-secondary-50 dark:bg-secondary-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-secondary-900 dark:text-secondary-100 mb-4">
                    UI Components Library
                </h2>
                <p class="text-lg text-secondary-600 dark:text-secondary-400 max-w-2xl mx-auto">
                    Explore our comprehensive collection of pre-built UI components. 
                    All components are fully responsive and support dark mode.
                </p>
            </div>

            <!-- Component Tabs -->
            <div class="mb-8">
                <div class="flex flex-wrap justify-center gap-2 mb-8">
                    <button onclick="showComponent('buttons')" class="component-tab-btn active px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200">
                        Buttons
                    </button>
                    <button onclick="showComponent('cards')" class="component-tab-btn px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200">
                        Cards
                    </button>
                    <button onclick="showComponent('forms')" class="component-tab-btn px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200">
                        Forms
                    </button>
                    <button onclick="showComponent('alerts')" class="component-tab-btn px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200">
                        Alerts
                    </button>
                </div>
            </div>

            <!-- Component Display Areas -->
            <div id="buttons-component" class="component-display">
                @include('components.ui.buttons')
            </div>

            <div id="cards-component" class="component-display hidden">
                @include('components.ui.cards')
            </div>

            <div id="forms-component" class="component-display hidden">
                @include('components.ui.forms')
            </div>

            <div id="alerts-component" class="component-display hidden">
                @include('components.ui.alerts')
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-16 bg-primary-600 dark:bg-primary-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">
                Ready to Start Building?
            </h2>
            <p class="text-xl text-primary-100 mb-8 max-w-2xl mx-auto">
                Clone this repository and start building your next amazing Laravel application 
                with all the essentials already configured.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="https://github.com/TGaben/boilerplate" target="_blank" class="inline-flex items-center px-6 py-3 bg-white text-primary-600 hover:bg-primary-50 font-medium rounded-lg shadow-soft transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 0C4.477 0 0 4.484 0 10.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0110 4.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.203 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.942.359.31.678.921.678 1.856 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0020 10.017C20 4.484 15.522 0 10 0z" clip-rule="evenodd" />
                    </svg>
                    View on GitHub
                </a>
                @if(app()->environment(['local', 'development']))
                <a href="{{ route('docs.index') }}" class="inline-flex items-center px-6 py-3 border border-white text-white hover:bg-white hover:text-primary-600 font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    Documentation
                </a>
                @endif
            </div>
        </div>
    </section>

@else
    <!-- Clean Welcome Page (when demo is disabled) -->
    <section class="min-h-screen flex items-center justify-center bg-gradient-to-b from-primary-50 to-white dark:from-secondary-900 dark:to-secondary-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="w-20 h-20 bg-primary-600 rounded-full mx-auto mb-8 flex items-center justify-center">
                <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </div>
            
            <h1 class="text-4xl md:text-6xl font-bold text-secondary-900 dark:text-secondary-100 mb-6">
                Welcome to
                <span class="text-primary-600 dark:text-primary-400">{{ config('app.name') }}</span>
            </h1>
            
            <p class="text-xl text-secondary-600 dark:text-secondary-400 mb-8 max-w-2xl mx-auto">
                Your Laravel application is ready. Start building something amazing.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/admin') }}" class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-soft transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-soft transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            Get Started
                        </a>
                        
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 border border-secondary-300 dark:border-secondary-600 text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-700 font-medium rounded-lg transition-colors duration-200">
                                Create Account
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </section>
@endif
@endsection

@push('styles')
<style>
    .component-tab-btn {
        @apply bg-white dark:bg-secondary-700 text-secondary-700 dark:text-secondary-300 border border-secondary-200 dark:border-secondary-600;
    }
    .component-tab-btn.active {
        @apply bg-primary-600 text-white border-primary-600;
    }
    .component-tab-btn:not(.active):hover {
        @apply bg-secondary-50 dark:bg-secondary-600;
    }
</style>
@endpush

@push('scripts')
<script>
    function showComponent(componentName) {
        // Hide all components
        const displays = document.querySelectorAll('.component-display');
        displays.forEach(display => {
            display.classList.add('hidden');
        });
        
        // Show selected component
        const selectedDisplay = document.getElementById(componentName + '-component');
        if (selectedDisplay) {
            selectedDisplay.classList.remove('hidden');
        }
        
        // Update tab buttons
        const buttons = document.querySelectorAll('.component-tab-btn');
        buttons.forEach(button => {
            button.classList.remove('active');
        });
        
        // Add active class to clicked button
        event.target.classList.add('active');
    }
</script>
@endpush
