// Fallback search functionality for development
let searchIndex = [
    // Default fallback data - will be replaced by docs content loader
    {
        title: "Quick Start Guide",
        url: "#index",
        content: "2 perces telepítés Laravel Boilerplate egyparancs setup",
        category: "home",
        difficulty: "easy"
    }
];

// Function to update search index from docs content loader
window.updateLocalSearchIndex = function(docsSearchIndex) {
    if (docsSearchIndex && docsSearchIndex.length > 0) {
        searchIndex = docsSearchIndex.map(doc => ({
            title: doc.title,
            url: doc.url,
            content: doc.content,
            category: doc.category,
            difficulty: 'medium' // Default difficulty
        }));
        console.log('🔄 Local search index updated:', searchIndex.length, 'documents');
    }
};

function performFallbackSearch(query) {
    const results = searchIndex.filter(item => 
        item.title.toLowerCase().includes(query) ||
        item.content.toLowerCase().includes(query) ||
        item.category.toLowerCase().includes(query)
    );

    displaySearchResults(results, query);
}

function displaySearchResults(results, query) {
    const existingResults = document.getElementById('search-results');
    if (existingResults) {
        existingResults.remove();
    }

    if (results.length === 0) {
        return;
    }

    const resultsContainer = document.createElement('div');
    resultsContainer.id = 'search-results';
    resultsContainer.className = 'absolute top-full left-0 right-0 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg mt-1 max-h-96 overflow-y-auto z-50';

    const resultsList = results.map(result => {
        const difficultyColors = {
            easy: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400', 
            advanced: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
        };

        return `
            <div class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-600 last:border-b-0" 
                 onclick="navigateToResult('${result.url}')">
                <div class="flex items-center justify-between">
                    <h4 class="font-medium text-gray-900 dark:text-white">${highlightQuery(result.title, query)}</h4>
                    <div class="flex space-x-2">
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                            ${result.category}
                        </span>
                        <span class="px-2 py-1 text-xs rounded-full ${difficultyColors[result.difficulty]}">
                            ${result.difficulty}
                        </span>
                    </div>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">${highlightQuery(result.content.substring(0, 100), query)}...</p>
            </div>
        `;
    }).join('');

    resultsContainer.innerHTML = `
        <div class="p-2 border-b border-gray-100 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
            <span class="text-xs text-gray-600 dark:text-gray-400">
                ${results.length} találat "${query}" keresésre
            </span>
        </div>
        ${resultsList}
    `;

    document.querySelector('#docsearch .relative').appendChild(resultsContainer);

    // Close results when clicking outside
    document.addEventListener('click', function closeResults(e) {
        if (!resultsContainer.contains(e.target) && !document.getElementById('fallback-search').contains(e.target)) {
            resultsContainer.remove();
            document.removeEventListener('click', closeResults);
        }
    });
}

function highlightQuery(text, query) {
    const regex = new RegExp(`(${query})`, 'gi');
    return text.replace(regex, '<mark class="bg-yellow-200 dark:bg-yellow-800">$1</mark>');
}

function navigateToResult(url) {
    const resultsContainer = document.getElementById('search-results');
    if (resultsContainer) {
        resultsContainer.remove();
    }
    
    // Clear search input
    const searchInput = document.getElementById('fallback-search');
    if (searchInput) {
        searchInput.value = '';
    }
    
    if (url.startsWith('#')) {
        // Update hash for docs routing
        window.location.hash = url;
    } else {
        window.location.href = url;
    }
}
