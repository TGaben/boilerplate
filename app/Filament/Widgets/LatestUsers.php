<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestUsers extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => User::query()->latest()->take(10))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('messages.Name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('messages.Email'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label(__('messages.Roles'))
                    ->badge()
                    ->separator(',')
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'user' => 'success',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label(__('messages.Last Login'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder(__('messages.Never')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('messages.Registered'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->heading(__('messages.Latest Registered Users'))
            ->description(__('messages.Recently registered users in the system'))
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
