<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomPageResource\Pages;
use App\Filament\Resources\CustomPageResource\RelationManagers;
use App\Models\CustomPage;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use App\Filament\Forms\Components\AceEditor;

class CustomPageResource extends Resource
{
    protected static ?string $model = CustomPage::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';

    protected static string | \UnitEnum | null $navigationGroup = 'Content Management';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Section::make('Page Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Set $set) => 
                                $operation === 'create' ? $set('slug', Str::slug($state)) : null
                            ),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(CustomPage::class, 'slug', ignoreRecord: true),
                        Forms\Components\TextInput::make('public_url')
                            ->label('Public Link')
                            ->placeholder('Will be generated after save')
                            ->readOnly()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record !== null)
                            ->formatStateUsing(fn ($state, $record) => $record instanceof \App\Models\CustomPage ? route('custom-page.show', $record->slug) : null)
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('copy')
                                    ->icon('heroicon-m-clipboard-document')
                                    ->color('info')
                                    ->extraAttributes([
                                        'x-on:click' => "
                                            const link = \$el.parentElement.previousElementSibling.value;
                                            if (navigator.clipboard) {
                                                navigator.clipboard.writeText(link).then(() => {
                                                     new FilamentNotification().title('Link copied').success().send();
                                                });
                                            } else {
                                                const el = document.createElement('textarea');
                                                el.value = link;
                                                document.body.appendChild(el);
                                                el.select();
                                                document.execCommand('copy');
                                                document.body.removeChild(el);
                                                new FilamentNotification().title('Link copied (fallback)').success().send();
                                            }
                                        ",
                                    ])
                            ),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->inline(false)
                            ->required(),
                    ])->columns(2),

                Section::make('HTML Content')
                    ->schema([
                        AceEditor::make('content')
                            ->language('html')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => "/p/{$state}"),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\Action::make('copy_link')
                    ->label('Copy')
                    ->icon('heroicon-o-clipboard-document')
                    ->color('info')
                    ->extraAttributes(fn ($record) => [
                        'onclick' => "
                            const link = '" . ($record ? route('custom-page.show', $record->slug) : '') . "';
                            if (navigator.clipboard) {
                                navigator.clipboard.writeText(link).then(() => {
                                     new FilamentNotification().title('Link copied').success().send();
                                });
                            } else {
                                const el = document.createElement('textarea');
                                el.value = link;
                                document.body.appendChild(el);
                                el.select();
                                document.execCommand('copy');
                                document.body.removeChild(el);
                                new FilamentNotification().title('Link copied (fallback)').success().send();
                            }
                        ",
                    ]),
                \Filament\Actions\Action::make('view_page')
                    ->label('View')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => $record ? route('custom-page.show', $record->slug) : null)
                    ->openUrlInNewTab(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCustomPages::route('/'),
            'create' => Pages\CreateCustomPage::route('/create'),
            'edit' => Pages\EditCustomPage::route('/{record}/edit'),
        ];
    }
}
