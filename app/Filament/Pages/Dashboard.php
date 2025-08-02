<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BasePage;

class Dashboard extends BasePage
{
    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\SystemStats::class,
            \App\Filament\Widgets\LatestUsers::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }
}
