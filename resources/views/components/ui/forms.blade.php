<!-- Form Components Showcase -->
<div class="max-w-2xl space-y-8">
    <!-- Basic Form -->
    <div class="bg-white dark:bg-secondary-800 rounded-lg shadow-soft border border-secondary-200 dark:border-secondary-700 p-6">
        <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100 mb-6">Basic Form Elements</h3>
        <form class="space-y-6">
            <!-- Text Input -->
            <div>
                <label for="email" class="block text-sm font-medium text-secondary-900 dark:text-secondary-100 mb-2">
                    Email Address
                </label>
                <input type="email" id="email" name="email" 
                       class="w-full px-3 py-2 border border-secondary-300 dark:border-secondary-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-secondary-700 dark:text-secondary-100 transition-colors duration-200"
                       placeholder="Enter your email">
            </div>

            <!-- Password Input -->
            <div>
                <label for="password" class="block text-sm font-medium text-secondary-900 dark:text-secondary-100 mb-2">
                    Password
                </label>
                <input type="password" id="password" name="password" 
                       class="w-full px-3 py-2 border border-secondary-300 dark:border-secondary-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-secondary-700 dark:text-secondary-100 transition-colors duration-200"
                       placeholder="Enter your password">
            </div>

            <!-- Textarea -->
            <div>
                <label for="message" class="block text-sm font-medium text-secondary-900 dark:text-secondary-100 mb-2">
                    Message
                </label>
                <textarea id="message" name="message" rows="4" 
                          class="w-full px-3 py-2 border border-secondary-300 dark:border-secondary-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-secondary-700 dark:text-secondary-100 transition-colors duration-200"
                          placeholder="Write your message here..."></textarea>
            </div>

            <!-- Select -->
            <div>
                <label for="country" class="block text-sm font-medium text-secondary-900 dark:text-secondary-100 mb-2">
                    Country
                </label>
                <select id="country" name="country" 
                        class="w-full px-3 py-2 border border-secondary-300 dark:border-secondary-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-secondary-700 dark:text-secondary-100 transition-colors duration-200">
                    <option value="">Select a country</option>
                    <option value="us">United States</option>
                    <option value="uk">United Kingdom</option>
                    <option value="ca">Canada</option>
                    <option value="au">Australia</option>
                </select>
            </div>

            <!-- Checkboxes -->
            <div>
                <fieldset>
                    <legend class="block text-sm font-medium text-secondary-900 dark:text-secondary-100 mb-3">
                        Preferences
                    </legend>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input id="newsletter" name="preferences[]" value="newsletter" type="checkbox" 
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-secondary-300 dark:border-secondary-600 rounded">
                            <label for="newsletter" class="ml-3 text-sm text-secondary-900 dark:text-secondary-100">
                                Subscribe to newsletter
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input id="updates" name="preferences[]" value="updates" type="checkbox" 
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-secondary-300 dark:border-secondary-600 rounded">
                            <label for="updates" class="ml-3 text-sm text-secondary-900 dark:text-secondary-100">
                                Receive product updates
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input id="marketing" name="preferences[]" value="marketing" type="checkbox" 
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-secondary-300 dark:border-secondary-600 rounded">
                            <label for="marketing" class="ml-3 text-sm text-secondary-900 dark:text-secondary-100">
                                Marketing communications
                            </label>
                        </div>
                    </div>
                </fieldset>
            </div>

            <!-- Radio Buttons -->
            <div>
                <fieldset>
                    <legend class="block text-sm font-medium text-secondary-900 dark:text-secondary-100 mb-3">
                        Account Type
                    </legend>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input id="personal" name="account_type" value="personal" type="radio" 
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-secondary-300 dark:border-secondary-600">
                            <label for="personal" class="ml-3 text-sm text-secondary-900 dark:text-secondary-100">
                                Personal
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input id="business" name="account_type" value="business" type="radio" 
                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-secondary-300 dark:border-secondary-600">
                            <label for="business" class="ml-3 text-sm text-secondary-900 dark:text-secondary-100">
                                Business
                            </label>
                        </div>
                    </div>
                </fieldset>
            </div>

            <!-- Toggle Switch -->
            <div class="flex items-center justify-between">
                <div>
                    <label for="notifications" class="text-sm font-medium text-secondary-900 dark:text-secondary-100">
                        Push Notifications
                    </label>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400">
                        Get notified when someone mentions you
                    </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="notifications" class="sr-only peer">
                    <div class="w-11 h-6 bg-secondary-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-secondary-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-secondary-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-secondary-600 peer-checked:bg-primary-600"></div>
                </label>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-3 pt-4">
                <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    Submit
                </button>
                <button type="button" class="px-6 py-2 border border-secondary-300 dark:border-secondary-600 text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-700 text-sm font-medium rounded-lg transition-colors duration-200">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <!-- Form with Validation States -->
    <div class="bg-white dark:bg-secondary-800 rounded-lg shadow-soft border border-secondary-200 dark:border-secondary-700 p-6">
        <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100 mb-6">Validation States</h3>
        <div class="space-y-6">
            <!-- Success State -->
            <div>
                <label class="block text-sm font-medium text-secondary-900 dark:text-secondary-100 mb-2">
                    Valid Input
                </label>
                <input type="text" value="valid@example.com"
                       class="w-full px-3 py-2 border border-success-300 rounded-lg focus:ring-2 focus:ring-success-500 focus:border-success-500 dark:bg-secondary-700 dark:text-secondary-100 transition-colors duration-200">
                <p class="mt-1 text-sm text-success-600">This field is valid!</p>
            </div>

            <!-- Error State -->
            <div>
                <label class="block text-sm font-medium text-secondary-900 dark:text-secondary-100 mb-2">
                    Invalid Input
                </label>
                <input type="email" value="invalid-email"
                       class="w-full px-3 py-2 border border-error-300 rounded-lg focus:ring-2 focus:ring-error-500 focus:border-error-500 dark:bg-secondary-700 dark:text-secondary-100 transition-colors duration-200">
                <p class="mt-1 text-sm text-error-600">Please enter a valid email address.</p>
            </div>

            <!-- Warning State -->
            <div>
                <label class="block text-sm font-medium text-secondary-900 dark:text-secondary-100 mb-2">
                    Warning Input
                </label>
                <input type="password" value="123"
                       class="w-full px-3 py-2 border border-warning-300 rounded-lg focus:ring-2 focus:ring-warning-500 focus:border-warning-500 dark:bg-secondary-700 dark:text-secondary-100 transition-colors duration-200">
                <p class="mt-1 text-sm text-warning-600">Password should be at least 8 characters long.</p>
            </div>
        </div>
    </div>

    <!-- Input Groups -->
    <div class="bg-white dark:bg-secondary-800 rounded-lg shadow-soft border border-secondary-200 dark:border-secondary-700 p-6">
        <h3 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100 mb-6">Input Groups</h3>
        <div class="space-y-6">
            <!-- Input with Icon -->
            <div>
                <label class="block text-sm font-medium text-secondary-900 dark:text-secondary-100 mb-2">
                    Search
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" class="w-full pl-10 pr-3 py-2 border border-secondary-300 dark:border-secondary-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-secondary-700 dark:text-secondary-100" 
                           placeholder="Search...">
                </div>
            </div>

            <!-- Input with Button -->
            <div>
                <label class="block text-sm font-medium text-secondary-900 dark:text-secondary-100 mb-2">
                    Subscribe
                </label>
                <div class="flex">
                    <input type="email" class="flex-1 px-3 py-2 border border-secondary-300 dark:border-secondary-600 rounded-l-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-secondary-700 dark:text-secondary-100" 
                           placeholder="Enter email">
                    <button class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-r-lg border border-primary-600 hover:border-primary-700 transition-colors duration-200">
                        Subscribe
                    </button>
                </div>
            </div>

            <!-- Input with Addon -->
            <div>
                <label class="block text-sm font-medium text-secondary-900 dark:text-secondary-100 mb-2">
                    Website URL
                </label>
                <div class="flex">
                    <span class="inline-flex items-center px-3 py-2 border border-r-0 border-secondary-300 dark:border-secondary-600 bg-secondary-50 dark:bg-secondary-700 text-secondary-500 dark:text-secondary-400 text-sm rounded-l-lg">
                        https://
                    </span>
                    <input type="text" class="flex-1 px-3 py-2 border border-secondary-300 dark:border-secondary-600 rounded-r-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-secondary-700 dark:text-secondary-100" 
                           placeholder="www.example.com">
                </div>
            </div>
        </div>
    </div>
</div>
