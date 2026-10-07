<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApiKeyResource\Pages;
use App\Models\ApiKey;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApiKeyResource extends Resource
{
    protected static ?string $model = ApiKey::class;

    protected static ?string $label = "API Key";
    protected static ?string $navigationLabel = "API Keys";
    protected static ?string $pluralModelLabel = "API Keys";
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-key';
    protected static string | \UnitEnum | null $navigationGroup = "Integrasi & API";
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'operator_umum']) ?? false;
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $record->user_id === $user->id;
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $record->user_id === $user->id;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && !$user->hasRole(['super_admin', 'operator_umum'])) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Informasi Kredensial API')
                ->description('Kelola kunci akses API untuk coding agent (Cursor, Claude, Windsurf, dll) atau sistem otomatisasi.')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Pemilik Kunci (User)')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->default(fn () => auth()->id())
                        ->disabled(fn () => !auth()->user()?->hasRole('super_admin')),

                    Forms\Components\TextInput::make('name')
                        ->label('Nama Kunci / Agen')
                        ->placeholder('e.g. Antigravity Agent, Claude Auto Worker')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('key')
                        ->label('Token API Key')
                        ->required()
                        ->default(fn () => ApiKey::generateSecureKey())
                        ->readOnly()
                        ->copyable()
                        ->helperText('Gunakan token ini pada header HTTP: X-API-KEY: <token> atau Authorization: Bearer <token>.'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Status Aktif')
                        ->default(true)
                        ->helperText('Jika dinonaktifkan, request API dengan key ini akan ditolak (401 Unauthorized).'),

                    Forms\Components\DateTimePicker::make('expires_at')
                        ->label('Batas Kedaluwarsa (Opsional)')
                        ->helperText('Kosongkan jika ingin key berlaku tanpa batas waktu.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kunci / Agen')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemilik User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('key')
                    ->label('Token')
                    ->formatStateUsing(fn (string $state) => substr($state, 0, 8) . '...' . substr($state, -4))
                    ->copyable()
                    ->tooltip('Klik untuk menyalin token'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_used_at')
                    ->label('Terakhir Digunakan')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('Belum pernah'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Kedaluwarsa')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('Permanen'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApiKeys::route('/'),
            'create' => Pages\CreateApiKey::route('/create'),
            'edit' => Pages\EditApiKey::route('/{record}/edit'),
        ];
    }
}
