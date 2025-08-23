/**
 * =================================================================
 * Laravel Boilerplate Docs Content Loader
 * =================================================================
 * 
 * Ez a script dinamikusan betölti és rendereli a /docs mappa 
 * markdown tartalmát a modern docs-site felületen.
 */

// Import marked.js for proper markdown parsing
import { marked } from 'https://cdn.jsdelivr.net/npm/marked@11.2.0/+esm';

class DocsContentLoader {
    constructor() {
        this.docsStructure = null;
        this.currentContent = null;
        this.baseDocsPath = 'docs/';
        this.contentCache = new Map();
        
        // Configure marked.js with custom renderer
        this.configureMarkedRenderer();
    }

    /**
     * Configure marked.js renderer with TailwindCSS classes
     */
    configureMarkedRenderer() {
        const renderer = new marked.Renderer();
        
        // Headers with TailwindCSS classes
        renderer.heading = (text, level) => {
            const classes = {
                1: 'text-3xl font-bold text-gray-900 dark:text-white mb-6 mt-8',
                2: 'text-2xl font-bold text-gray-900 dark:text-white mb-4 mt-8',
                3: 'text-xl font-semibold text-gray-900 dark:text-white mb-3 mt-6',
                4: 'text-lg font-semibold text-gray-900 dark:text-white mb-2 mt-4',
                5: 'text-base font-semibold text-gray-900 dark:text-white mb-2 mt-4',
                6: 'text-sm font-semibold text-gray-900 dark:text-white mb-2 mt-4'
            };
            return `<h${level} class="${classes[level] || classes[6]}">${text}</h${level}>`;
        };

        // Paragraphs
        renderer.paragraph = (text) => {
            return `<p class="text-gray-700 dark:text-gray-300 mb-4 leading-relaxed">${text}</p>`;
        };

        // Lists
        renderer.list = (body, ordered) => {
            const tag = ordered ? 'ol' : 'ul';
            const classes = ordered ? 'list-decimal list-inside space-y-2 mb-4 ml-4' : 'list-disc list-inside space-y-2 mb-4 ml-4';
            return `<${tag} class="${classes}">${body}</${tag}>`;
        };

        renderer.listitem = (text) => {
            return `<li class="text-gray-700 dark:text-gray-300">${text}</li>`;
        };

        // Code blocks
        renderer.code = (code, language) => {
            return `<div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 my-4 overflow-x-auto border border-gray-200 dark:border-gray-700">
                <pre class="text-sm"><code class="language-${language || 'text'} text-gray-800 dark:text-gray-200">${this.escapeHtml(code)}</code></pre>
            </div>`;
        };

        // Inline code
        renderer.codespan = (code) => {
            return `<code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-sm font-mono text-red-600 dark:text-red-400">${code}</code>`;
        };

        // Links
        renderer.link = (href, title, text) => {
            const titleAttr = title ? ` title="${title}"` : '';
            return `<a href="${href}"${titleAttr} class="text-blue-600 dark:text-blue-400 hover:underline font-medium">${text}</a>`;
        };

        // Blockquotes
        renderer.blockquote = (quote) => {
            return `<blockquote class="border-l-4 border-blue-500 pl-4 my-4 italic text-gray-600 dark:text-gray-400 bg-blue-50 dark:bg-blue-900/20 py-2 rounded-r">${quote}</blockquote>`;
        };

        // Tables
        renderer.table = (header, body) => {
            return `<div class="overflow-x-auto my-6">
                <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <thead class="bg-gray-50 dark:bg-gray-700">${header}</thead>
                    <tbody>${body}</tbody>
                </table>
            </div>`;
        };

        renderer.tablerow = (content) => {
            return `<tr class="border-b border-gray-200 dark:border-gray-600">${content}</tr>`;
        };

        renderer.tablecell = (content, flags) => {
            const tag = flags.header ? 'th' : 'td';
            const classes = flags.header 
                ? 'px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider'
                : 'px-4 py-3 text-sm text-gray-700 dark:text-gray-300';
            return `<${tag} class="${classes}">${content}</${tag}>`;
        };

        // Set the custom renderer
        marked.setOptions({
            renderer: renderer,
            gfm: true,
            breaks: false,
            pedantic: false,
            sanitize: false,
            smartLists: true,
            smartypants: false
        });
    }

