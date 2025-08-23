<!DOCTYPE html>
<html lang="hu" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentáció - Laravel Boilerplate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireStyles
    
    <style>
        /* Search highlighting styles */
        mark {
            background-color: #fef3c7;
            color: #92400e;
            padding: 0.125rem 0.25rem;
            border-radius: 0.25rem;
            font-weight: 500;
        }
        
        .dark mark {
            background-color: #d97706;
            color: #fef3c7;
        }
        
        /* Livewire dropdown specific styles */
        .bg-yellow-200 {
            background-color: #fef3c7 !important;
        }
        
        .dark\:bg-yellow-600 {
            background-color: #d97706 !important;
        }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900">
    <div class="min-h-full">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            📚 Dokumentáció
                        </h1>
                    </div>
                    
                    <!-- Search Bar -->
                    <div class="flex-1 max-w-lg mx-8">
                        <livewire:doc-search />
                    </div>
                    
                    <div>
                        <a href="/" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            ← Vissza a főoldalra
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($categories as $categoryKey => $category)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @switch($categoryKey)
                                            @case('core')
                                                <div class="text-3xl">⚙️</div>
                                                @break
                                            @case('recipes')
                                                <div class="text-3xl">🍳</div>
                                                @break
                                            @case('deployment')
                                                <div class="text-3xl">🚀</div>
                                                @break
                                            @case('troubleshooting')
                                                <div class="text-3xl">🔧</div>
                                                @break
                                            @default
                                                <div class="text-3xl">📄</div>
                                        @endswitch
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                                {{ $category['name'] }}
                                            </dt>
                                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                                {{ $category['count'] }} dokumentum
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                                
                                @if($category['documents']->isNotEmpty())
                                    <div class="mt-4">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                                            Legfrissebb dokumentumok:
                                        </h4>
                                        <ul class="space-y-1">
                                            @foreach($category['documents']->take(3) as $doc)
                                                <li>
                                                    <a href="{{ route('docs.show', ['category' => $categoryKey, 'slug' => $doc->slug]) }}" 
                                                       class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                                        {{ $doc->title }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="mt-6">
                                    <a href="{{ route('docs.category', $categoryKey) }}" 
                                       class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md text-sm text-center block transition duration-150 ease-in-out">
                                        Összes megtekintése →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Getting Started Section -->
                <div class="mt-12 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6">
                    <div class="max-w-3xl mx-auto text-center">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                            🚀 Kezdj el most!
                        </h2>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">
                            Használd a keresést a fenti sávban, vagy böngészd át a kategóriákat specifikus témák keresésére.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="{{ route('docs.category', 'core') }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-md transition duration-150 ease-in-out">
                                Core Komponensek
                            </a>
                            <a href="{{ route('docs.category', 'recipes') }}" 
                               class="bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-md transition duration-150 ease-in-out">
                                Receptek & Útmutatók
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>
