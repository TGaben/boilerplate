<x-filament::page>
    <x-filament::header>
        <x-slot name="heading">
            Dashboard
        </x-slot>
    </x-filament::header>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        <x-filament::card>
            <h2 class="text-lg font-semibold">Welcome to Admin Panel</h2>
            <p class="text-gray-600">You are successfully logged in to the admin panel.</p>
        </x-filament::card>
    </div>
</x-filament::page>