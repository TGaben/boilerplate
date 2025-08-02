<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityResource\Pages;

use App\Filament\Resources\ActivityResource;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewActivity extends ViewRecord
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No edit action for read-only activity log
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make(__('messages.Activity Details'))
                    ->schema([
                        Infolists\Components\TextEntry::make('causer.name')
                            ->label(__('messages.User'))
                            ->placeholder(__('messages.System')),

                        Infolists\Components\TextEntry::make('description')
                            ->label(__('messages.Action')),

                        Infolists\Components\TextEntry::make('subject_type')
                            ->label(__('messages.Model'))
                            ->formatStateUsing(fn ($state) => class_basename($state)),

                        Infolists\Components\TextEntry::make('subject_id')
                            ->label(__('messages.Record ID')),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label(__('messages.Date'))
                            ->dateTime(),
                    ])->columns(2),

                Infolists\Components\Section::make(__('messages.Changed Data'))
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('properties.attributes')
                            ->label(__('messages.New Values'))
                            ->visible(fn ($record) => !empty($record->properties['attributes'] ?? [])),

                        Infolists\Components\KeyValueEntry::make('properties.old')
                            ->label(__('messages.Old Values'))
                            ->visible(fn ($record) => !empty($record->properties['old'] ?? [])),
                    ])->visible(fn ($record) => !empty($record->properties['attributes'] ?? []) || !empty($record->properties['old'] ?? [])),
            ]);
    }
}
