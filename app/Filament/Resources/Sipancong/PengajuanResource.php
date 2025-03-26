<?php

namespace App\Filament\Resources\Sipancong;

use App\Filament\Resources\Sipancong\PengajuanResource\Forms\PengajuanForms;
use App\Filament\Resources\Sipancong\PengajuanResource\Pages;
use App\Filament\Resources\Sipancong\PengajuanResource\RelationManagers;
use App\Models\Sipancong\JenisDokumen;
use App\Models\Sipancong\Pengajuan;
use App\Models\Sipancong\PosisiDokumen;
use App\Models\Sipancong\StatusPembayaran;
use App\Models\Sipancong\StatusPengajuan;
use App\Models\Sipancong\Subfungsi;
use App\Services\Sipancong\PengajuanServices;
use Dom\Text;
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
use Filament\Resources\Components\Tab;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

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
                Select::make("sub_fungsi_id")
                    ->label("Sub Fungsi")
                    ->options(Subfungsi::pluck("nama", "id"))
                    ->required(),
                TextInput::make('nomor_form_pembayaran')
                    ->label("Nomor Form Pembayaran")
                    ->helperText("Bisa lebih dari satu nomor. Gunakan koma sebagai pemisah nomor. Contoh: 12,13,140")
                    ->required()
                    ->maxLength(50)
                    ->default(null),
                TextInput::make('nomor_detail_fa')
                    ->label("Nomor Detail FA")
                    ->required()
                    ->helperText("Bisa lebih dari satu nomor. Gunakan koma sebagai pemisah nomor. Contoh: 12,13,140")
                    ->maxLength(50)
                    ->default(null),
                Textarea::make('uraian_pengajuan')
                    ->label("Uraian Pengajuan")
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('nominal_pengajuan')
                    ->required()
                    ->numeric(),
                TextInput::make('link_folder_dokumen')
                    ->helperText(new HtmlString("Pastikan akses sudah folder sudah terbuka untuk edit! Pastikan dokumen sudah ditandatangani <strong>selain</strong> PPK, Bendahara, Kepala Satker"))
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

            ->deferLoading()
            ->defaultSort("updated_at", "desc")
            ->columns([
                TextColumn::make('subfungsi.nama')
                    ->searchable()
                    ->label("Subfungsi")
                    ->searchable(),
                TextColumn::make('nomor_pengajuan')
                    ->sortable(
                        query: fn($query, $direction) => $query->orderBy(
                            DB::raw('CAST(nomor_pengajuan AS UNSIGNED)'),
                            $direction
                        )
                    )
                    ->numeric()
                    ->label("No")
                    ->searchable(),
                TextColumn::make("uraian_pengajuan")
                    ->searchable()
                    ->label("Uraian Pengajuan"),
                TextColumn::make('penanggungJawab.nama')
                    ->label("Penanggung Jawab")
                    ->searchable()
                    ->sortable(),
                TextColumn::make('statusPengajuanPpk.nama')
                    ->label("Status di PPK")
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('statusPengajuanBendahara.nama')
                    ->label("Status di Bendahara")
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('statusPengajuanPpspm.nama')
                    ->label("Status di PPSPM")
                    ->searchable()
                    ->badge()
                    ->sortable(),
                TextColumn::make('error')
                    ->label('Error Pengajuan')
                    ->badge()
                    ->color("danger")
                    ->getStateUsing(function ($record) {
                        $text = "";
                        $text = ($record->link_folder_dokumen) ? $text . "" : $text . " Link folder dokumen tidak boleh kosong.";
                        $text = ($record->nip_penanggung_jawab) ? $text . "" : $text . " Penanggung jawab tidak boleh kosong.";
                        return trim($text) ? trim($text) : "-";
                    }),
                TextColumn::make("pegawai.nama")
                    ->searchable()
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
                    ->label("Link Folder")
                    ->searchable(),
                TextColumn::make('posisiDokumen.nama')
                    ->label("Posisi Dokumen")
                    ->numeric()
                    ->sortable(),

                TextColumn::make('nominal_dibayarkan')
                    ->label("Nominal dibayarkan")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nominal_dikembalikan')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label("Nominal Dikembalikan")
                    ->numeric()
                    ->sortable(),
                TextColumn::make('statusPembayaran.nama')
                    ->badge()
                    ->label("Status Pembayaran")
                    ->sortable(),
                TextColumn::make('tanggal_pembayaran')
                    ->date()
                    ->sortable(),
                TextColumn::make('jenisDokumen.nama')
                    ->label("Jenis Dokumen")
                    ->sortable(),
                TextColumn::make('nomor_dokumen')
                    ->label("No SPM/SPBY")
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
                SelectFilter::make('nip_penanggung_jawab')
                    ->label("Penanggung Jawab")
                    ->relationship('penanggungJawab', 'nama')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('nip_pengaju')
                    ->label("Pengaju")
                    ->relationship('pegawai', 'nama')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                SelectFilter::make('sub_fungsi_id')
                    ->label("Sub Fungsi")
                    ->relationship('subfungsi', 'nama')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('status_pembayaran_id')
                    ->label("Status Pembayaran")
                    ->relationship('statusPembayaran', 'nama')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('status_pengajuan_ppk_id')
                    ->label("Status Pengajuan ke PPK")
                    ->relationship('statusPengajuanPpk', 'nama')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('status_pengajuan_bendahara_id')
                    ->label("Status Pengajuan ke Bendahara")
                    ->relationship('statusPengajuanBendahara', 'nama')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('status_pengajuan_ppspm_id')
                    ->label("Status Pengajuan ke PPSPM")
                    ->relationship('statusPengajuanPpspm', 'nama')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('jenisDokumen')
                    ->label("Jenis Dokumen")
                    ->relationship('jenisDokumen', 'nama')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Action::make("linkfolder")
                        ->label("Lihat Dokumen")
                        ->icon("heroicon-m-link")
                        ->url(fn(Pengajuan $record): string => $record->link_folder_dokumen)
                        ->openUrlInNewTab()
                        ->hidden(function (Pengajuan $record) {
                            return !($record->link_folder_dokumen);
                        }),
                    Action::make("Ubah Pengajuan")
                        ->label("Ubah Pengajuan")
                        ->icon("heroicon-o-pencil")
                        ->form(PengajuanForms::pengajuanPembayaran())
                        ->fillForm(function (Pengajuan $record): array {
                            return $record->toArray();
                        })
                        ->action(function (array $data, Pengajuan $record) {
                            PengajuanServices::ubahPengajuan($data, $record);
                        }),
                    Action::make("Aksi Pengaju")
                        ->label("Tanggapan Pengaju")
                        ->icon("heroicon-o-pencil")
                        ->form(PengajuanForms::tanggapanPengaju())
                        ->fillForm(function (Pengajuan $record): array {
                            return $record->toArray();
                        })
                        ->action(function (array $data, Pengajuan $record) {
                            PengajuanServices::tanggapi($data, $record);
                        }),
                    Action::make("Aksi PPK")
                        ->label("Aksi PPK")
                        ->modalHeading('Pemeriksaan PPK')
                        ->hidden(function (Pengajuan $record): bool {
                            return !PengajuanServices::isSiapDiperiksaPpk($record);
                        })
                        ->icon("heroicon-o-pencil")
                        ->form(PengajuanForms::pemeriksaanPpk())
                        ->fillForm(function (Pengajuan $record): array {
                            return $record->toArray();
                        })
                        ->action(function (array $data, Pengajuan $record) {
                            PengajuanServices::pemeriksaanPpk($data, $record);
                        }),
                    Action::make("Aksi Bendahara")
                        ->modalHeading('Pemeriksaan Bendahara')
                        ->label("Aksi Bendahara")
                        ->hidden(function (Pengajuan $record): bool {
                            return !PengajuanServices::isSiapDiperiksaBendahara($record);
                        })
                        ->icon("heroicon-o-pencil")->form(PengajuanForms::pemeriksaanBendahara())
                        ->fillForm(function (Pengajuan $record): array {
                            return $record->toArray();
                        })
                        ->action(function (array $data, Pengajuan $record) {
                            PengajuanServices::pemeriksaanBendahara($data, $record);
                        }),
                    Action::make("Aksi PPSPM")
                        ->label("Aksi PPSPM")
                        ->modalHeading('Pemeriksaan PPSPM')
                        ->hidden(function (Pengajuan $record): bool {
                            return !PengajuanServices::isSiapDiperiksaPpspm($record);
                        })
                        ->icon("heroicon-o-pencil")->form(PengajuanForms::pemeriksaanPpspm())
                        ->fillForm(function (Pengajuan $record): array {
                            return $record->toArray();
                        })
                        ->action(function (array $data, Pengajuan $record) {
                            PengajuanServices::pemeriksaanPpspm($data, $record);
                        }),
                    Action::make("pemrosesanBendahara")
                        ->label("Proses Bendahara")
                        ->hidden(function (Pengajuan $record): bool {
                            return !PengajuanServices::isSiapDiprosesBendahara($record);
                        })
                        ->modalHeading('Pemrosesan Pembayaran')
                        ->icon("heroicon-o-credit-card")->form(PengajuanForms::pemrosesanBendahara())
                        ->fillForm(function (Pengajuan $record): array {
                            return $record->toArray();
                        })
                        ->action(function (array $data, Pengajuan $record) {
                            PengajuanServices::pemrosesanBendahara($data, $record);
                        }),
                    DeleteAction::make("hapus")

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


    #Custom

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengajuans::route('/'),
            'create' => Pages\CreatePengajuan::route('/create'),
            'edit' => Pages\EditPengajuan::route('/{record}/edit'),
        ];
    }
}
