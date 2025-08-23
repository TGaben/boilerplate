<!DOCTYPE html>
<html lang="hu" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document->title }} - Dokumentáció</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireStyles
    
    <style>
        .documentation-content {
            line-height: 1.7;
            color: #374151;
        }
        
        .dark .documentation-content {
            color: #d1d5db;
        }
        
        .documentation-content h1 {
            font-size: 2rem;
            font-weight: 700;
            margin: 2rem 0 1rem 0;
            color: #111827;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 0.5rem;
        }
        
        .dark .documentation-content h1 {
            color: #f9fafb;
            border-bottom-color: #60a5fa;
        }
        
        .documentation-content h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 1.5rem 0 0.75rem 0;
            color: #1f2937;
        }
        
        .dark .documentation-content h2 {
            color: #e5e7eb;
        }
        
        .documentation-content h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 1.25rem 0 0.5rem 0;
            color: #374151;
        }
        
        .dark .documentation-content h3 {
            color: #d1d5db;
        }
        
        .documentation-content p {
            margin: 1rem 0;
            text-align: justify;
        }
        
        .documentation-content ul, .documentation-content ol {
            margin: 1rem 0;
            padding-left: 2rem;
        }
        
        .documentation-content ul {
            list-style-type: disc;
        }
        
        .documentation-content ol {
            list-style-type: decimal;
        }
        
        .documentation-content li {
            margin: 0.75rem 0;
            line-height: 1.6;
            position: relative;
        }
        
        .documentation-content ul ul {
            margin: 0.5rem 0;
            list-style-type: circle;
        }
        
        .documentation-content ol ol {
            margin: 0.5rem 0;
            list-style-type: lower-alpha;
        }
        
        .documentation-content ul ul ul {
            list-style-type: square;
        }
        
        .documentation-content code {
            background-color: #f3f4f6;
            color: #dc2626;
            padding: 0.125rem 0.375rem;
            border-radius: 0.25rem;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
        }
        
        .dark .documentation-content code {
            background-color: #374151;
            color: #fca5a5;
        }
        
        .documentation-content pre {
            background-color: #1f2937;
            color: #e5e7eb;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin: 1rem 0;
            border-left: 4px solid #3b82f6;
        }
        
        .documentation-content pre code {
            background: none;
            color: inherit;
            padding: 0;
        }
        
        .documentation-content blockquote {
            border-left: 4px solid #e5e7eb;
            padding-left: 1rem;
            margin: 1rem 0;
            font-style: italic;
            color: #6b7280;
        }
        
        .dark .documentation-content blockquote {
            border-left-color: #4b5563;
            color: #9ca3af;
        }
        
        .documentation-content strong {
            font-weight: 600;
            color: #111827;
        }
        
        .dark .documentation-content strong {
            color: #f9fafb;
        }
        
        .documentation-content a {
            color: #3b82f6;
            text-decoration: underline;
        }
        
        .documentation-content a:hover {
            color: #1d4ed8;
        }
        
        .dark .documentation-content a {
            color: #60a5fa;
        }
        
        .dark .documentation-content a:hover {
            color: #93c5fd;
        }
        
        .documentation-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }
        
        .documentation-content th,
        .documentation-content td {
            border: 1px solid #e5e7eb;
            padding: 0.5rem 0.75rem;
            text-align: left;
        }
        
        .documentation-content th {
            background-color: #f9fafb;
            font-weight: 600;
        }
        
        .dark .documentation-content th,
        .dark .documentation-content td {
            border-color: #4b5563;
        }
        
        .dark .documentation-content th {
            background-color: #374151;
        }
        
        /* Search highlighting styles for content */
        .documentation-content mark {
            background-color: #fef3c7;
            color: #92400e;
            padding: 0.125rem 0.25rem;
            border-radius: 0.25rem;
            font-weight: 500;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        
        .dark .documentation-content mark {
            background-color: #d97706;
            color: #fef3c7;
        }
        
        /* Animation for highlights */
        .documentation-content mark.highlight-flash {
            animation: highlight-flash 2s ease-in-out;
        }
        
        @keyframes highlight-flash {
            0% { background-color: #fbbf24; }
            50% { background-color: #fef3c7; }
            100% { background-color: #fef3c7; }
        }
        
        .dark .documentation-content mark.highlight-flash {
            animation: highlight-flash-dark 2s ease-in-out;
        }
        
        @keyframes highlight-flash-dark {
            0% { background-color: #f59e0b; }
            50% { background-color: #d97706; }
            100% { background-color: #d97706; }
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
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $document->title }}
                        </h1>
                    </div>
                    
                    <!-- Search Bar -->
                    <div class="flex-1 max-w-lg mx-8">
                        <livewire:doc-search />
                    </div>
                    
                    <div>
                        <a href="{{ route('docs.category', $document->category) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            ← Vissza a kategóriához
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Sidebar Navigation -->
                <aside class="w-full lg:w-64 flex-shrink-0">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            {{ ucfirst($document->category) }} Dokumentumok
                        </h3>
                        <nav class="space-y-2">
                            @foreach($navigation as $navItem)
                                <a href="{{ route('docs.show', ['category' => $navItem->category, 'slug' => $navItem->slug]) }}" 
                                   class="block px-3 py-2 rounded-md text-sm font-medium {{ $navItem->slug === $document->slug ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-700' }}">
                                    {{ $navItem->title }}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </aside>

                <!-- Document Content -->
                <div class="flex-1">
                    <!-- Breadcrumb -->
                    <nav class="flex mb-6" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            @foreach($breadcrumbs as $breadcrumb)
                                <li class="inline-flex items-center">
                                    @if($breadcrumb['url'])
                                        <a href="{{ $breadcrumb['url'] }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                                            {{ $breadcrumb['title'] }}
                                        </a>
                                        <span class="mx-2 text-gray-400">/</span>
                                    @else
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $breadcrumb['title'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>

                    <!-- Document Content -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                        <div class="px-6 py-8">
                            <article class="documentation-content">
                                {!! $document->content !!}
                            </article>
                        </div>
                    </div>

                    <!-- Document Footer -->
                    <div class="mt-6 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <p>Kategória: <span class="font-medium">{{ ucfirst($document->category) }}</span></p>
                                <p>Utolsó frissítés: {{ $document->updated_at->format('Y. m. d.') }}</p>
                            </div>
                            <div class="flex space-x-3">
                                <a href="{{ route('docs.category', $document->category) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600">
                                    📂 Kategória áttekintése
                                </a>
                                <a href="{{ route('docs.index') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                    📚 Összes dokumentum
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    @livewireScripts
    
    <script>
        // Content highlighting functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Get highlight parameter from URL
            const urlParams = new URLSearchParams(window.location.search);
            const highlightTerm = urlParams.get('highlight');
            
            if (highlightTerm && highlightTerm.trim()) {
                highlightContentText(highlightTerm.trim());
            }
        });

        function highlightContentText(searchTerm) {
            const contentElement = document.querySelector('.documentation-content');
            if (!contentElement) return;

            // Split search term into individual words
            const searchTerms = searchTerm.toLowerCase().split(' ').filter(term => term.length >= 2);
            
            if (searchTerms.length === 0) return;

            // Create a regex pattern for all search terms
            const regexPattern = searchTerms.map(term => escapeRegExp(term)).join('|');
            const regex = new RegExp(`(${regexPattern})`, 'gi');

            highlightInElement(contentElement, regex);
            
            // Scroll to first highlight with smooth animation
            setTimeout(() => {
                const firstHighlight = contentElement.querySelector('mark');
                if (firstHighlight) {
                    firstHighlight.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                    // Add flash animation
                    firstHighlight.classList.add('highlight-flash');
                }
            }, 100);
        }

        function highlightInElement(element, regex) {
            // Process text nodes only, skip already highlighted content
            const walker = document.createTreeWalker(
                element,
                NodeFilter.SHOW_TEXT,
                {
                    acceptNode: function(node) {
                        // Skip if parent is already a mark element or script/style
                        if (node.parentNode.tagName === 'MARK' || 
                            node.parentNode.tagName === 'SCRIPT' || 
                            node.parentNode.tagName === 'STYLE' ||
                            node.parentNode.tagName === 'CODE' ||
                            node.parentNode.tagName === 'PRE') {
                            return NodeFilter.FILTER_REJECT;
                        }
                        return NodeFilter.FILTER_ACCEPT;
                    }
                }
            );

            const textNodes = [];
            let node;
            while (node = walker.nextNode()) {
                textNodes.push(node);
            }

            textNodes.forEach(textNode => {
                const text = textNode.textContent;
                if (regex.test(text)) {
                    const highlightedHTML = text.replace(regex, '<mark>$1</mark>');
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = highlightedHTML;
                    
                    // Replace text node with highlighted content
                    const parent = textNode.parentNode;
                    while (tempDiv.firstChild) {
                        parent.insertBefore(tempDiv.firstChild, textNode);
                    }
                    parent.removeChild(textNode);
                }
            });
        }

        function escapeRegExp(string) {
            // Escape special regex characters
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        // Also highlight search terms when using the search component on this page
        document.addEventListener('livewire:init', () => {
            Livewire.on('searchPerformed', (data) => {
                if (data.query) {
                    // Clear existing highlights
                    clearHighlights();
                    // Apply new highlights
                    highlightContentText(data.query);
                }
            });
        });

        function clearHighlights() {
            const contentElement = document.querySelector('.documentation-content');
            if (!contentElement) return;

            const marks = contentElement.querySelectorAll('mark');
            marks.forEach(mark => {
                const parent = mark.parentNode;
                parent.replaceChild(document.createTextNode(mark.textContent), mark);
                parent.normalize(); // Merge adjacent text nodes
            });
        }

        // Add URL update functionality when searching on current page
        window.updateHighlightInUrl = function(query) {
            if (query && query.trim()) {
                const url = new URL(window.location);
                url.searchParams.set('highlight', query.trim());
                window.history.replaceState({}, '', url);
            } else {
                const url = new URL(window.location);
                url.searchParams.delete('highlight');
                window.history.replaceState({}, '', url);
            }
        };
    </script>
</body>
</html>
