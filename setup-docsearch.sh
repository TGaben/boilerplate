#!/bin/bash

# =================================================================
# Algolia DocSearch Setup Script
# =================================================================

set -e

echo "🚀 Algolia DocSearch Setup kezdése..."

# 1. NPM dependencies telepítése
echo "📦 NPM dependencies telepítése..."
npm install

# 2. Docs-site könyvtár létrehozása
echo "📁 Docs-site struktúra létrehozása..."
mkdir -p docs-site

# 3. Index.html létrehozása
echo "📄 Index.html létrehozása..."
cat > docs-site/index.html << 'EOF'
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Boilerplate Dokumentáció</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Laravel Boilerplate - Production-ready PHP alkalmazás fejlesztési alap. Filament admin, Spatie permissions, tesztelési környezet.">
    <meta name="keywords" content="Laravel, PHP, Boilerplate, Filament, Admin Panel, Authentication, Permissions">
    <meta name="author" content="Laravel Boilerplate Team">
    
    <!-- TailwindCSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.4/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Algolia DocSearch CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3" />
    
    <!-- Custom Styles -->
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #374151;
            background-color: #f9fafb;
        }
        
        /* Layout */
        .docs-container {
            display: flex;
            min-height: 100vh;
        }
        
        .docs-sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid #e5e7eb;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .docs-main {
            margin-left: 280px;
            padding: 32px;
            flex: 1;
            max-width: calc(100% - 280px);
        }
        
        /* Navigation */
        .docs-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            position: fixed;
            top: 0;
            left: 280px;
            right: 0;
            z-index: 50;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .docs-nav h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .docs-search {
            padding: 8px 16px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #f9fafb;
            min-width: 240px;
        }
        
        .dark-toggle {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px 12px;
            margin-left: 12px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .dark-toggle:hover {
            background: #e5e7eb;
        }
        
        /* Sidebar */
        .sidebar-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
            margin-top: 24px;
        }
        
        .sidebar-title:first-child {
            margin-top: 0;
        }
        
        .sidebar-link {
            display: block;
            padding: 8px 12px;
            color: #6b7280;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 4px;
            transition: all 0.2s;
        }
        
        .sidebar-link:hover {
            background: #f3f4f6;
            color: #374151;
        }
        
        .sidebar-link.active {
            background: #3b82f6;
            color: white;
        }
        
        /* Main content */
        .docs-content {
            margin-top: 80px;
        }
        
        /* Stats cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            padding: 24px;
            border-radius: 12px;
            text-align: center;
            color: white;
            font-weight: 600;
        }
        
        .stat-card h3 {
            font-size: 2.5rem;
            margin-bottom: 8px;
        }
        
        .stat-card p {
            opacity: 0.9;
        }
        
        /* Quick start section */
        .quick-start {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 32px;
            border-radius: 12px;
            margin-bottom: 32px;
        }
        
        .quick-start h2 {
            font-size: 1.875rem;
            margin-bottom: 16px;
        }
        
        .quick-start pre {
            background: rgba(0,0,0,0.2);
            padding: 16px;
            border-radius: 8px;
            overflow-x: auto;
            margin-top: 16px;
        }
        
        /* Search results */
        #search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .search-result {
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
        }
        
        .search-result:hover {
            background: #f9fafb;
        }
        
        .search-result:last-child {
            border-bottom: none;
        }
        
        .search-hint {
            padding: 8px 16px;
            color: #6b7280;
            font-size: 0.875rem;
            text-align: center;
        }
        
        /* Dark mode styles */
        .dark-mode {
            background-color: #1f2937 !important;
            color: #f3f4f6 !important;
        }
        
        .dark-mode .docs-nav,
        .dark-mode .docs-sidebar {
            background-color: #374151 !important;
            color: #f3f4f6 !important;
            border-color: #4b5563 !important;
        }
        
        .dark-mode .sidebar-link {
            color: #d1d5db !important;
        }
        
        .dark-mode .sidebar-link:hover {
            background-color: #4b5563 !important;
            color: #f3f4f6 !important;
        }
        
        .dark-mode .docs-search {
            background-color: #4b5563 !important;
            border-color: #6b7280 !important;
            color: #f3f4f6 !important;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .docs-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .docs-sidebar.open {
                transform: translateX(0);
            }
            
            .docs-main {
                margin-left: 0;
                max-width: 100%;
            }
            
            .docs-nav {
                left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="docs-nav">
        <h1>🚀 Laravel Boilerplate Docs</h1>
        <div style="display: flex; align-items: center; position: relative;">
            <input type="text" 
                   id="docsearch" 
                   class="docs-search" 
                   placeholder="Keresés a dokumentációban...">
            <input type="text" 
                   id="fallback-search" 
                   class="docs-search" 
                   style="display: none;"
                   placeholder="Keresés a dokumentációban...">
            <button onclick="toggleDarkMode()" class="dark-toggle">🌙</button>
        </div>
    </nav>

    <div class="docs-container">
        <!-- Sidebar -->
        <aside class="docs-sidebar">
            <div class="sidebar-section">
                <div class="sidebar-title">⚡ Gyors Kezdés</div>
                <a href="#quick-start" class="sidebar-link">🚀 2 Perces Setup</a>
                <a href="#automations" class="sidebar-link">🤖 Automatizálás</a>
                <a href="#environment" class="sidebar-link">🌍 Környezeti Sablonok</a>
            </div>
            
            <div class="sidebar-section">
                <div class="sidebar-title">🔧 Core Komponensek</div>
                <a href="#authentication" class="sidebar-link">🔐 Authentication</a>
                <a href="#admin-panel" class="sidebar-link">🎛️ Admin Panel</a>
                <a href="#testing" class="sidebar-link">🧪 Testing</a>
                <a href="#quality" class="sidebar-link">⚡ Quality Gates</a>
            </div>
            
            <div class="sidebar-section">
                <div class="sidebar-title">🍳 Recipes</div>
                <a href="#api-development" class="sidebar-link">🔌 API Development</a>
                <a href="#file-uploads" class="sidebar-link">💾 File Uploads</a>
                <a href="#multi-tenancy" class="sidebar-link">🏗️ Multi-Tenancy</a>
                <a href="#all-recipes" class="sidebar-link">📋 Összes Recipe</a>
            </div>
            
            <div class="sidebar-section">
                <div class="sidebar-title">🚀 Deployment</div>
                <a href="#architecture" class="sidebar-link">🏗️ Architektúra</a>
                <a href="#docker" class="sidebar-link">🐳 Docker</a>
                <a href="#cloud" class="sidebar-link">☁️ Cloud Platforms</a>
            </div>
            
            <div class="sidebar-section">
                <div class="sidebar-title">🔧 Troubleshooting</div>
                <a href="#common-issues" class="sidebar-link">❗ Gyakori Problémák</a>
                <a href="#performance" class="sidebar-link">⚡ Teljesítmény</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="docs-main">
            <div class="docs-content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h3>2 perc</h3>
                        <p>Setup idő</p>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <h3>161</h3>
                        <p>Átfogó teszt</p>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <h3>47</h3>
                        <p>Recipe</p>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <h3>Max</h3>
                        <p>PHPStan Level</p>
                    </div>
                </div>

                <!-- Quick Start Section -->
                <section id="quick-start" class="quick-start">
                    <h2>⚡ Gyors Kezdés</h2>
                    <p>Egylenyeres paranccsal telepíthető, production-ready Laravel alkalmazás. Heteket spórolj meg a fejlesztésből, kezdj el funkciókkal foglalkozni!</p>
                    
                    <pre><code># 1. Repository klónozása
git clone https://github.com/Team/boilerplate.git
cd boilerplate

# 2. Egyparncs setup ✨
./scripts/quick-start.sh

# ✅ Kész! Admin credentials:
# Email: admin@admin.com
# Password: password</code></pre>
                    
                    <div style="margin-top: 16px; padding: 12px; background: rgba(255,255,255,0.1); border-radius: 6px;">
                        <strong>✅ Mit kapsz automatikusan:</strong><br>
                        🔐 Authentication & Permissions • 🎛️ Filament Admin Panel • 🧪 Testing Foundation • ⚡ Quality Gates
                    </div>
                </section>

                <!-- Dynamic Content Sections -->
                <div id="dynamic-content">
                    <section id="authentication" style="margin-top: 48px;">
                        <h2 style="font-size: 1.875rem; font-weight: 700; margin-bottom: 16px; color: #1f2937;">🔐 Authentication & Permissions</h2>
                        <p style="color: #6b7280; margin-bottom: 24px;">Spatie Laravel Permission alapú szerepkör és jogosultság kezelés. Filament adminnal teljes körű felhasználó menedzsment.</p>
                    </section>

                    <section id="admin-panel" style="margin-top: 48px;">
                        <h2 style="font-size: 1.875rem; font-weight: 700; margin-bottom: 16px; color: #1f2937;">🎛️ Admin Panel (Filament)</h2>
                        <p style="color: #6b7280; margin-bottom: 24px;">Modern, reszponzív admin interface. Users, Roles, Permissions kezelés. Dashboard widgets és testreszabható resource view-k.</p>
                    </section>

                    <section id="testing" style="margin-top: 48px;">
                        <h2 style="font-size: 1.875rem; font-weight: 700; margin-bottom: 16px; color: #1f2937;">🧪 Testing Foundation</h2>
                        <p style="color: #6b7280; margin-bottom: 24px;">161 átfogó teszt: Unit, Feature, Browser tests. Teljes test coverage az auth, admin és permission funkcionalitáshoz.</p>
                    </section>

                    <section id="quality" style="margin-top: 48px;">
                        <h2 style="font-size: 1.875rem; font-weight: 700; margin-bottom: 16px; color: #1f2937;">⚡ Quality Gates</h2>
                        <p style="color: #6b7280; margin-bottom: 24px;">PHPStan Max Level statikus analízis, Laravel Pint code style, automated quality checks. Production-ready kód garantált.</p>
                    </section>

                    <section id="api-development" style="margin-top: 48px;">
                        <h2 style="font-size: 1.875rem; font-weight: 700; margin-bottom: 16px; color: #1f2937;">🔌 API Development Recipe</h2>
                        <p style="color: #6b7280; margin-bottom: 24px;">Moduláris funkciók telepítése API development file uploads multi-tenancy...</p>
                    </section>

                    <section id="file-uploads" style="margin-top: 48px;">
                        <h2 style="font-size: 1.875rem; font-weight: 700; margin-bottom: 16px; color: #1f2937;">💾 File Upload System</h2>
                        <p style="color: #6b7280; margin-bottom: 24px;">Fájlfeltöltés képoptimalizálással, storage management és cloud integráció támogatással.</p>
                    </section>

                    <section id="multi-tenancy" style="margin-top: 48px;">
                        <h2 style="font-size: 1.875rem; font-weight: 700; margin-bottom: 16px; color: #1f2937;">🏗️ Multi-Tenancy Recipe</h2>
                        <p style="color: #6b7280; margin-bottom: 24px;">SaaS multi-tenant architektúra tenant izolációval és adatbázis szeparációval.</p>
                    </section>

                    <section id="all-recipes" style="margin-top: 48px;">
                        <h2 style="font-size: 1.875rem; font-weight: 700; margin-bottom: 16px; color: #1f2937;">📋 Összes Recipe</h2>
                        <p style="color: #6b7280; margin-bottom: 24px;">Teljes recipe lista kategorikusan rendezve, nehézségi szintekkel és funkció leírásokkal.</p>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-top: 32px;">
                            <div style="background: #ddd6fe; padding: 24px; border-radius: 12px; border-left: 4px solid #8b5cf6;">
                                <h3 style="font-weight: 600; color: #1f2937; margin-bottom: 8px;">🔌 API Development Recipe</h3>
                                <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 12px;">Moduláris funkciók telepítése API development file uploads multi-tenancy...</p>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="background: #10b981; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 500;">Easy</span>
                                    <a href="#api-development" style="color: #8b5cf6; text-decoration: none; font-weight: 500;">→ Részletek</a>
                                </div>
                            </div>
                            
                            <div style="background: #fed7d7; padding: 24px; border-radius: 12px; border-left: 4px solid #f56565;">
                                <h3 style="font-weight: 600; color: #1f2937; margin-bottom: 8px;">💾 File Uploads Recipe</h3>
                                <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 12px;">Fájlfeltöltés képoptimalizálással, storage management és cloud integráció támogatással.</p>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="background: #f59e0b; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 500;">Medium</span>
                                    <a href="#file-uploads" style="color: #f56565; text-decoration: none; font-weight: 500;">→ Részletek</a>
                                </div>
                            </div>
                            
                            <div style="background: #e0e7ff; padding: 24px; border-radius: 12px; border-left: 4px solid #6366f1;">
                                <h3 style="font-weight: 600; color: #1f2937; margin-bottom: 8px;">🏗️ Multi-Tenancy Recipe</h3>
                                <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 12px;">SaaS multi-tenant architektúra tenant izolációval és adatbázis szeparációval.</p>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="background: #8b5cf6; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 500;">Advanced</span>
                                    <a href="#multi-tenancy" style="color: #6366f1; text-decoration: none; font-weight: 500;">→ Részletek</a>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="architecture" style="margin-top: 48px;">
                        <h2 style="font-size: 1.875rem; font-weight: 700; margin-bottom: 16px; color: #1f2937;">🏗️ Architektúra</h2>
                        <p style="color: #6b7280; margin-bottom: 24px;">Rendszerterv és technológiai döntések magyarázata. Docker, Laravel Sail, modern PHP stack.</p>
                    </section>

                    <section id="docker" style="margin-top: 48px;">
                        <h2 style="font-size: 1.875rem; font-weight: 700; margin-bottom: 16px; color: #1f2937;">🐳 Docker</h2>
                        <p style="color: #6b7280; margin-bottom: 24px;">Laravel Sail alapú fejlesztői környezet és production Docker deployment útmutatók.</p>
                    </section>

                    <section id="common-issues" style="margin-top: 48px;">
                        <h2 style="font-size: 1.875rem; font-weight: 700; margin-bottom: 16px; color: #1f2937;">❗ Gyakori Problémák</h2>
                        <p style="color: #6b7280; margin-bottom: 24px;">Troubleshooting útmutató a leggyakoribb setup és fejlesztési problémákhoz.</p>
                    </section>

                    <section id="performance" style="margin-top: 48px;">
                        <h2 style="font-size: 1.875rem; font-weight: 700; margin-bottom: 16px; color: #1f2937;">⚡ Teljesítmény</h2>
                        <p style="color: #6b7280; margin-bottom: 24px;">Performance debugging és optimalizálási tippek Redis cache-sel és query optimalizálással.</p>
                    </section>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@docsearch/js@3"></script>
    <script type="module" src="/search-fallback.js"></script>
    <script>
        // Dark mode toggle with visual feedback
        function toggleDarkMode() {
            const body = document.body;
            const isDark = body.classList.contains('dark-mode');
            
            if (isDark) {
                // Switch to light mode
                body.classList.remove('dark-mode');
                body.style.backgroundColor = '#f9fafb';
                body.style.color = '#374151';
                localStorage.setItem('darkMode', 'false');
                console.log('🌅 Switched to light mode');
            } else {
                // Switch to dark mode
                body.classList.add('dark-mode');
                body.style.backgroundColor = '#1f2937';
                body.style.color = '#f3f4f6';
                localStorage.setItem('darkMode', 'true');
                console.log('🌙 Switched to dark mode');
            }
        }
        
        // Initialize dark mode from localStorage
        function initDarkMode() {
            const savedMode = localStorage.getItem('darkMode');
            if (savedMode === 'true') {
                document.body.classList.add('dark-mode');
                document.body.style.backgroundColor = '#1f2937';
                document.body.style.color = '#f3f4f6';
                console.log('🌙 Dark mode initialized from localStorage');
            } else {
                console.log('🌅 Light mode initialized');
            }
        }
        
        // Algolia DocSearch initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Try to initialize Algolia DocSearch for production
            if (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                try {
                    docsearch({
                        appId: 'YOUR_APP_ID',
                        apiKey: 'YOUR_SEARCH_API_KEY', 
                        indexName: 'laravel_boilerplate_docs',
                        container: '#docsearch',
                        placeholder: 'Keresés a dokumentációban...',
                        searchParameters: {
                            facetFilters: ['lang:hu']
                        },
                        transformItems(items) {
                            return items.map(item => ({
                                ...item,
                                url: item.url.replace('https://your-domain.com', '')
                            }));
                        }
                    });
                    console.log('🔍 Algolia DocSearch initialized');
                } catch (error) {
                    console.warn('⚠️ Algolia DocSearch failed, falling back to local search');
                    initFallbackSearch();
                }
            } else {
                // Development environment - use fallback search
                console.log('🔍 Development mode - using fallback search');
                initFallbackSearch();
            }
        });
        
        function initFallbackSearch() {
            // Hide Algolia search, show fallback
            document.getElementById('docsearch').style.display = 'none';
            document.getElementById('fallback-search').style.display = 'block';
            
            // Wait for search-fallback.js to load
            const checkSearch = setInterval(() => {
                if (window.performFallbackSearch) {
                    clearInterval(checkSearch);
                    const searchInput = document.getElementById('fallback-search');
                    searchInput.addEventListener('input', function(e) {
                        const query = e.target.value.toLowerCase();
                        if (query.length > 2) {
                            window.performFallbackSearch(query);
                        } else {
                            // Clear results
                            const existingResults = document.getElementById('search-results');
                            if (existingResults) {
                                existingResults.remove();
                            }
                        }
                    });
                    console.log('✅ Fallback search initialized');
                }
            }, 100);
        }
        
        // Navigation handling with smooth scrolling
        function initializeNavigation() {
            console.log('🔗 Initializing navigation...');
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                        console.log('🎯 Navigated to:', this.getAttribute('href'));
                    }
                });
            });
        }
        
        // Initialize everything
        document.addEventListener('DOMContentLoaded', function() {
            initializeNavigation();
            initDarkMode();
            console.log('🚀 Documentation site initialized');
        });
        
        // Also initialize if DOM is already loaded
        if (document.readyState !== 'loading') {
            initializeNavigation();
            initDarkMode();
            console.log('🚀 Documentation site initialized (immediate)');
        }
    </script>
