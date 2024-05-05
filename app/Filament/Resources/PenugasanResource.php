<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenugasanResource\Pages;
use App\Filament\Resources\PenugasanResource\RelationManagers;
use App\Filament\Resources\PenugasanResource\Widgets\StatusSuratTugasChart;
use App\Models\Kegiatan;
use App\Models\MasterSls;
use App\Models\Pegawai;
use App\Models\Penugasan;
use App\Supports\Constants;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;

use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;


class PenugasanResource extends Resource
{
    protected static ?string $model = Penugasan::class;

    protected static ?string $label = "Pengajuan";
    protected static ?string $navigationLabel = "Pengajuan";
    protected static ?string $pluralModelLabel = "Pengajuan";
    protected static ?string $navigationIcon = 'fluentui-document-add-24-o';
    protected static ?string $navigationGroup = "Surat Tugas";

    public static function getWidgets(): array
    {
        return [
            StatusSuratTugasChart::class
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nip')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kegiatan_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('jenis_perjadin')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('tgl_mulai_tugas'),
                Forms\Components\DateTimePicker::make('tgl_akhir_tugas'),
                Forms\Components\TextInput::make('tbh_hari_jalan_awal')
                    ->default(0)
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('tbh_hari_jalan_akhir')
                    ->default(0)
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('prov_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kabkot_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kecamatan_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('desa_kel_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('jenis_surat_tugas')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('surat_tugas_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('pegawai_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('transportasi')
                    ->required()
                    ->maxLength(255)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function(Builder $query){
                return $query->with(["riwayatPengajuan"]);
            })
            ->columns([
                Tables\Columns\TextColumn::make('pegawai.nama')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('kegiatan.nama')
                    ->numeric()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_perjadin')
                    ->badge()
                    ->label("Tanggal Perjadin")
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tbh_hari_jalan_awal')
                    ->label("Tambah Hari Perjalanan Awal")
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('tbh_hari_jalan_akhir')
                    ->label("Tambah Hari Perjalanan Akhir")
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('provinsi.provinsi')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('kabkot.kabkot')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('kecamatan.kecamatan')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('desa.desa_kel')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_surat_tugas')
                    ->label("Jenis Surat Tugas")
                    ->state(function (Penugasan $record): string {
                        return $record->jenis_surat;
                    })
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('surat_tugas_id')
                    ->label('Nomor Surat Tugas')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('plh.nama')
                    ->label("Penyetuju")
                    ->numeric()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('riawayatPengajuan.status')
                    ->badge()
                    ->state(function (Penugasan $record): string {
                        return $record->riwayatPengajuan?->last_status ?? "";
                    })
                    ->toggleable()
                    ->label("Status"),
                    // ->searchable(),
                Tables\Columns\TextColumn::make('transportasi')
                    ->state(function (Penugasan $record): string {
                        return $record->jenis_transportasi;
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
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
                SelectFilter::make('pegawai')
                    ->relationship('pegawai', 'nama')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                DateRangeFilter::make("tgl_mulai_tugas")
                    ->label("Tanggal Mulai Tugas"),
                DateRangeFilter::make("tgl_akhir_tugas")
                    ->label("Tanggal Akhir Tugas"),
                SelectFilter::make('riwayatPengajuan')
                    ->options(Constants::STATUS_PENGAJUAN_OPTIONS)
                    ->searchable()
                    ->multiple()
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['values']))
                        {
                            // if we have a value (the aircraft ID from our options() query), just query a nested
                            // set of whereHas() clauses to reach our target, in this case two deep
                            $query->whereHas(
                                'riwayatPengajuan',
                                fn (Builder $query) => $query->whereIn('status',$data['values'])
                            );
                        }
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make("setujui")
                    ->visible(function (Penugasan $record){
                        return $record->canSetujui();
                        ;})
                    ->action(function (Penugasan $record){
                        if($record->setujui())
                        return self::notify("success","Penugasan berhasil disetujui");
                        return self::notify("danger","Aksi penyetujuan gagal");
                    })
                    ->label("Setujui"),
                Action::make("batalkan")
                    ->visible(function (Penugasan $record){
                        return $record->canBatalkan();
                    ;})
                    ->requiresConfirmation()
                    ->modalDescription("Apakah anda yakin akan membatalkan pengajuan ini? (Pengajuan ini perlu dibuat ulang jika ingin diajukan kembali)")
                    ->action(function (Penugasan $record){
                        if($record->batalkan())
                        return self::notify("success","Penugasan berhasil dibatalkan");
                        return self::notify("danger","Aksi pembatalan gagal");
                    })
                    ->label("Batalkan"),
                Action::make("tolak")
                    ->visible(function (Penugasan $record){
                        return $record->canTolak()
                    ;})
                    ->action(function (Penugasan $record){
                        if($record->tolak())
                        return self::notify("success","Penugasan berhasil ditolak");
                        return self::notify("danger","Aksi penolakan gagal");
                    })
                    ->label("Tolak"),
                Action::make("cetak")
                    ->visible(function (Penugasan $record){
                        return $record->canCetak();
                    ;})
                    ->action(function (Penugasan $record){
                        if($record->cetak())
                        return self::notify("success","Penugasan berhasil dicetak");
                        return self::notify("danger","Aksi pencetakan gagal");
                    })
                    ->label("Cetak"),
                Action::make("kumpulkan")
                    ->visible(function (Penugasan $record){
                        return $record->canKumpulkan();
                    ;})
                    ->action(function (Penugasan $record){
                        if($record->kumpulkan())
                        return self::notify("success","Penugasan berhasil dikumpulkan");
                        return self::notify("danger","Aksi pengumpulan gagal");
                    })
                    ->label("Kumpulkan"),
                Action::make("Cairkan")
                    ->visible(function (Penugasan $record){
                        return $record->canCairkan();
                    ;})
                    ->action(function (Penugasan $record){
                        if($record->cairkan())
                        return self::notify("success","Penugasan berhasil dicairkan");
                        return self::notify("danger","Aksi pencairan gagal");
                    })
                    ->label("Cairkan"),
                Action::make("revisi")
                    ->mountUsing(function (Form $form,Penugasan $record){
                        $form->fill([
                            ...$record->toArray(),
                            ...[
                                "id"=>$record->id,
                                "catatan_butuh_perbaikan"=>$record->riwayatPengajuan->catatan_butuh_perbaikan
                                ]
                        ]);
                    })

                    ->visible(function (Penugasan $record){
                        return $record->canPerluPerbaikan();
                    ;})
                    ->label("Arahkan Revisi")
                    ->form([
                        Hidden::make("id"),
                        Textarea::make("catatan_butuh_perbaikan")
                            ->required()
                            ->label("Yang Perlu Diperbaiki:"),
                        Select::make("jenis_surat_tugas")
                            ->options(
                                Constants::JENIS_SURAT_TUGAS_OPTIONS
                            )
                            ->searchable()
                            ->disabled()
                            ,
                        Select::make("nip")
                            ->label("Pegawai")
                            ->options(function(){
                                return Pegawai::pluck('nama','nip')->toArray();
                            })
                            ->searchDebounce(100)
                            ->searchable(['nama'])
                            ->disabled(),
                        Select::make("kegiatan_id")
                            ->label("Kegiatan")
                            ->relationship('kegiatan','id')
                            ->searchDebounce(100)
                            ->getOptionLabelFromRecordUsing(fn(Kegiatan $record)=>$record->nama)
                            ->disabled()
                            ->searchable(['nama']),
                        DatePicker::make('tgl_mulai_tugas')
                            ->disabled()
                            ->label("Tanggal Mulai Penugasan"),
                        DatePicker::make('tgl_akhir_tugas')
                            ->disabled()
                            ->label("Tanggal Selesai Penugasan"),
                        TextInput::make("tbh_hari_jalan_awal")
                            ->disabled()
                            ->placeholder("Masukan tanggal")
                            ->numeric()
                            ->label("Tambah Hari Awal Perjalanan"),
                        TextInput::make("tbh_hari_jalan_akhir")
                            ->disabled()
                            ->placeholder("Masukan tanggal")
                            ->numeric()
                            ->label("Tambah Hari Akhir Perjalanan"),
                        Select::make("prov_id")
                            ->label("Provinsi")
                            ->disabled()
                            ->relationship('provinsi','prov_id')
                            ->searchDebounce(100)
                            ->getOptionLabelFromRecordUsing(fn(MasterSls $record)=>$record->provinsi)
                            ->searchable(['provinsi']),
                        Select::make("kabkot_id")
                            ->disabled()
                            ->label("Kabupaten/Kota")
                            ->relationship('kabkot','kabkot_id')
                            ->searchDebounce(100)
                            ->getOptionLabelFromRecordUsing(fn(MasterSls $record)=>$record->kabkot)
                            ->searchable(['kabkot']),
                        Select::make("kecamatan_id")
                            ->disabled()
                            ->label("Kecamatan")
                            ->relationship('kecamatan','kec_id')
                            ->searchDebounce(100)
                            ->getOptionLabelFromRecordUsing(fn(MasterSls $record)=>$record->kecamatan)
                            ->searchable(['kecamatan']),
                        Select::make("desa_kel_id")
                            ->label("Desa/Kelurahan")
                            ->disabled()
                            ->relationship('desa','desa_kel_id')
                            ->searchDebounce(100)
                            ->getOptionLabelFromRecordUsing(fn(MasterSls $record)=>$record->desa_kel)
                            ->searchable(['desa_kel']),
                        Select::make("transportasi")
                            ->options(
                                Constants::JENIS_TRANSPORTASI_OPTIONS
                            )
                            ->searchable()
                            ->disabled()
                            ,
                    ])
                    ->action(function(array $data){
                        if(Penugasan::perluPerbaikan($data))
                        return self::notify("success","Arahan perbaikan berhasil dikirim");
                        return self::notify("danger","Arahan perbaikan gagal dikirim");
                    })
                ,
                Action::make("ajukan_revisi")
                    ->label("Perbaiki")
                    ->visible(function (Penugasan $record){
                        return $record->canAjukanRevisi();
                    ;})
                    ->mountUsing(function (Form $form,Penugasan $record){
                        $form->fill([
                            ...$record->toArray(),
                            ...[
                                "id"=>$record->id,
                                "catatan_butuh_perbaikan"=>$record->riwayatPengajuan->catatan_butuh_perbaikan
                                ]
                        ]);
                    })
                    ->form([
                        Hidden::make("id")
                        ,
                        Textarea::make("catatan_butuh_perbaikan")
                            ->label("Catatan Revisi")
                            ->disabled()
                        ,
                        Select::make("jenis_surat_tugas")
                            ->options(
                                Constants::JENIS_SURAT_TUGAS_OPTIONS
                            )
                            ->searchable()
                            ->required()
                            ,
                        Select::make("nip")
                            ->label("Pegawai")
                            ->options(function(){
                                return Pegawai::pluck('nama','nip')->toArray();
                            })
                            ->searchDebounce(100)
                            ->searchable(['nama'])
                            ->required(),
                        Select::make("kegiatan_id")
                            ->label("Kegiatan")
                            ->relationship('kegiatan','id')
                            ->searchDebounce(100)
                            ->getOptionLabelFromRecordUsing(fn(Kegiatan $record)=>$record->nama)
                            ->required()
                            ->searchable(['nama']),
                        DatePicker::make('tgl_mulai_tugas')
                            ->required()
                            ->label("Tanggal Mulai Penugasan"),
                        DatePicker::make('tgl_akhir_tugas')
                            ->required()
                            ->label("Tanggal Selesai Penugasan"),
                        TextInput::make("tbh_hari_jalan_awal")
                            ->required()
                            ->placeholder("Masukan tanggal")
                            ->numeric()
                            ->label("Tambah Hari Awal Perjalanan"),
                        TextInput::make("tbh_hari_jalan_akhir")
                            ->required()
                            ->placeholder("Masukan tanggal")
                            ->numeric()
                            ->label("Tambah Hari Akhir Perjalanan"),
                        Select::make("prov_id")
                            ->label("Provinsi")
                            ->required()
                            ->relationship('provinsi','prov_id')
                            ->searchDebounce(100)
                            ->getOptionLabelFromRecordUsing(fn(MasterSls $record)=>$record->provinsi)
                            ->searchable(['provinsi']),
                        Select::make("kabkot_id")
                            ->required()
                            ->label("Kabupaten/Kota")
                            ->relationship('kabkot','kabkot_id')
                            ->searchDebounce(100)
                            ->getOptionLabelFromRecordUsing(fn(MasterSls $record)=>$record->kabkot)
                            ->searchable(['kabkot']),
                        Select::make("kecamatan_id")
                            ->required()
                            ->label("Kecamatan")
                            ->relationship('kecamatan','kec_id')
                            ->searchDebounce(100)
                            ->getOptionLabelFromRecordUsing(fn(MasterSls $record)=>$record->kecamatan)
                            ->searchable(['kecamatan']),
                        Select::make("desa_kel_id")
                            ->label("Desa/Kelurahan")
                            ->relationship('desa','desa_kel_id')
                            ->searchDebounce(100)
                            ->getOptionLabelFromRecordUsing(fn(MasterSls $record)=>$record->desa_kel)
                            ->searchable(['desa_kel']),
                        Select::make("transportasi")
                            ->options(
                                Constants::JENIS_TRANSPORTASI_OPTIONS
                            )
                            ->searchable()
                            ->required()
                            ,
                    ])
                    ->action(function (array $data) {
                        // dd($data);
                        if(Penugasan::ajukanRevisi($data))
                        return self::notify("success","Revisi pengajuan berhasil dikirim");
                        return self::notify("danger","Revisi pengajuan berhasil dikirim");
                    })
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
            'index' => Pages\ListPenugasans::route('/'),
            // 'create' => Pages\CreatePenugasan::route('/create'),
            'edit' => Pages\EditPenugasan::route('/{record}/edit'),
        ];
    }
    public static function notify(string $notificationStatus, string $title){
        if($notificationStatus == "success"){
            Notification::make()
                ->title($title)
                ->success()
                ->send();
        }
        if($notificationStatus == "danger"){
            Notification::make()
                ->title($title)
                ->danger()
                ->send();
        }
    }
}
