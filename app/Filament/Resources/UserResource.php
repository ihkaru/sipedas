<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use PDO;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $label = "Pengguna";
    protected static ?string $navigationLabel = "Pengguna";
    protected static ?string $pluralModelLabel = "Pengguna";
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = "Pengguna";

    public static function canViewAny(): bool{
        return auth()->user()->hasRole('super_admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('jadikan_kepala')
                    ->label("Tambah Role Kepala")
                    ->hidden(function(User $record){
                        return !auth()->user()->hasRole('super_admin') || $record->hasRole('kepala_satker');
                    })
                    ->action(function(User $record){
                        if($record->assignRole('kepala_satker')){
                            return Notification::make()
                            ->title('Sukses menambahkan role kepala satker ke '.$record->pegawai->nama)
                            ->success();
                        };
                        return Notification::make()
                        ->title('Gagal menambahkan role kepala satker ke '.$record->pegawai->nama)
                        ->danger();
                    }),
                Action::make('lepas_kepala')
                    ->label('Lepas Role Kepala')
                    ->hidden(function(User $record){
                        return !auth()->user()->hasRole('super_admin') || !$record->hasRole('kepala_satker');
                    })
                    ->action(function(User $record){
                        if($record->assignRole('kepala_satker')){
                            return Notification::make()
                            ->title('Sukses melepaskan role kepala satker dari '.$record->pegawai->nama)
                            ->success();
                        };
                        return Notification::make()
                        ->title('Gagal melepaskan role kepala satker dari '.$record->pegawai->nama)
                        ->success();
                    }),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