</body>
</html>
EOF

# 4. Search-fallback.js létrehozása
echo "🔍 Search fallback JS létrehozása..."
cat > docs-site/search-fallback.js << 'EOF'
// Development fallback search functionality
// Used when Algolia DocSearch is not available (localhost)

// Search index with documentation content
window.searchIndex = [
    {
        title: "Gyors Kezdés - 2 Perces Setup",
        url: "#quick-start",
        content: "production-ready Laravel alkalmazás telepíthető paranccsal setup admin credentials",
        category: "setup",
        difficulty: "easy"
    },
    {
        title: "Authentication & Permissions",
        url: "#authentication",
        content: "Spatie Laravel Permission szerepkör jogosultság kezelés Filament admin felhasználó menedzsment",
        category: "core",
        difficulty: "easy"
    },
    {
        title: "Admin Panel (Filament)",
        url: "#admin-panel",
        content: "Modern reszponzív admin interface Users Roles Permissions dashboard widgets resource views",
        category: "core",
        difficulty: "easy"
    },
    {
        title: "Testing Foundation",
        url: "#testing",
        content: "161 átfogó teszt Unit Feature Browser tests coverage auth admin permission",
        category: "core",
        difficulty: "medium"
    },
    {
        title: "Quality Gates",
        url: "#quality",
        content: "PHPStan Max Level statikus analízis Laravel Pint code style automated quality checks production-ready",
        category: "core",
        difficulty: "medium"
    },
    {
        title: "API Development Recipe",
        url: "#api-development",
        content: "moduláris funkciók telepítése API development file uploads multi-tenancy",
        category: "recipes",
        difficulty: "easy"
    },
    {
        title: "File Upload System",
        url: "#file-uploads",
        content: "fájlfeltöltés képoptimalizálás storage management cloud integráció támogatás",
        category: "recipes",
        difficulty: "medium"
    },
    {
        title: "Multi-Tenancy Recipe",
        url: "#multi-tenancy",
        content: "SaaS multi-tenant architektúra tenant izoláció adatbázis szeparáció",
        category: "recipes",
        difficulty: "advanced"
    },
    {
        title: "Összes Recipe",
        url: "#all-recipes",
        content: "teljes recipe lista kategorikus rendezés nehézségi szintek funkció leírások",
        category: "recipes",
        difficulty: "easy"
    },
    {
        title: "Architektúra",
        url: "#architecture",
        content: "rendszerterv technológiai döntések Docker Laravel Sail modern PHP stack",
        category: "deployment",
        difficulty: "medium"
    },
    {
        title: "Docker",
        url: "#docker",
        content: "Laravel Sail fejlesztői környezet production Docker deployment útmutatók",
        category: "deployment",
        difficulty: "medium"
    },
    {
        title: "Gyakori Problémák",
        url: "#common-issues",
        content: "troubleshooting útmutató setup fejlesztési problémák megoldások",
        category: "troubleshooting",
        difficulty: "easy"
    },
    {
        title: "Teljesítmény Optimalizálás",
        url: "#performance",
        content: "performance debugging optimalizálás Redis cache query optimalizálás",
        category: "troubleshooting",
        difficulty: "advanced"
    }
];

