<div class="relative fi-topbar-item" x-data="{ open: false }">
    <!-- Language Switcher Button -->
    <button 
        x-ref="button"
        @click="open = !open"
        @click.away="open = false"
        class="fi-topbar-item-btn flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 transition duration-75 hover:bg-gray-50 focus:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:text-gray-200 dark:hover:bg-white/5 dark:focus:bg-white/5"
        type="button"
    >
        @if($currentLanguage === 'hu')
            <span class="text-lg">🇭🇺</span>
        @else
            <span class="text-lg">🇬🇧</span>
        @endif
        <span class="truncate">{{ config('languages.available.' . $currentLanguage . '.native_name', 'Language') }}</span>
        <svg class="h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-anchor.bottom-end="$refs.button"
        class="fi-dropdown-panel absolute z-50 w-48 sm:w-56 divide-y divide-gray-100 rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:divide-white/5 dark:bg-gray-900 dark:ring-white/10"
        style="max-width: calc(100vw - 1rem);"
        @click="open = false"
    >
        <div class="fi-dropdown-list p-1">
            <!-- Header -->
            <div class="fi-dropdown-item flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                </svg>
                <span class="truncate">{{ __('messages.Switch Language') }}</span>
            </div>
            
            @foreach($availableLanguages as $langCode => $language)
                <button
                    wire:click="switchLanguage('{{ $langCode }}')"
                    class="fi-dropdown-item flex w-full items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 focus:bg-gray-50 focus:outline-none dark:text-gray-200 dark:hover:bg-white/5 dark:focus:bg-white/5 {{ $currentLanguage === $langCode ? 'bg-gray-50 dark:bg-white/5 font-medium' : '' }}"
                >
                    <span class="text-lg flex-shrink-0">{{ $language['flag'] }}</span>
                    <div class="flex flex-1 items-center justify-between min-w-0">
                        <div class="flex flex-col items-start min-w-0 flex-1">
                            <span class="font-medium truncate">{{ $language['native_name'] }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $language['name'] }}</span>
                        </div>
                        @if($currentLanguage === $langCode)
                            <svg class="h-4 w-4 text-primary-600 flex-shrink-0 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </div>
                </button>
            @endforeach
        </div>
    </div>
</div>