@props(['class' => ''])

@php
    $currentLanguage = session(config('languages.session_key', 'locale'), config('languages.default', 'hu'));
    $availableLanguages = config('languages.available', []);
@endphp

<div class="relative {{ $class }}" x-data="{ open: false }">
    <!-- Language Switcher Button -->
    <button 
        @click="open = !open"
        @click.away="open = false"
        type="button"
        class="inline-flex items-center gap-x-1.5 rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
        aria-expanded="false"
    >
        <span class="text-lg">{{ $availableLanguages[$currentLanguage]['flag'] ?? '🌐' }}</span>
        <span class="text-sm font-medium">{{ $availableLanguages[$currentLanguage]['native_name'] ?? $currentLanguage }}</span>
        <svg class="-mr-1 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
    </button>

    <!-- Language Dropdown -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-md bg-white dark:bg-gray-800 py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
        style="display: none;"
    >
        @foreach($availableLanguages as $langCode => $language)
            <form method="POST" action="{{ route('language.switch', $langCode) }}" class="w-full">
                @csrf
                <button
                    type="submit"
                    class="flex w-full items-center gap-x-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 {{ $currentLanguage === $langCode ? 'bg-gray-50 dark:bg-gray-700 font-semibold' : '' }}"
                >
                    <span class="text-lg">{{ $language['flag'] }}</span>
                    <div class="flex flex-col items-start">
                        <span class="font-medium">{{ $language['native_name'] }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $language['name'] }}</span>
                    </div>
                    @if($currentLanguage === $langCode)
                        <svg class="ml-auto h-4 w-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </button>
            </form>
        @endforeach
    </div>
</div>