    /**
     * Dokumentáció struktúra betöltése
     */
    async loadDocsStructure() {
        this.docsStructure = {
            'index': {
                title: '🏠 Főoldal',
                path: 'README.md',
                category: 'home'
            },
            'how-to-guides': {
                title: '📋 Útmutató Gyűjtemény',
                path: 'how-to-guides.md',
                category: 'guides'
            },
            'deployment': {
                title: '🚀 Deployment Áttekintő',
                path: 'deployment.md',
                category: 'deployment'
            },
            // Core komponensek
            'core/authentication': {
                title: '🔐 Authentication & Permissions',
                path: 'core/authentication.md',
                category: 'core',
                difficulty: '🟢 Easy'
            },
            'core/admin-panel': {
                title: '🎛️ Admin Panel (Filament)',
                path: 'core/admin-panel.md',
                category: 'core',
                difficulty: '🟢 Easy'
            },
            'core/environment-setup': {
                title: '🌍 Environment Setup',
                path: 'core/environment-setup.md',
                category: 'core',
                difficulty: '🟡 Medium'
            },
            'core/testing': {
                title: '🧪 Testing Foundation',
                path: 'core/testing.md',
                category: 'core',
                difficulty: '🟡 Medium'
            },
            // Recipes
            'recipes/api-development': {
                title: '🔌 API Development',
                path: 'recipes/api-development.md',
                category: 'recipes',
                difficulty: '🟢 Easy',
                timeEstimate: '2-3 óra'
            },
            'recipes/file-uploads': {
                title: '💾 File Upload System',
                path: 'recipes/file-uploads.md',
                category: 'recipes',
                difficulty: '🟡 Medium',
                timeEstimate: '3-4 óra'
            },
            'recipes/multi-tenancy': {
                title: '🏗️ Multi-Tenancy',
                path: 'recipes/multi-tenancy.md',
                category: 'recipes',
                difficulty: '🔴 Advanced',
                timeEstimate: '1-2 nap'
            },
            // Deployment
            'deployment/architecture': {
                title: '🏗️ Architecture Overview',
                path: 'deployment/architecture.md',
                category: 'deployment'
            },
            // Troubleshooting
            'troubleshooting/common-issues': {
                title: '🔧 Common Issues',
                path: 'troubleshooting/common-issues.md',
                category: 'troubleshooting'
            },
            'troubleshooting/quality-check': {
                title: '🔍 Quality Check Guide',
                path: 'troubleshooting/quality-check.md',
                category: 'troubleshooting'
            }
        };

        return this.docsStructure;
    }

    /**
     * Markdown fájl betöltése és HTML-re konvertálása
     */
    async loadMarkdownContent(docPath) {
        // Cache ellenőrzés
        if (this.contentCache.has(docPath)) {
            return this.contentCache.get(docPath);
        }

        try {
            // A markdown tartalom fetch-elése relatív path-ról
            const response = await fetch(`${this.baseDocsPath}${docPath}`);
            
            if (!response.ok) {
                throw new Error(`Failed to load ${docPath}: ${response.statusText}`);
            }

            const markdownText = await response.text();
            const htmlContent = marked.parse(markdownText);
            
            // Cache-elés
            this.contentCache.set(docPath, htmlContent);
            
            return htmlContent;
        } catch (error) {
            console.error(`Error loading markdown content: ${error.message}`);
            return this.getErrorContent(docPath, error.message);
        }
    }



    /**
     * HTML escape helper
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Error content generálása
     */
    getErrorContent(docPath, errorMessage) {
        return `
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-red-800 dark:text-red-200 mb-2">
                    ❌ Dokumentum betöltési hiba
                </h2>
                <p class="text-red-600 dark:text-red-300 mb-4">
                    Nem sikerült betölteni a dokumentumot: <code>${docPath}</code>
                </p>
                <details class="text-sm text-red-500 dark:text-red-400">
                    <summary class="cursor-pointer font-medium">Részletek</summary>
                    <pre class="mt-2 p-2 bg-red-100 dark:bg-red-900/40 rounded">${errorMessage}</pre>
                </details>
                <div class="mt-4">
                    <a href="#index" class="text-blue-600 dark:text-blue-400 hover:underline">
                        ← Vissza a főoldalra
                    </a>
                </div>
            </div>
        `;
    }

    /**
     * Navigation menü generálása a docs struktúra alapján
     */
    generateNavigation() {
        if (!this.docsStructure) {
            return '';
        }

        const categories = {
            'home': { title: '🏠 Kezdőlap', items: [] },
            'guides': { title: '📋 Útmutatók', items: [] },
            'core': { title: '🏗️ Core Komponensek', items: [] },
            'recipes': { title: '🍳 Recipes', items: [] },
            'deployment': { title: '🚀 Deployment', items: [] },
            'troubleshooting': { title: '🔧 Troubleshooting', items: [] }
        };

        // Docs elemek kategorizálása
        Object.entries(this.docsStructure).forEach(([key, doc]) => {
            if (categories[doc.category]) {
                categories[doc.category].items.push({ key, ...doc });
            }
        });

        // HTML generálása
        let navHtml = '';
        Object.entries(categories).forEach(([categoryKey, category]) => {
            if (category.items.length > 0) {
                navHtml += `
                    <div class="sidebar-section">
                        <div class="sidebar-title">${category.title}</div>
                        ${category.items.map(item => `
                            <a href="#${item.key}" 
                               class="sidebar-link" 
                               data-doc-key="${item.key}"
                               title="${item.difficulty || ''} ${item.timeEstimate || ''}">
                                ${item.title}
                                ${item.difficulty ? `<span class="text-xs opacity-75">${item.difficulty}</span>` : ''}
                            </a>
                        `).join('')}
                    </div>
                `;
            }
        });

        return navHtml;
    }

