<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Welcome Section --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-sparkles class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-lg font-medium text-gray-900 dark:text-white">
                            Welcome to Admin Panel
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Manage your Laravel Boilerplate application
                        </p>
                    </div>
                </div>
                
                <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Current User</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ auth()->user()->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ auth()->user()->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Role</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">
                                @foreach(auth()->user()->roles as $role)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $role->name === 'admin' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Login</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">
                                {{ auth()->user()->last_login_at?->diffForHumans() ?? 'First time' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Quick Actions</h2>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <a href="{{ route('filament.admin.resources.users.index') }}" 
                       class="group relative flex items-center space-x-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-5 py-4 shadow-sm hover:border-gray-400 dark:hover:border-gray-500 focus-within:ring-2 focus-within:ring-blue-500">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-users class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="absolute inset-0" aria-hidden="true"></span>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Manage Users</p>
                            <p class="truncate text-sm text-gray-500 dark:text-gray-400">View and edit users</p>
                        </div>
                    </a>

                    <a href="{{ route('filament.admin.resources.roles.index') }}" 
                       class="group relative flex items-center space-x-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-5 py-4 shadow-sm hover:border-gray-400 dark:hover:border-gray-500 focus-within:ring-2 focus-within:ring-blue-500">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-shield-check class="h-6 w-6 text-green-600 dark:text-green-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="absolute inset-0" aria-hidden="true"></span>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Manage Roles</p>
                            <p class="truncate text-sm text-gray-500 dark:text-gray-400">Configure user roles</p>
                        </div>
                    </a>

                    <a href="{{ route('filament.admin.resources.permissions.index') }}" 
                       class="group relative flex items-center space-x-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-5 py-4 shadow-sm hover:border-gray-400 dark:hover:border-gray-500 focus-within:ring-2 focus-within:ring-blue-500">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-key class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="absolute inset-0" aria-hidden="true"></span>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">View Permissions</p>
                            <p class="truncate text-sm text-gray-500 dark:text-gray-400">System permissions</p>
                        </div>
                    </a>

                    <div class="group relative flex items-center space-x-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-5 py-4 shadow-sm">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-cog-6-tooth class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">System Settings</p>
                            <p class="truncate text-sm text-gray-500 dark:text-gray-400">Coming soon...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>