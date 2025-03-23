<?php

namespace App\Filament\Resources\Sipancong;

use App\Filament\Resources\Sipancong\PengajuanResource\Pages;
use App\Filament\Resources\Sipancong\PengajuanResource\RelationManagers;
use App\Models\Sipancong\JenisDokumen;
use App\Models\Sipancong\Pengajuan;
use App\Models\Sipancong\PosisiDokumen;
use App\Models\Sipancong\StatusPembayaran;
use App\Models\Sipancong\StatusPengajuan;
use App\Services\Sipancong\PengajuanServices;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;

class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $label = "Pengajuan";
    protected static ?string $navigationLabel = "Pengajuan";
    protected static ?string $pluralModelLabel = "Pengajuan";
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = "Pembayaran";
    protected static ?int $navigationSort = 1;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nomor_pengajuan')
                    ->required()
                    ->maxLength(50),
                DatePicker::make('tanggal_pengajuan')
                    ->required(),
                TextInput::make('nomor_form_pembayaran')
                    ->required()
                    ->maxLength(50)
                    ->default(null),
                TextInput::make('nomor_detail_fa')
                    ->required()
                    ->maxLength(50)
                    ->default(null),
                Textarea::make('uraian_pengajuan')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('nominal_pengajuan')
                    ->required()
                    ->numeric(),
                TextInput::make('link_folder_dokumen')
                    ->required()
                    ->maxLength(255)
                    ->default(null),
                Select::make('posisi_dokumen_id')
                    ->label("Posisi Dokumen")
                    ->options(PosisiDokumen::pluck('nama', 'id'))
                    ->required()
                    ->default(null),
                Select::make('status_pengajuan_ppk_id')
                    ->label("Status Pengajuan di PPK")
                    ->options(StatusPengajuan::pluck('nama', 'id'))
                    ->default(null),
                Select::make('status_pengajuan_ppspm_id')
                    ->label("Status Pengajuan di PPSPM")
                    ->options(StatusPengajuan::pluck('nama', 'id'))
                    ->default(null),
                Select::make('status_pengajuan_bendahara_id')
                    ->label("Status Pengajuan di Bendahara")
                    ->options(StatusPengajuan::pluck('nama', 'id'))
                    ->default(null),
                Textarea::make('catatan_ppk')
                    ->columnSpanFull(),
                Textarea::make('catatan_bendahara')
                    ->columnSpanFull(),
                Textarea::make('catatan_ppspm')
                    ->columnSpanFull(),
                Textarea::make('tanggapan_pengaju_ke_ppk')
                    ->label("Tanggapan Pengaju ke PPK")
                    ->columnSpanFull(),
                Textarea::make('tanggapan_pengaju_ke_ppspm')
                    ->label("Tanggapan Pengaju ke PPSPM")
                    ->columnSpanFull(),
                Textarea::make('tanggapan_pengaju_ke_bendahara')
                    ->label("Tanggapan Pengaju ke Bendahara")
                    ->columnSpanFull(),
                TextInput::make('nominal_dibayarkan')
                    ->label("Nominal Dibayarkan")
                    ->numeric()
                    ->default(null),
                TextInput::make('nominal_dikembalikan')
                    ->label("Nominal Dikembalikan")
                    ->numeric()
                    ->default(null),
                Select::make('status_pembayaran_id')
                    ->label("Status Pembayaran")
                    ->options(StatusPembayaran::pluck("nama", "id"))
                    ->default(null),
                DatePicker::make('tanggal_pembayaran'),
                Select::make('jenis_dokumen_id')
                    ->options(JenisDokumen::pluck("nama", "id"))
                    ->label("Jenis Dokumen")
                    ->default(null),
                TextInput::make('nomor_dokumen')
                    ->maxLength(50)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_pengajuan')
                    ->label("No")
                    ->searchable(),
                TextColumn::make("uraian_pengajuan")
                    ->searchable()
                    ->label("Uraian Pengajuan"),
                TextColumn::make("pegawai.nama")
                    ->label("Pengaju"),
                TextColumn::make('tanggal_pengajuan')
                    ->label("Tanggal Pengajuan")
                    ->date()
                    ->sortable(),
                TextColumn::make('nomor_form_pembayaran')
                    ->label("No. Form Pembayaran")
                    ->searchable(),
                TextColumn::make('nomor_detail_fa')
                    ->label("No. Detail FA")
                    ->searchable(),
                TextColumn::make('nominal_pengajuan')
                    ->label("Nominal Pengajuan")
                    ->numeric()
                    ->sortable(),
                TextColumn::make('link_folder_dokumen')
                    ->searchable(),
                TextColumn::make('posisi_dokumen_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status_pengajuan_ppk_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status_pengajuan_ppspm_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nominal_dibayarkan')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nominal_dikembalikan')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status_pembayaran_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tanggal_pembayaran')
                    ->date()
                    ->sortable(),
                TextColumn::make('jenis_dokumen_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nomor_dokumen')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Action::make("Link")
                        ->icon("heroicon-m-link")
                        ->url(fn(Pengajuan $record): string => $record->link_folder_dokumen)
                        ->openUrlInNewTab()
                        ->hidden(function (Pengajuan $record) {
                            return !($record->link_folder_dokumen);
                        }),
                    Action::make("Aksi Pengaju")
                        ->label("Tanggapan Pengaju")
                        ->icon("heroicon-o-pencil")
                        ->form([
                            Textarea::make('uraian_pengajuan')
                                ->readOnly(),
                            Select::make('status_pengajuan_ppk_id')
                                ->label("Status Pengajuan di PPK")
                                ->options(StatusPengajuan::pluck('nama', 'id'))
                                ->disabled(),
                            Select::make('status_pengajuan_ppspm_id')
                                ->label("Status Pengajuan di PPSPM")
                                ->options(StatusPengajuan::pluck('nama', 'id'))
                                ->disabled(),
                            Select::make('status_pengajuan_bendahara_id')
                                ->label("Status Pengajuan di Bendahara")
                                ->options(StatusPengajuan::pluck('nama', 'id'))
                                ->disabled(),
                            Textarea::make('tanggapan_pengaju_ke_ppk')
                                ->label("Tanggapan Pengaju ke PPK")
                                ->columnSpanFull(),
                            Textarea::make('tanggapan_pengaju_ke_ppspm')
                                ->label("Tanggapan Pengaju ke PPSPM")
                                ->columnSpanFull(),
                            Textarea::make('tanggapan_pengaju_ke_bendahara')
                                ->label("Tanggapan Pengaju ke Bendahara")
                                ->columnSpanFull(),
                        ])
                        ->fillForm(function (Pengajuan $record): array {
                            return $record->toArray();
                        })
                        ->action(function (array $data, Pengajuan $record) {
                            PengajuanServices::tanggapi($data, $record);
                        })
                ])->link()->label("Aksi"),

            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([]);
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
            'index' => Pages\ListPengajuans::route('/'),
            'create' => Pages\CreatePengajuan::route('/create'),
            'edit' => Pages\EditPengajuan::route('/{record}/edit'),
        ];
    }
}
