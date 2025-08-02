@php
    $currentLanguage = app()->getLocale(); // Use current app locale instead of session
    $availableLanguages = config('languages.available', []);
@endphp

<div class="relative fi-topbar-item" x-data="{ open: false }">
    <!-- Language Switcher Button -->
    <button 
        x-ref="button"
        @click="open = !open"
        @click.away="open = false"
        type="button"
        class="fi-topbar-item-button flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition duration-75 hover:bg-gray-50 hover:text-gray-500 focus:bg-gray-50 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-400 dark:focus:bg-white/5 dark:focus:text-gray-400"
        title="{{ __('messages.Switch Language') }}"
    >
        <span class="text-lg">{{ $availableLanguages[$currentLanguage]['flag'] ?? '🌐' }}</span>
    </button>

    <!-- Language Dropdown -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        class="fi-dropdown-panel absolute z-50 mt-2 w-48 origin-top-right divide-y divide-gray-100 rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 transition dark:divide-white/5 dark:bg-gray-900 dark:ring-white/10"
        style="display: none; right: 0; max-width: calc(100vw - 1rem);"
        @style([
            'right: 0' => true,
            'left: auto' => true,
        ])
    >
        <div class="fi-dropdown-content py-1">
            <div class="fi-dropdown-header flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-950 dark:text-white">
                <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                </svg>
                <span class="truncate">{{ __('messages.Switch Language') }}</span>
            </div>
            
            @foreach($availableLanguages as $langCode => $language)
                <form method="POST" action="{{ route('language.switch', $langCode) }}" class="w-full">
                    @csrf
                    <button
                        type="submit"
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
                </form>
            @endforeach
        </div>
    </div>
</div>