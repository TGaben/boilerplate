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

    protected static ?array $globalSearchColumns = ['description', 'subject_type'];

    protected static bool $isGlobalSearchEnabled = false;

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
            ->query(function () {
                /** @var \Illuminate\Database\Eloquent\Builder $query */
                $query = \Spatie\Activitylog\Models\Activity::query();

                return $query->select(['id', 'description', 'subject_type', 'subject_id', 'causer_type', 'causer_id', 'created_at']);
            })
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('messages.Action'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label(__('messages.Model'))
                    ->sortable()
                    ->formatStateUsing(fn ($state) => class_basename($state ?? '')),

                Tables\Columns\TextColumn::make('subject_id')
                    ->label(__('messages.Record ID'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('causer_id')
                    ->label(__('messages.User ID'))
                    ->sortable()
                    ->placeholder(__('messages.System')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('messages.Date'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // Simplified filters without relationships
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No bulk actions
            ])
            ->defaultSort('id', 'desc')
            ->heading(__('messages.Activity Log'))
            ->description(__('messages.Activity Log Description'))
            ->searchable(false);
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

    public static function getGloballySearchableAttributes(): array
    {
        return []; // No searchable attributes for global search
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->description ?? '';
    }
}
