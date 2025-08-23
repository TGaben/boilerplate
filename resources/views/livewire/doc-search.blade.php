<div class="relative w-full max-w-2xl mx-auto">
    <!-- Search Input -->
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        
        <input 
            type="text"
            wire:model.live.debounce.300ms="query"
            placeholder="{{ $placeholder }}"
            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white dark:bg-gray-800 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:text-white text-sm"
            autocomplete="off"
            data-search-input
        />
        
        @if($query)
            <button 
                wire:click="clearSearch"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        @endif
    </div>

    <!-- Search Results -->
    @if($showResults && $query)
        <div 
            class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-96 overflow-y-auto"
            data-search-results
        >
            @if(empty($results))
                <!-- No Results -->
                <div class="p-4 text-center text-gray-500 dark:text-gray-400" data-search-no-results>
                    <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.5-.935-6.172-2.172M16.172 9.172A4 4 0 0015 6.343A7.953 7.953 0 0012 6c-1.756 0-3.449.448-4.95 1.172"/>
                    </svg>
                    <h3 class="text-sm font-medium mb-1">Nincs találat</h3>
                    <p class="text-xs">Próbálj meg más keresési kifejezést használni.</p>
                </div>
            @else
                <!-- Results by Category -->
                @php $resultIndex = 0; @endphp
                @foreach($results as $categoryKey => $category)
                    <div class="border-b border-gray-100 dark:border-gray-700 last:border-b-0" data-search-category="{{ $categoryKey }}">
                        <!-- Category Header -->
                        <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border-b border-gray-100 dark:border-gray-600">
                            <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider sidebar-title">
                                {{ $category['name'] }}
                            </h4>
                        </div>
                        
                        <!-- Category Results -->
                        @foreach($category['documents'] as $document)
                            <button 
                                wire:click="selectResult('{{ $document['url'] }}')"
                                class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                                data-result-index="{{ $resultIndex }}"
                                data-search-result-0
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">
                                            {!! $document['highlighted_title'] !!}
                                        </h5>
                                        @if($document['excerpt'])
                                            <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
                                                {!! $document['highlighted_excerpt'] !!}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="ml-2 flex-shrink-0">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ ucfirst($document['category']) }}
                                        </span>
                                    </div>
                                </div>
                            </button>
                            @php $resultIndex++; @endphp
                        @endforeach
                    </div>
                @endforeach
                
                <!-- Results Count Footer -->
                <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $this->resultsCount }} eredmény "{{ $query }}" keresésre
                    </p>
                </div>
            @endif
        </div>
    @endif

    <!-- Loading State -->
    <div wire:loading wire:target="query" class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg">
        <div class="p-4 text-center">
            <svg class="animate-spin h-5 w-5 text-blue-500 mx-auto" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Keresés...</p>
        </div>
    </div>
    
    <!-- Inline styles to avoid multiple root elements -->
    <style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    </style>
</div>
