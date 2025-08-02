<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SystemStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make(__('messages.Total Users'), User::count())
                ->description(__('messages.Registered users in the system'))
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make(__('messages.Admin Users'), User::role('admin')->count())
                ->description(__('messages.Users with admin privileges'))
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('warning'),

            Stat::make(__('messages.Total Roles'), Role::count())
                ->description(__('messages.Available user roles'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make(__('messages.Total Permissions'), Permission::count())
                ->description(__('messages.System permissions'))
                ->descriptionIcon('heroicon-m-key')
                ->color('gray'),
        ];
    }
}