    /**
     * Search index generálása a markdown tartalmakból
     */
    async generateSearchIndex() {
        const searchIndex = [];

        for (const [key, doc] of Object.entries(this.docsStructure)) {
            try {
                const content = await this.loadMarkdownContent(doc.path);
                
                // Text extraction HTML-ből
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = content;
                const textContent = tempDiv.textContent || tempDiv.innerText;

                searchIndex.push({
                    key: key,
                    title: doc.title,
                    content: textContent.toLowerCase(),
                    path: doc.path,
                    category: doc.category,
                    url: `#${key}`
                });
            } catch (error) {
                console.warn(`Failed to index ${key}:`, error);
            }
        }

        return searchIndex;
    }

    /**
     * Document megjelenítése a content area-ban
     */
    async renderDocument(docKey) {
        const doc = this.docsStructure[docKey];
        if (!doc) {
            this.renderNotFound(docKey);
            return;
        }

        // Loading state
        const contentArea = document.getElementById('dynamic-content');
        if (contentArea) {
            contentArea.innerHTML = `
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="ml-3 text-gray-600 dark:text-gray-400">Dokumentum betöltése...</span>
                </div>
            `;
        }

        try {
            const htmlContent = await this.loadMarkdownContent(doc.path);
            
            if (contentArea) {
                contentArea.innerHTML = `
                    <div class="docs-article">
                        <div class="mb-6">
                            <nav class="text-sm breadcrumbs">
                                <a href="#index" class="text-blue-600 dark:text-blue-400 hover:underline">Dokumentáció</a>
                                <span class="mx-2 text-gray-400">›</span>
                                <span class="text-gray-600 dark:text-gray-400">${doc.title}</span>
                            </nav>
                        </div>
                        <article class="prose prose-lg max-w-none dark:prose-invert">
                            ${htmlContent}
                        </article>
                        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                📁 Forrás: <code>${doc.path}</code>
                            </p>
                        </div>
                    </div>
                `;
            }

            // Active navigation frissítése
            this.updateActiveNavigation(docKey);

        } catch (error) {
            console.error('Error rendering document:', error);
            if (contentArea) {
                contentArea.innerHTML = this.getErrorContent(doc.path, error.message);
            }
        }
    }

    /**
     * 404 oldal megjelenítése
     */
    renderNotFound(docKey) {
        const contentArea = document.getElementById('dynamic-content');
        if (contentArea) {
            contentArea.innerHTML = `
                <div class="text-center py-12">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                        404 - Dokumentum nem található
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        A keresett dokumentum (<code>${docKey}</code>) nem létezik.
                    </p>
                    <a href="#index" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg inline-block">
                        ← Vissza a főoldalra
                    </a>
                </div>
            `;
        }
    }

    /**
     * Active navigation elem frissítése
     */
    updateActiveNavigation(activeKey) {
        // Összes link inactive
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.classList.remove('active');
        });

        // Active link kijelölése
        const activeLink = document.querySelector(`[data-doc-key="${activeKey}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
    }

    /**
     * URL routing kezelése
     */
    setupRouting() {
        // Hash change listener
        window.addEventListener('hashchange', () => {
            const hash = window.location.hash.substring(1);
            const docKey = hash || 'index';
            this.renderDocument(docKey);
        });

        // Sidebar link click handler
        document.addEventListener('click', (e) => {
            if (e.target.matches('.sidebar-link') || e.target.closest('.sidebar-link')) {
                const link = e.target.matches('.sidebar-link') ? e.target : e.target.closest('.sidebar-link');
                const docKey = link.getAttribute('data-doc-key');
                if (docKey) {
                    e.preventDefault();
                    window.location.hash = docKey;
                }
            }
        });

        // Initial load
        const initialHash = window.location.hash.substring(1) || 'index';
        this.renderDocument(initialHash);
    }

    /**
     * Initialisálás
     */
    async init() {
        console.log('🚀 Docs Content Loader inicializálása...');
        
        try {
            // Docs struktúra betöltése
            await this.loadDocsStructure();
            
            // Navigation generálása és beillesztése
            const sidebar = document.querySelector('.docs-sidebar nav');
            if (sidebar) {
                sidebar.innerHTML = this.generateNavigation();
            }
            
            // Routing setup
            this.setupRouting();
            
            // Search index generálása (background)
            this.generateSearchIndex().then(searchIndex => {
                console.log('📊 Search index generálva:', searchIndex.length, 'dokumentum');
                window.docsSearchIndex = searchIndex;
                
                // Trigger search system update
                if (window.updateLocalSearchIndex) {
                    window.updateLocalSearchIndex(searchIndex);
                }
            });

            console.log('✅ Docs Content Loader kész!');
            
        } catch (error) {
            console.error('❌ Docs Content Loader inicializálási hiba:', error);
        }
    }
}

// Global instance létrehozása
window.docsContentLoader = new DocsContentLoader();

// Auto-init when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.docsContentLoader.init();
    });
} else {
    window.docsContentLoader.init();
}