// Define search function
window.performFallbackSearch = function(query) {
    console.log('🔍 performFallbackSearch called with:', query);
    const results = window.searchIndex.filter(item => 
        item.title.toLowerCase().includes(query) ||
        item.content.toLowerCase().includes(query) ||
        item.category.toLowerCase().includes(query)
    );
    console.log('Found results:', results.length);
    window.displaySearchResults(results, query);
}

window.displaySearchResults = function displaySearchResults(results, query) {
    const existingResults = document.getElementById('search-results');
    if (existingResults) {
        existingResults.remove();
    }

    const searchContainer = document.getElementById('fallback-search').parentElement;
    const resultsContainer = document.createElement('div');
    resultsContainer.id = 'search-results';
    resultsContainer.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        max-height: 400px;
        overflow-y: auto;
        z-index: 1000;
        margin-top: 4px;
    `;

    if (results.length === 0) {
        resultsContainer.innerHTML = `
            <div class="search-hint">Nincs találat a(z) "${query}" keresésre</div>
        `;
    } else {
        const difficultyColors = {
            easy: '#10b981',
            medium: '#f59e0b', 
            advanced: '#8b5cf6'
        };

        results.forEach((result, index) => {
            const resultElement = document.createElement('div');
            resultElement.className = 'search-result';
            resultElement.style.cssText = `
                padding: 12px 16px;
                border-bottom: 1px solid #f3f4f6;
                cursor: pointer;
                transition: background-color 0.2s;
            `;
            
            resultElement.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
                    <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">${window.highlightQuery(result.title, query)}</div>
                    <span style="background: ${difficultyColors[result.difficulty] || '#6b7280'}; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem; text-transform: capitalize;">${result.difficulty}</span>
                </div>
                <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 6px;">${window.highlightQuery(result.content.substring(0, 100) + '...', query)}</div>
                <div style="color: #9ca3af; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">${result.category}</div>
            `;
            
            resultElement.addEventListener('click', () => window.navigateToResult(result.url));
            resultElement.addEventListener('mouseenter', () => {
                resultElement.style.backgroundColor = '#f9fafb';
            });
            resultElement.addEventListener('mouseleave', () => {
                resultElement.style.backgroundColor = 'transparent';
            });
            
            if (index === results.length - 1) {
                resultElement.style.borderBottom = 'none';
            }
            
            resultsContainer.appendChild(resultElement);
        });
    }

    searchContainer.appendChild(resultsContainer);
}

