<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    public static function canAccess(): bool
    {
        // Allow access for all authenticated users for now
        return \Illuminate\Support\Facades\Auth::check();
    }
}
