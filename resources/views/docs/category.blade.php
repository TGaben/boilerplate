<!DOCTYPE html>
<html lang="hu" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $categoryName }} - Dokumentáció</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireStyles
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900">
    <div class="min-h-full">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            📂 {{ $categoryName }}
                        </h1>
                    </div>
                    
                    <!-- Search Bar -->
                    <div class="flex-1 max-w-lg mx-8">
                        <livewire:doc-search :categories="[$category]" />
                    </div>
                    
                    <div>
                        <a href="{{ route('docs.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            ← Vissza a dokumentációhoz
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <!-- Breadcrumb -->
                <nav class="flex mb-6" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('docs.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                                📚 Dokumentáció
                            </a>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <span class="mx-2 text-gray-400">/</span>
                                <span class="ml-1 text-sm font-medium text-gray-500 dark:text-gray-400">{{ $categoryName }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- Documents Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($documents as $document)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg hover:shadow-lg transition duration-150 ease-in-out">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="text-2xl">📄</div>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                                Dokumentum
                                            </dt>
                                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                                {{ $document->title }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                                
                                @if($document->excerpt)
                                    <div class="mt-4">
                                        <p class="text-sm text-gray-600 dark:text-gray-300">
                                            {{ $document->excerpt }}
                                        </p>
                                    </div>
                                @endif

                                <div class="mt-6">
                                    <a href="{{ route('docs.show', ['category' => $document->category, 'slug' => $document->slug]) }}" 
                                       class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md text-sm text-center block transition duration-150 ease-in-out">
                                        Olvasás →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12">
                            <div class="text-gray-400 text-4xl mb-4">📭</div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                Nincs dokumentum ebben a kategóriában
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                Próbálj meg keresni másik kategóriában.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>


