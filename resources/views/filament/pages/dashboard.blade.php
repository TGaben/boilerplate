<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <h2 class="text-lg font-medium text-gray-900">Welcome to Admin Panel</h2>
                <p class="mt-1 text-sm text-gray-600">You are successfully logged in to the admin panel.</p>
                <div class="mt-3">
                    <p class="text-sm text-gray-500">
                        Current user: {{ auth()->user()->name ?? 'Guest' }}<br>
                        Email: {{ auth()->user()->email ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>