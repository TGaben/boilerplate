<!-- Alert Components Showcase -->
<div class="space-y-6">
    <!-- Basic Alerts -->
    <div>
        <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100 mb-4">Basic Alerts</h3>
        <div class="space-y-4">
            <!-- Success Alert -->
            <div class="bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 text-success-800 dark:text-success-200 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium">Successfully saved! Your changes have been applied.</span>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 text-primary-800 dark:text-primary-200 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium">New update available. Click here to refresh your page.</span>
                </div>
            </div>

            <!-- Warning Alert -->
            <div class="bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 text-warning-800 dark:text-warning-200 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium">Your trial will expire in 3 days. Upgrade to continue using all features.</span>
                </div>
            </div>

            <!-- Error Alert -->
            <div class="bg-error-50 dark:bg-error-900/20 border border-error-200 dark:border-error-800 text-error-800 dark:text-error-200 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium">Error occurred while saving. Please try again.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Dismissible Alerts -->
    <div>
        <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100 mb-4">Dismissible Alerts</h3>
        <div class="space-y-4">
            <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 text-primary-800 dark:text-primary-200 px-4 py-3 rounded-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm font-medium">This is a dismissible alert. You can close it using the X button.</span>
                    </div>
                    <button type="button" class="ml-4 text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-200">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts with Actions -->
    <div>
        <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100 mb-4">Alerts with Actions</h3>
        <div class="space-y-4">
            <div class="bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-warning-600 dark:text-warning-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-warning-800 dark:text-warning-200 mb-1">
                            Storage Almost Full
                        </h4>
                        <p class="text-sm text-warning-700 dark:text-warning-300 mb-3">
                            You're using 95% of your available storage. Upgrade your plan to get more space.
                        </p>
                        <div class="flex space-x-3">
                            <button class="bg-warning-600 hover:bg-warning-700 text-white px-3 py-1.5 text-xs font-medium rounded-md transition-colors duration-200">
                                Upgrade Plan
                            </button>
                            <button class="text-warning-700 dark:text-warning-300 hover:text-warning-800 dark:hover:text-warning-200 px-3 py-1.5 text-xs font-medium transition-colors duration-200">
                                Manage Files
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast-style Alerts -->
    <div>
        <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100 mb-4">Toast-style Alerts</h3>
        <div class="space-y-4">
            <div class="bg-white dark:bg-secondary-800 border border-secondary-200 dark:border-secondary-700 rounded-lg shadow-soft p-4 border-l-4 border-l-success-500">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-success-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-secondary-900 dark:text-secondary-100">File uploaded successfully</p>
                            <p class="text-xs text-secondary-600 dark:text-secondary-400">Your document has been processed</p>
                        </div>
                    </div>
                    <button class="text-secondary-400 hover:text-secondary-600 dark:hover:text-secondary-300">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="bg-white dark:bg-secondary-800 border border-secondary-200 dark:border-secondary-700 rounded-lg shadow-soft p-4 border-l-4 border-l-error-500">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-error-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-secondary-900 dark:text-secondary-100">Upload failed</p>
                            <p class="text-xs text-secondary-600 dark:text-secondary-400">File size exceeds the 10MB limit</p>
                        </div>
                    </div>
                    <button class="text-secondary-400 hover:text-secondary-600 dark:hover:text-secondary-300">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Demo Button -->
    <div class="pt-6">
        <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100 mb-4">Interactive Demo</h3>
        <button onclick="showToast('This is a test notification!', 'success')" 
                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
            Show Toast Notification
        </button>
    </div>
</div>
