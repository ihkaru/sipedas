<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\ApiAuditLog;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = ApiAuditLog::class;

    protected static ?string $label = "Audit Log API";
    protected static ?string $navigationLabel = "Audit Log API";
    protected static ?string $pluralModelLabel = "Audit Log API";
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string | \UnitEnum | null $navigationGroup = "Integrasi & API";
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'operator_umum']) ?? false;
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

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Informasi Request API')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('action')
                        ->label('Aksi')
                        ->disabled(),
                    Forms\Components\TextInput::make('method')
                        ->label('HTTP Method')
                        ->disabled(),
                    Forms\Components\TextInput::make('path')
                        ->label('Endpoint URL')
                        ->disabled(),
                    Forms\Components\TextInput::make('ip_address')
                        ->label('IP Address')
                        ->disabled(),
                    Forms\Components\TextInput::make('status_code')
                        ->label('HTTP Status Code')
                        ->disabled(),
                    Forms\Components\TextInput::make('created_at')
                        ->label('Waktu Eksekusi')
                        ->disabled(),
                ]),

            Section::make('Kredensial & Pemanggil')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('user.name')
                        ->label('User Terkait')
                        ->disabled(),
                    Forms\Components\TextInput::make('apiKey.name')
                        ->label('API Key yang Digunakan')
                        ->disabled(),
                ]),

            Section::make('Snapshot Payload & State')
                ->schema([
                    Forms\Components\Textarea::make('payload_display')
                        ->label('Request Payload (JSON)')
                        ->rows(5)
                        ->disabled()
                        ->afterStateHydrated(fn ($component, $record) => $component->state(
                            $record?->payload ? json_encode($record->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '-'
                        )),
                    Forms\Components\Textarea::make('state_before_display')
                        ->label('State Before (JSON)')
                        ->rows(6)
                        ->disabled()
                        ->afterStateHydrated(fn ($component, $record) => $component->state(
                            $record?->state_before ? json_encode($record->state_before, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '-'
                        )),
                    Forms\Components\Textarea::make('state_after_display')
                        ->label('State After (JSON)')
                        ->rows(6)
                        ->disabled()
                        ->afterStateHydrated(fn ($component, $record) => $component->state(
                            $record?->state_after ? json_encode($record->state_after, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '-'
                        )),
                ]),

            Section::make('Status Rollback (Pembalik Keadaan)')
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('is_reversible')
                        ->label('Secara Logis Dapat Di-rollback')
                        ->disabled(),
                    Forms\Components\Toggle::make('is_rolled_back')
                        ->label('Status Telah Di-rollback')
                        ->disabled(),
                    Forms\Components\TextInput::make('rolled_back_at')
                        ->label('Waktu Rollback')
                        ->disabled(),
                    Forms\Components\TextInput::make('rollback_reason')
                        ->label('Alasan Rollback')
                        ->disabled(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/y H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ALLOCATE_HONOR' => 'info',
                        'BATCH_ALLOCATE' => 'primary',
                        'DELETE_ALLOCATION', 'DELETE_HONOR' => 'danger',
                        'ROLLBACK' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('method')
                    ->label('Method')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'POST' => 'success',
                        'DELETE' => 'danger',
                        'GET' => 'gray',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('path')
                    ->label('Endpoint')
                    ->searchable()
                    ->limit(25),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User / Key')
                    ->formatStateUsing(function (ApiAuditLog $record): string {
                        $user = $record->user?->name ?? 'System';
                        $key = $record->apiKey?->name ? " [{$record->apiKey->name}]" : '';
                        return $user . $key;
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('status_code')
                    ->label('Status')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 200 && $state < 300 => 'success',
                        $state >= 400 && $state < 500 => 'warning',
                        $state >= 500 => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_reversible')
                    ->label('Reversible')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\IconColumn::make('is_rolled_back')
                    ->label('Rolled Back')
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-uturn-left')
                    ->falseIcon('heroicon-o-minus'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'ALLOCATE_HONOR' => 'ALLOCATE_HONOR',
                        'BATCH_ALLOCATE' => 'BATCH_ALLOCATE',
                        'DELETE_ALLOCATION' => 'DELETE_ALLOCATION',
                        'ROLLBACK' => 'ROLLBACK',
                    ]),
                Tables\Filters\TernaryFilter::make('is_reversible')
                    ->label('Dapat Di-Rollback'),
                Tables\Filters\TernaryFilter::make('is_rolled_back')
                    ->label('Sudah Di-Rollback'),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),

                \Filament\Actions\Action::make('rollback')
                    ->label('Rollback')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Rollback Perubahan API')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan perubahan dari operasi API ini? Data alokasi honor dan nomor surat terkait akan dikembalikan ke kondisi sebelumnya.')
                    ->modalSubmitActionLabel('Ya, Jalankan Rollback')
                    ->visible(fn (ApiAuditLog $record): bool => $record->is_reversible && !$record->is_rolled_back)
                    ->form([
                        Forms\Components\TextInput::make('reason')
                            ->label('Alasan Rollback')
                            ->required()
                            ->default('Rollback manual via Filament Dashboard'),
                    ])
                    ->action(function (ApiAuditLog $record, array $data): void {
                        try {
                            $result = $record->executeRollback(auth()->user(), $data['reason'] ?? null);
                            Notification::make()
                                ->title('Rollback Berhasil')
                                ->body($result['message'])
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Rollback Gagal')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }
}
