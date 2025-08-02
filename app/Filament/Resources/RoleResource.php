<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Roles';

    protected static ?string $modelLabel = 'Role';

    protected static ?string $pluralModelLabel = 'Roles';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Role Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->unique(Role::class, 'name', ignoreRecord: true)
                            ->label('Role Name')
                            ->helperText('The name of the role (e.g., admin, user, moderator).'),

                        Forms\Components\TextInput::make('guard_name')
                            ->required()
                            ->maxLength(255)
                            ->default('web')
                            ->label('Guard Name')
                            ->helperText('The guard name this role belongs to.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Permissions')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->label('Assign Permissions')
                            ->helperText('Select the permissions this role should have.')
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(2)
                            ->gridDirection('row'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Role Name')
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'user' => 'success',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('guard_name')
                    ->label('Guard')
                    ->sortable()
                    ->badge()
                    ->color('secondary'),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->counts('permissions')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Users')
                    ->counts('users')
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('guard_name')
                    ->label('Filter by Guard')
                    ->options([
                        'web' => 'Web',
                        'api' => 'API',
                    ]),

                Tables\Filters\Filter::make('has_permissions')
                    ->label('Has Permissions')
                    ->query(fn (Builder $query): Builder => $query->has('permissions')),

                Tables\Filters\Filter::make('has_users')
                    ->label('Has Users')
                    ->query(fn (Builder $query): Builder => $query->has('users')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, Role $record) {
                        // Prevent deletion if role has users assigned
                        if ($record->users()->count() > 0) {
                            $action->cancel();
                            // You can also show a notification here
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Tables\Actions\DeleteBulkAction $action, $records) {
                            // Remove roles that have users assigned from bulk delete
                            $filteredRecords = $records->filter(fn ($record) => $record->users()->count() === 0);

                            if ($records->count() !== $filteredRecords->count()) {
                                // Show notification that some roles were excluded
                            }
                        }),
                ]),
            ])
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('viewAny', Role::class) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create', Role::class) ?? false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('update', $record) ?? false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('delete', $record) ?? false;
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('view', $record) ?? false;
    }
}
