<?php

namespace App\Filament\Resources\Salespeople;

use App\Filament\Resources\Salespeople\Pages\CreateSalesperson;
use App\Filament\Resources\Salespeople\Pages\EditSalesperson;
use App\Filament\Resources\Salespeople\Pages\ListSalespeople;
use App\Models\Salesperson;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SalespersonResource extends Resource
{
    protected static ?string $model = Salesperson::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static ?string $navigationLabel = 'Salespeople';

    protected static ?string $modelLabel = 'salesperson';

    protected static ?string $pluralModelLabel = 'Salespeople';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(100)
                ->unique(table: 'salespeople', column: 'name', ignoreRecord: true),
            Toggle::make('is_active')
                ->label('Active')
                ->default(true)
                ->onColor('success')
                ->offColor('danger'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->placeholder('All salespeople'),
            ])
            ->actions([
                EditAction::make(),
                Action::make('toggle')
                    ->label(fn (Salesperson $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (Salesperson $record) => $record->is_active ? Heroicon::OutlinedXCircle : Heroicon::OutlinedCheckCircle)
                    ->color(fn (Salesperson $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Salesperson $record) => $record->is_active ? 'Deactivate '.$record->name : 'Activate '.$record->name)
                    ->modalDescription(fn (Salesperson $record) => $record->is_active
                        ? 'This salesperson will no longer appear in the active salespeople list.'
                        : 'This salesperson will be re-added to the active salespeople list.')
                    ->action(fn (Salesperson $record) => $record->update(['is_active' => ! $record->is_active])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSalespeople::route('/'),
            'create' => CreateSalesperson::route('/create'),
            'edit'   => EditSalesperson::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->hasRole('Admin') ?? false;
    }
}
