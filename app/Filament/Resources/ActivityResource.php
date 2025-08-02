<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $recordTitleAttribute = 'description';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Adminisztráció';

    protected static ?int $navigationSort = 40;

    public static function getModelLabel(): string
    {
        return __('messages.Activity');
    }

    public static function getPluralModelLabel(): string
    {
        return __('messages.Activity Log');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.Activity Log');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('messages.Administration');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('causer.name')
                    ->label(__('messages.User'))
                    ->sortable()
                    ->searchable()
                    ->placeholder(__('messages.System')),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('messages.Action'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label(__('messages.Model'))
                    ->sortable()
                    ->formatStateUsing(fn ($state) => class_basename($state)),

                Tables\Columns\TextColumn::make('subject_id')
                    ->label(__('messages.Record ID'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('messages.Date'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('causer_id')
                    ->label(__('messages.User'))
                    ->relationship('causer', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('subject_type')
                    ->label(__('messages.Model'))
                    ->options([
                        'App\Models\User' => __('messages.User'),
                        'App\Models\Role' => __('messages.Role'),
                        'App\Models\Permission' => __('messages.Permission'),
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Remove bulk actions for read-only resource
            ])
            ->defaultSort('created_at', 'desc')
            ->heading(__('messages.Activity Log'))
            ->description(__('messages.Activity Log Description'));
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
            'view' => Pages\ViewActivity::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
