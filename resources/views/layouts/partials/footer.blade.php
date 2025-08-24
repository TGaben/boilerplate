<footer class="bg-white dark:bg-secondary-800 border-t border-secondary-200 dark:border-secondary-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand Section -->
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center mb-4">
                    <img class="h-10 w-auto" src="{{ asset('images/logo.svg') }}" alt="{{ config('app.name') }}">
                </div>
                <p class="text-secondary-600 dark:text-secondary-400 text-sm max-w-md">
                    A professional Laravel boilerplate with admin panel, user management, 
                    and comprehensive UI components. Built with modern technologies and 
                    best practices for rapid development.
                </p>
                <div class="mt-6 flex space-x-4">
                    <a href="#" class="text-secondary-400 hover:text-secondary-600 dark:hover:text-secondary-300 transition-colors duration-200">
                        <span class="sr-only">GitHub</span>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 0C4.477 0 0 4.484 0 10.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0110 4.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.203 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.942.359.31.678.921.678 1.856 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0020 10.017C20 4.484 15.522 0 10 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    <a href="#" class="text-secondary-400 hover:text-secondary-600 dark:hover:text-secondary-300 transition-colors duration-200">
                        <span class="sr-only">Twitter</span>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M6.29 18.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0020 3.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.073 4.073 0 01.8 7.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 010 16.407a11.616 11.616 0 006.29 1.84" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-sm font-semibold text-secondary-900 dark:text-secondary-100 tracking-wider uppercase mb-4">
                    Quick Links
                </h3>
                <ul class="space-y-3">
                    <li>
                        <a href="{{ url('/') }}" class="text-secondary-600 dark:text-secondary-400 hover:text-primary-600 dark:hover:text-primary-400 text-sm transition-colors duration-200">
                            Home
                        </a>
                    </li>
                    @if(config('boilerplate.features.demo_ui_components', true))
                        <li>
                            <a href="#components" class="text-secondary-600 dark:text-secondary-400 hover:text-primary-600 dark:hover:text-primary-400 text-sm transition-colors duration-200">
                                Components
                            </a>
                        </li>
                    @endif
                    @if (Route::has('login'))
                        @auth
                            <li>
                                <a href="{{ url('/admin') }}" class="text-secondary-600 dark:text-secondary-400 hover:text-primary-600 dark:hover:text-primary-400 text-sm transition-colors duration-200">
                                    Dashboard
                                </a>
                            </li>
                        @else
                            <li>
                                <a href="{{ route('login') }}" class="text-secondary-600 dark:text-secondary-400 hover:text-primary-600 dark:hover:text-primary-400 text-sm transition-colors duration-200">
                                    Login
                                </a>
                            </li>
                        @endauth
                    @endif
                </ul>
            </div>

            <!-- Resources -->
            <div>
                <h3 class="text-sm font-semibold text-secondary-900 dark:text-secondary-100 tracking-wider uppercase mb-4">
                    Resources
                </h3>
                <ul class="space-y-3">
                    <li>
                        <a href="https://laravel.com/docs" target="_blank" class="text-secondary-600 dark:text-secondary-400 hover:text-primary-600 dark:hover:text-primary-400 text-sm transition-colors duration-200">
                            Laravel Docs
                        </a>
                    </li>
                    <li>
                        <a href="https://tailwindcss.com/docs" target="_blank" class="text-secondary-600 dark:text-secondary-400 hover:text-primary-600 dark:hover:text-primary-400 text-sm transition-colors duration-200">
                            Tailwind CSS
                        </a>
                    </li>
                    <li>
                        <a href="https://filamentphp.com/docs" target="_blank" class="text-secondary-600 dark:text-secondary-400 hover:text-primary-600 dark:hover:text-primary-400 text-sm transition-colors duration-200">
                            Filament PHP
                        </a>
                    </li>
                    @if(app()->environment(['local', 'development']))
                    <li>
                        <a href="{{ route('docs.index') }}" class="text-secondary-600 dark:text-secondary-400 hover:text-primary-600 dark:hover:text-primary-400 text-sm transition-colors duration-200">
                            Documentation
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="mt-8 pt-8 border-t border-secondary-200 dark:border-secondary-700">
            <div class="flex flex-col sm:flex-row justify-between items-center">
                <p class="text-secondary-500 dark:text-secondary-400 text-sm">
                    &copy; {{ date('Y') }} {{ config('boilerplate.brand.name', config('app.name')) }}. All rights reserved.
                </p>
                <div class="mt-4 sm:mt-0 flex space-x-6">
                    <a href="#" class="text-secondary-500 dark:text-secondary-400 hover:text-secondary-700 dark:hover:text-secondary-300 text-sm transition-colors duration-200">
                        Privacy Policy
                    </a>
                    <a href="#" class="text-secondary-500 dark:text-secondary-400 hover:text-secondary-700 dark:hover:text-secondary-300 text-sm transition-colors duration-200">
                        Terms of Service
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
