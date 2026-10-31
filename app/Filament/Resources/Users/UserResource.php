<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'user';

    protected static ?string $pluralModelLabel = 'Users';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Full Name')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label('Email Address')
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(table: 'users', column: 'email', ignoreRecord: true),

            TextInput::make('password')
                ->label('Password')
                ->password()
                ->minLength(8)
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $operation): bool => $operation === 'create')
                ->helperText(fn (string $operation) => $operation === 'edit' ? 'Leave blank to keep the current password.' : null),

            Select::make('role')
                ->label('Role')
                ->options(fn () => Role::orderBy('name')->pluck('name', 'name'))
                ->required()
                ->afterStateHydrated(function (Select $component, ?User $record) {
                    $component->state($record?->getRoleNames()->first());
                }),

            Toggle::make('is_active')
                ->label('Active')
                ->default(true)
                ->onColor('success')
                ->offColor('danger'),

            Section::make('Panel Access')
                ->description('Which areas this user can log into. Role above still controls what they can do inside the admin panel.')
                ->columns(1)
                ->schema([
                    Toggle::make('can_access_admin')
                        ->label('Admin Panel')
                        ->helperText('Orders, products, settings, reports.'),

                    Toggle::make('can_access_pos')
                        ->label('POS Terminal')
                        ->helperText('In-store sales at /pos.')
                        ->live(),

                    Toggle::make('pos_shares_admin_login')
                        ->label('Use their admin login for POS')
                        ->helperText('Skips a separate POS login - logging into the admin panel signs them into POS too. Leave off for a shared shop-floor terminal where whoever is logged into POS shouldn\'t also be signed into admin.')
                        ->visible(fn (Get $get) => $get('can_access_pos'))
                        ->inline(false),

                    Toggle::make('can_access_service')
                        ->label('Service Panel')
                        ->helperText('Service bookings at /service.'),
                ]),
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

                TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable()
                    ->copyable(),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->separator(',')
                    ->color('primary'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                IconColumn::make('can_access_admin')
                    ->label('Admin')
                    ->boolean(),

                IconColumn::make('can_access_pos')
                    ->label('POS')
                    ->boolean(),

                IconColumn::make('can_access_service')
                    ->label('Service')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->placeholder('All users'),

                SelectFilter::make('role')
                    ->label('Role')
                    ->relationship('roles', 'name'),
            ])
            ->actions([
                EditAction::make(),

                Action::make('toggle_active')
                    ->label(fn (User $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (User $record) => $record->is_active ? Heroicon::OutlinedXCircle : Heroicon::OutlinedCheckCircle)
                    ->color(fn (User $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record) => $record->is_active ? 'Deactivate '.$record->name.'?' : 'Activate '.$record->name.'?')
                    ->modalDescription(fn (User $record) => $record->is_active
                        ? 'This user will no longer be able to log in.'
                        : 'This user will be able to log in again.')
                    ->hidden(fn (User $record) => $record->id === auth()->id())
                    ->action(fn (User $record) => $record->update(['is_active' => ! $record->is_active])),
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
            'index'  => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit'   => EditUser::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('Admin') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('Admin') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasRole('Admin') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasRole('Admin') && $record->id !== auth()->id();
    }
}
