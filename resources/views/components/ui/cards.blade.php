<!-- Card Components Showcase -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Basic Card -->
    <div class="bg-white dark:bg-secondary-800 rounded-lg shadow-soft border border-secondary-200 dark:border-secondary-700 p-6">
        <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100 mb-2">Basic Card</h3>
        <p class="text-secondary-600 dark:text-secondary-400 text-sm">
            This is a basic card component with some content. Perfect for displaying information in a clean, organized way.
        </p>
    </div>

    <!-- Card with Header -->
    <div class="bg-white dark:bg-secondary-800 rounded-lg shadow-soft border border-secondary-200 dark:border-secondary-700 overflow-hidden">
        <div class="px-6 py-4 bg-secondary-50 dark:bg-secondary-700 border-b border-secondary-200 dark:border-secondary-600">
            <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100">Card with Header</h3>
        </div>
        <div class="px-6 py-4">
            <p class="text-secondary-600 dark:text-secondary-400 text-sm">
                This card has a distinct header section that stands out from the content area.
            </p>
        </div>
    </div>

    <!-- Card with Footer -->
    <div class="bg-white dark:bg-secondary-800 rounded-lg shadow-soft border border-secondary-200 dark:border-secondary-700 overflow-hidden">
        <div class="px-6 py-4">
            <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100 mb-2">Card with Footer</h3>
            <p class="text-secondary-600 dark:text-secondary-400 text-sm">
                This card includes a footer section for actions or additional information.
            </p>
        </div>
        <div class="px-6 py-3 bg-secondary-50 dark:bg-secondary-700 border-t border-secondary-200 dark:border-secondary-600">
            <button class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 text-sm font-medium">
                Learn More
            </button>
        </div>
    </div>

    <!-- Profile Card -->
    <div class="bg-white dark:bg-secondary-800 rounded-lg shadow-soft border border-secondary-200 dark:border-secondary-700 p-6 text-center">
        <div class="w-16 h-16 bg-primary-600 rounded-full mx-auto mb-4 flex items-center justify-center">
            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100 mb-1">John Doe</h3>
        <p class="text-secondary-600 dark:text-secondary-400 text-sm mb-4">Software Developer</p>
        <button class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
            View Profile
        </button>
    </div>

    <!-- Stats Card -->
    <div class="bg-white dark:bg-secondary-800 rounded-lg shadow-soft border border-secondary-200 dark:border-secondary-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-secondary-600 dark:text-secondary-400 text-sm font-medium">Total Users</p>
                <p class="text-2xl font-bold text-secondary-900 dark:text-secondary-100">1,234</p>
            </div>
            <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/20 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" />
                </svg>
            </div>
        </div>
        <div class="mt-4 flex items-center">
            <span class="text-success-600 text-sm font-medium">↗ 12%</span>
            <span class="text-secondary-500 dark:text-secondary-400 text-sm ml-2">from last month</span>
        </div>
    </div>

    <!-- Feature Card -->
    <div class="bg-gradient-to-br from-primary-500 to-primary-600 rounded-lg shadow-soft text-white p-6">
        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
            </svg>
        </div>
        <h3 class="text-lg font-semibold mb-2">Premium Feature</h3>
        <p class="text-white/90 text-sm mb-4">
            Unlock advanced functionality with our premium features designed for power users.
        </p>
        <button class="w-full px-4 py-2 bg-white text-primary-600 hover:bg-white/90 text-sm font-medium rounded-lg transition-colors duration-200">
            Upgrade Now
        </button>
    </div>

    <!-- Interactive Card -->
    <div class="bg-white dark:bg-secondary-800 rounded-lg shadow-soft border border-secondary-200 dark:border-secondary-700 p-6 hover:shadow-medium transform hover:-translate-y-1 transition-all duration-200 cursor-pointer">
        <div class="w-12 h-12 bg-warning-100 dark:bg-warning-900/20 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-warning-600 dark:text-warning-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100 mb-2">Interactive Card</h3>
        <p class="text-secondary-600 dark:text-secondary-400 text-sm">
            This card has hover effects and can be clicked. Perfect for navigation or call-to-action cards.
        </p>
    </div>

    <!-- Loading Card -->
    <div class="bg-white dark:bg-secondary-800 rounded-lg shadow-soft border border-secondary-200 dark:border-secondary-700 p-6">
        <div class="animate-pulse">
            <div class="w-12 h-12 bg-secondary-200 dark:bg-secondary-700 rounded-lg mb-4"></div>
            <div class="h-4 bg-secondary-200 dark:bg-secondary-700 rounded mb-2"></div>
            <div class="h-3 bg-secondary-200 dark:bg-secondary-700 rounded w-3/4"></div>
        </div>
    </div>

    <!-- Full-width Card -->
    <div class="md:col-span-2 lg:col-span-3 bg-white dark:bg-secondary-800 rounded-lg shadow-soft border border-secondary-200 dark:border-secondary-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100">Full-width Card</h3>
            <button class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 text-sm font-medium">
                View All
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-secondary-900 dark:text-secondary-100">1,234</div>
                <div class="text-secondary-600 dark:text-secondary-400 text-sm">Active Users</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-secondary-900 dark:text-secondary-100">5,678</div>
                <div class="text-secondary-600 dark:text-secondary-400 text-sm">Page Views</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-secondary-900 dark:text-secondary-100">9.1%</div>
                <div class="text-secondary-600 dark:text-secondary-400 text-sm">Conversion Rate</div>
            </div>
        </div>
    </div>
</div>
