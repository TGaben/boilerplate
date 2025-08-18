/**
 * Theme Management System
 * Handles dark/light mode toggle functionality
 */

class ThemeManager {
    constructor() {
        this.storageKey = 'theme';
        this.themes = ['light', 'dark', 'system'];
        this.currentTheme = this.getStoredTheme();
        
        this.init();
    }

    init() {
        // Set initial theme
        this.applyTheme(this.currentTheme);
        
        // Setup theme toggle button
        this.setupThemeToggle();
        
        // Listen for system theme changes
        this.setupSystemThemeListener();
        
        // Listen for storage changes (multi-tab support)
        this.setupStorageListener();
    }

    getStoredTheme() {
        const stored = localStorage.getItem(this.storageKey);
        if (stored && this.themes.includes(stored)) {
            return stored;
        }
        return 'system'; // Default to system preference
    }

    getSystemTheme() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    getEffectiveTheme() {
        return this.currentTheme === 'system' ? this.getSystemTheme() : this.currentTheme;
    }

    applyTheme(theme) {
        const effectiveTheme = theme === 'system' ? this.getSystemTheme() : theme;
        
        // Apply to document
        if (effectiveTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // Update meta theme-color for mobile browsers
        this.updateMetaThemeColor(effectiveTheme);
        
        // Store preference
        localStorage.setItem(this.storageKey, theme);
        this.currentTheme = theme;

        // Dispatch custom event
        window.dispatchEvent(new CustomEvent('themeChanged', {
            detail: { theme: theme, effectiveTheme: effectiveTheme }
        }));
    }

    updateMetaThemeColor(theme) {
        let metaTag = document.querySelector('meta[name="theme-color"]');
        if (!metaTag) {
            metaTag = document.createElement('meta');
            metaTag.name = 'theme-color';
            document.head.appendChild(metaTag);
        }
        
        metaTag.content = theme === 'dark' ? '#1e293b' : '#ffffff';
    }

    toggleTheme() {
        const themes = ['light', 'dark', 'system'];
        const currentIndex = themes.indexOf(this.currentTheme);
        const nextTheme = themes[(currentIndex + 1) % themes.length];
        
        this.applyTheme(nextTheme);
    }

    setupThemeToggle() {
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                this.toggleTheme();
            });

            // Update tooltip/aria-label based on current theme
            this.updateToggleButton(themeToggle);
            
            // Listen for theme changes to update button
            window.addEventListener('themeChanged', () => {
                this.updateToggleButton(themeToggle);
            });
        }
    }

    updateToggleButton(button) {
        const effectiveTheme = this.getEffectiveTheme();
        const nextTheme = this.getNextThemeName();
        
        button.setAttribute('aria-label', `Switch to ${nextTheme} mode`);
        button.setAttribute('title', `Switch to ${nextTheme} mode`);
    }

    getNextThemeName() {
        const themes = ['light', 'dark', 'system'];
        const currentIndex = themes.indexOf(this.currentTheme);
        const nextTheme = themes[(currentIndex + 1) % themes.length];
        
        if (nextTheme === 'system') {
            return `system (${this.getSystemTheme()})`;
        }
        return nextTheme;
    }

    setupSystemThemeListener() {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        mediaQuery.addEventListener('change', () => {
            if (this.currentTheme === 'system') {
                this.applyTheme('system');
            }
        });
    }

    setupStorageListener() {
        window.addEventListener('storage', (e) => {
            if (e.key === this.storageKey && e.newValue) {
                this.applyTheme(e.newValue);
            }
        });
    }

    // Public API
    setTheme(theme) {
        if (this.themes.includes(theme)) {
            this.applyTheme(theme);
        }
    }

    getCurrentTheme() {
        return this.currentTheme;
    }

    getEffectiveTheme() {
        return this.getEffectiveTheme();
    }
}

// Initialize theme manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
});

// Mobile menu toggle functionality
document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', () => {
            const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
            
            mobileMenuButton.setAttribute('aria-expanded', !isExpanded);
            mobileMenu.classList.toggle('hidden');
            
            // Update hamburger icon (you can add animation here)
            const icon = mobileMenuButton.querySelector('svg');
            if (icon) {
                icon.style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(90deg)';
            }
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!mobileMenuButton.contains(e.target) && !mobileMenu.contains(e.target)) {
                mobileMenu.classList.add('hidden');
                mobileMenuButton.setAttribute('aria-expanded', 'false');
            }
        });

        // Close mobile menu on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.add('hidden');
                mobileMenuButton.setAttribute('aria-expanded', 'false');
            }
        });
    }
});

// Smooth scrolling for anchor links
document.addEventListener('DOMContentLoaded', () => {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', (e) => {
            const targetId = link.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Toast notification system
window.showToast = function(message, type = 'info', duration = 5000) {
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) return;

    const toast = document.createElement('div');
    toast.className = `
        toast max-w-sm w-full bg-white dark:bg-secondary-800 shadow-lg rounded-lg pointer-events-auto 
        border border-secondary-200 dark:border-secondary-700 transform transition-all duration-300 
        translate-x-full opacity-0
    `;

    const typeColors = {
        success: 'border-l-4 border-l-success-500',
        error: 'border-l-4 border-l-error-500',
        warning: 'border-l-4 border-l-warning-500',
        info: 'border-l-4 border-l-primary-500'
    };

    toast.className += ` ${typeColors[type] || typeColors.info}`;

    toast.innerHTML = `
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-1">
                    <p class="text-sm text-secondary-900 dark:text-secondary-100">${message}</p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button class="toast-close bg-white dark:bg-secondary-800 rounded-md inline-flex text-secondary-400 hover:text-secondary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `;

    toastContainer.appendChild(toast);

    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
    }, 100);

    // Auto remove
    const removeToast = () => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    };

    // Close button
    const closeButton = toast.querySelector('.toast-close');
    closeButton.addEventListener('click', removeToast);

    // Auto remove after duration
    if (duration > 0) {
        setTimeout(removeToast, duration);
    }
};

export { ThemeManager };
