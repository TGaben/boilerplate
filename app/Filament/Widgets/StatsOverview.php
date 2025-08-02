<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class StatsOverview extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
