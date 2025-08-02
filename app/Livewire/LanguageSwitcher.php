<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class LanguageSwitcher extends Component
{
    public string $currentLanguage;

    public function mount(): void
    {
        $this->currentLanguage = App::getLocale();
    }

    public function switchLanguage(string $language): void
    {
        // Get available languages from config
        $availableLanguages = array_keys(config('languages.available', []));

        // Validate the language parameter
        if (! in_array($language, $availableLanguages, true)) {
            $this->addError('language', 'Language not supported.');

            return;
        }

        // Store the language in session
        $sessionKey = config('languages.session_key', 'locale');
        Session::put($sessionKey, $language);
        Session::save();

        // Set the application locale for the current request
        App::setLocale($language);
        $this->currentLanguage = $language;

        // Force page refresh to apply language change completely
        $this->js('window.location.reload()');
    }

    public function render()
    {
        return view('livewire.language-switcher', [
            'availableLanguages' => config('languages.available', []),
        ]);
    }
}