window.highlightQuery = function highlightQuery(text, query) {
    const regex = new RegExp(`(${query})`, 'gi');
    return text.replace(regex, '<mark style="background: #fef3c7; padding: 1px 2px; border-radius: 2px;">$1</mark>');
}

window.navigateToResult = function navigateToResult(url) {
    // Clear search results
    const existingResults = document.getElementById('search-results');
    if (existingResults) {
        existingResults.remove();
    }
    
    // Clear search input
    const searchInput = document.getElementById('fallback-search');
    if (searchInput) {
        searchInput.value = '';
    }
    
    // Navigate to the result
    if (url.startsWith('#')) {
        const target = document.querySelector(url);
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            console.log('🎯 Navigated to search result:', url);
        }
    } else {
        window.location.href = url;
    }
}

// Clear results when clicking outside
document.addEventListener('click', function(e) {
    const searchContainer = document.getElementById('fallback-search')?.parentElement;
    const resultsContainer = document.getElementById('search-results');
    
    if (resultsContainer && searchContainer && !searchContainer.contains(e.target)) {
        resultsContainer.remove();
    }
});

console.log('🔍 Search fallback module loaded with', window.searchIndex.length, 'items');
EOF

echo "✅ Algolia DocSearch setup kész!"
echo "🌐 Indítsd el: npm run docs:dev"
echo "🚀 Elérhető: http://localhost:3000"
