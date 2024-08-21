<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenugasanResource\Pages;
use App\Filament\Resources\PenugasanResource\RelationManagers;
use App\Filament\Resources\PenugasanResource\Widgets\StatusSuratTugasChart;
use App\Models\Kegiatan;
use App\Models\MasterSls;
use App\Models\Mitra;
use App\Models\Pegawai;
use App\Models\Penugasan;
use App\Supports\Constants;
use App\Supports\TanggalMerah;
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
use Filament\Forms\Get;
use Filament\Forms\Set;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;


class PenugasanResource extends Resource
{
    protected static ?string $model = Penugasan::class;

    protected static ?string $label = "Pengajuan";
    protected static ?string $navigationLabel = "Pengajuan";
    protected static ?string $pluralModelLabel = "Pengajuan";
    protected static ?string $navigationIcon = 'fluentui-document-add-24-o';
    protected static ?string $navigationGroup = "Surat Tugas";

    public static function canViewAny(): bool{
        return auth()->user()->hasRole('kepala_satker') || auth()->user()->hasRole('operator_umum') || auth()->user()->hasRole('pegawai');
    }
    public static function getWidgets(): array
    {
        return [
            StatusSuratTugasChart::class
        ];
    }
    public static function formFilterPengajuan(){
        return [
            SelectFilter::make('pegawai')
                ->relationship('pegawai', 'nama')
                ->multiple()
                ->searchable()
                ->preload(),
            DateRangeFilter::make("tgl_pengajuan_tugas")
                ->label("Tanggal Pengajuan"),
            DateRangeFilter::make("tgl_mulai_tugas")
                ->label("Tanggal Mulai Tugas"),
            DateRangeFilter::make("tgl_akhir_tugas")
                ->label("Tanggal Akhir Tugas"),
            SelectFilter::make("kegiatan")
                ->label("Kegiatan")
                ->relationship('kegiatan', 'nama')
                ->preload()
                ->multiple()
            ,
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
            ];
    }
    public static function formLihatPengajuan(){
        return [
            Hidden::make("id"),
            Hidden::make("level_tujuan_penugasan")->live(),
            Hidden::make("jenis_surat_tugas")->live(),
            Hidden::make("jenis_peserta")->live(),
            Select::make("kegiatan_id")
                ->label("Kegiatan")
                ->relationship('kegiatan','id')
                ->searchDebounce(100)
                ->getOptionLabelFromRecordUsing(fn(Kegiatan $record)=>$record->nama)
                ->disabled()
                ->searchable(['nama']),
            DatePicker::make("tgl_pengajuan_tugas")
                ->native(false)
                ->required()
                ->label("Tanggal Pengajuan")
                ->live()
                ,
            Select::make("jenis_surat_tugas")
                ->options(
                    Constants::JENIS_SURAT_TUGAS_OPTIONS
                )
                ->searchable()
                ->disabled()
                ,
            Select::make("nama_pegawai")
                ->label("Pegawai Ditugaskan")
                ->hidden(function(Get $get, Penugasan $record){
                    return $record->jenis_peserta == Constants::JENIS_PESERTA_SURAT_TUGAS_MITRA;
                })
                ->relationship('satuSurat.pegawai','nip')
                ->getOptionLabelFromRecordUsing(fn(Pegawai $record)=>$record->nama)
                ->searchable(['nama'])
                ->multiple()
                ->disabled(),
            Select::make("nama_mitra")
                ->label("Mitra Ditugaskan")
                ->hidden(function(Get $get,Penugasan $record){
                    return $record->jenis_peserta == Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI;
                })
                ->relationship('satuSurat.mitra','id_sobat')
                ->getOptionLabelFromRecordUsing(fn(Mitra $record)=>$record->nama_1)
                ->searchable(['nama_1'])
                ->multiple()
                ->disabled(),
            Select::make("plh_id")
                ->label("Penyetuju")
                ->relationship('plh','nip')
                ->getOptionLabelFromRecordUsing(fn(Pegawai $record)=>$record->nama)
                ->searchable(['nip'])
                ->disabled(),
            Select::make("nip_pengaju")
                ->label("Pengaju")
                ->relationship('pengaju','nip_pengaju')
                ->getOptionLabelFromRecordUsing(fn(Pegawai $record)=>$record->nama)
                ->searchable(['nama'])
                ->disabled(),
            DatePicker::make('tgl_mulai_tugas')
                ->disabled()
                ->label("Tanggal Mulai Penugasan"),
            DatePicker::make('tgl_akhir_tugas')
                ->disabled()
                ->label("Tanggal Selesai Penugasan"),
            TextInput::make("tbh_hari_jalan_awal")
                ->disabled()
                ->hidden(function(Get $get){
                    if($get("jenis_surat_tugas") != Constants::PERJALANAN_DINAS_LUAR_KOTA) return true;
                    return collect([
                        null,
                        Constants::NON_SPPD,
                    ])->contains($get('jenis_surat_tugas'))
                    || collect([
                        null,
                        Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                    ])->contains($get('level_tujuan_penugasan'));
                })
                ->placeholder("Masukan tanggal")
                ->numeric()
                ->label("Tambah Hari Awal Perjalanan"),
            TextInput::make("tbh_hari_jalan_akhir")
                ->disabled()
                ->hidden(function(Get $get){
                    if($get("jenis_surat_tugas") != Constants::PERJALANAN_DINAS_LUAR_KOTA) return true;
                    return collect([
                        null,
                        Constants::NON_SPPD,
                    ])->contains($get('jenis_surat_tugas'))
                    || collect([
                        null,
                        Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                    ])->contains($get('level_tujuan_penugasan'));
                })
                ->placeholder("Masukan tanggal")
                ->numeric()
                ->label("Tambah Hari Akhir Perjalanan"),
            Select::make("prov_ids")
                ->label("Provinsi")
                ->disabled()
                ->relationship('tujuanSuratTugas.provinsi','prov_id')
                ->searchDebounce(100)
                ->getOptionLabelFromRecordUsing(fn(MasterSls $record)=>$record->provinsi)
                ->searchable(['provinsi']),
            Select::make("kabkot_ids")
                ->disabled()
                ->hidden(function(Get $get){
                    return collect([
                        null,
                        Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                    ])->contains($get('level_tujuan_penugasan'));
                })
                ->multiple(function(Get $get){
                    return self::butuhMultipleTujuan($get("jenis_surat_tugas"),$get('level_tujuan_penugasan'),Constants::LEVEL_PENUGASAN_KABUPATEN_KOTA);
                })
                ->label("Kabupaten/Kota")
                ->relationship('tujuanSuratTugas.kabkot','kabkot_id')
                ->searchDebounce(100)
                ->getOptionLabelFromRecordUsing(fn(MasterSls $record)=>$record->kabkot)
                ->searchable(['kabkot']),
            Select::make("kecamatan_ids")
                ->disabled()
                ->hidden(function(Get $get){
                    return collect([
                        null,
                        Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                        Constants::LEVEL_PENUGASAN_KABUPATEN_KOTA,
                    ])->contains($get('level_tujuan_penugasan'));
                })
                ->multiple(function(Get $get){
                    return self::butuhMultipleTujuan($get("jenis_surat_tugas"),$get('level_tujuan_penugasan'),Constants::LEVEL_PENUGASAN_KECAMATAN);
                })
                ->label("Kecamatan")
                ->relationship('tujuanSuratTugas.kecamatan','kec_id')
                ->searchDebounce(100)
                ->getOptionLabelFromRecordUsing(fn(MasterSls $record)=>$record->kecamatan)
                ->searchable(['kecamatan']),
            Select::make("desa_kel_ids")
                ->label("Desa/Kelurahan")
                ->hidden(function(Get $get){
                    return !collect([
                        Constants::LEVEL_PENUGASAN_DESA_KELURAHAN,
                    ])->contains($get('level_tujuan_penugasan'));
                })
                ->multiple(function(Get $get){
                    return self::butuhMultipleTujuan($get("jenis_surat_tugas"),$get('level_tujuan_penugasan'),Constants::LEVEL_PENUGASAN_DESA_KELURAHAN);
                })
                ->disabled()
                ->relationship('tujuanSuratTugas.desa','desa_kel_id')
                ->searchDebounce(100)
                ->getOptionLabelFromRecordUsing(fn(MasterSls $record)=>$record->desa_kel)
                ->searchable(['desa_kel']),
            Select::make("transportasi")
                ->options(
                    Constants::JENIS_TRANSPORTASI_OPTIONS
                )
                ->hidden(function(Get $get){
                    return collect([
                        null,
                        Constants::NON_SPPD,
                    ])->contains($get('jenis_surat_tugas'))
                    || collect([
                        null,
                        Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                    ])->contains($get('level_tujuan_penugasan'));
                })
                ->searchable()
                ->disabled()
                ,
                ];
    }
    public static function formPengajuan(){
        return [
            Select::make("jenis_surat_tugas")
                            ->options(
                                Constants::JENIS_SURAT_TUGAS_OPTIONS
                            )
                            ->live()
                            ->required()
                            ,
                        Select::make("jenis_peserta")
                            ->hidden(function(Get $get){
                                return $get('jenis_surat_tugas') == null;
                            })
                            ->options(
                                Constants::JENIS_PESERTA_SURAT_TUGAS
                            )
                            ->live()
                            ->required()
                            ,
                        Select::make("mitras")
                            ->hidden(function(Get $get){
                                return !($get('jenis_peserta') != Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI);
                            })
                            ->afterStateUpdated(function (?array $state, ?array $old,Set $set) {
                                $set('tgl_mulai_tugas',null);
                            })
                            ->label("Mitra Ditugaskan")
                            ->live()
                            ->options(function(){
                                return Mitra::pluck('nama_1','id_sobat')->toArray();
                            })
                            ->searchDebounce(100)
                            // ->getOptionLabelFromRecordUsing(fn(Pegawai $record)=>$record->nama)
                            ->searchable(['nama_1'])
                            ->required(function(Get $get){
                                return $get("jenis_peserta") == Constants::JENIS_PESERTA_SURAT_TUGAS_MITRA
                                    || $get("jenis_peserta") == Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI_MITRA;
                            })
                            ->multiple(),
                        Select::make("nips")
                            ->hidden(function(Get $get){
                                return $get('jenis_peserta') == Constants::JENIS_PESERTA_SURAT_TUGAS_MITRA;
                            })
                            ->afterStateUpdated(function (?array $state, ?array $old,Set $set) {
                                $set('tgl_mulai_tugas',null);
                            })
                            ->label("Pegawai Ditugaskan")
                            ->live()
                            ->options(function(){
                                return Pegawai::pluck('nama','nip')->toArray();
                            })
                            ->searchDebounce(100)
                            // ->getOptionLabelFromRecordUsing(fn(Pegawai $record)=>$record->nama)
                            ->searchable(['nama'])
                            ->required()
                            ->multiple(),
                        Select::make("kegiatan_id")
                            ->label("Kegiatan")
                            ->preload()
                            ->options(function(){
                                return Kegiatan::orderBy('created_at','desc')->pluck('nama','id')->toArray();
                            })
                            ->required()
                            ->searchable(['nama']),
                        DatePicker::make("tgl_pengajuan_tugas")
                            ->native(false)
                            ->helperText('Tanggal ini bukan tanggal mulai surat tugas, tapi tanggal Anda membuat pengajuan ini.')
                            ->maxDate(now()->endOfDay())
                            ->required()
                            ->label("Tanggal Anda Membuat Pengajuan Surat Tugas (Hari Ini)")
                            ->default(now()->startOfDay())
                            ->live()
                            ,
                        DatePicker::make('tgl_mulai_tugas')
                            ->hidden(function(Get $get){
                                return !($get('nips') != null || $get("mitras") != null);
                            })
                            ->afterStateUpdated(function (?string $state, ?string $old,Set $set) {
                                $set('tgl_akhir_tugas',null);
                            })
                            ->native(false)
                            ->live()
                            ->minDate(function(Get $get){
                                $kegiatan = Kegiatan::find($get('kegiatan_id')) ?? null;
                                return max($get('tgl_pengajuan_tugas'),$kegiatan?->tgl_awal_perjadin);
                            })
                            ->maxDate(function(Get $get){
                                $kegiatan = Kegiatan::find($get('kegiatan_id')) ?? null;
                                // if($get('tgl_akhir_tugas')) return $get('tgl_akhir_tugas');
                                return min(now()->addMonth(2),$kegiatan?->tgl_akhir_perjadin,$get('tgl_akhir_tugas'));
                            })
                            ->disabledDates(function(Get $get){
                                return array_merge(Penugasan::getDisabledDates($get("nips")),TanggalMerah::getLiburDates());
                            })
                            ->required()
                            ->label("Tanggal Mulai Penugasan"),
                        DatePicker::make('tgl_akhir_tugas')
                            ->hidden(function(Get $get){
                                return !(($get('nips') != null || $get("mitras") != null) && $get("tgl_mulai_tugas") != null);
                            })
                            ->native(false)
                            ->live()
                            ->minDate(function(Get $get){
                                if($get('tgl_mulai_tugas')) return $get('tgl_mulai_tugas');
                                return now()->subMonth(2);
                            })
                            ->maxDate(function(Get $get){
                                return Penugasan::getMinDate($get("tgl_mulai_tugas"),$get("nips")) ?? now()->addMonth(2);
                            })
                            ->disabledDates(function(Get $get){
                                return Penugasan::getDisabledDates($get("nips"));
                            })
                            ->required()
                            ->label("Tanggal Selesai Penugasan"),
                        TextInput::make("tbh_hari_jalan_awal")
                            ->hidden(function(Get $get){
                                if($get("jenis_surat_tugas") != Constants::PERJALANAN_DINAS_LUAR_KOTA) return true;
                                return collect([
                                    null,
                                    Constants::NON_SPPD,
                                ])->contains($get('jenis_surat_tugas'))
                                || collect([
                                    null,
                                    Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                                ])->contains($get('level_tujuan_penugasan'));
                            })
                            ->required(function(Get $get){
                                return !(collect([
                                    null,
                                    Constants::NON_SPPD,
                                ])->contains($get('jenis_surat_tugas'))
                                || collect([
                                    null,
                                    Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                                ])->contains($get('level_tujuan_penugasan')));
                            })
                            ->placeholder("Masukan jumlah hari")
                            ->numeric()
                            ->default(0)
                            ->label("Tambah Hari Awal Perjalanan"),

                        TextInput::make("tbh_hari_jalan_akhir")
                            ->hidden(function(Get $get){
                                if($get("jenis_surat_tugas") != Constants::PERJALANAN_DINAS_LUAR_KOTA) return true;
                                return collect([
                                    null,
                                    Constants::NON_SPPD,
                                ])->contains($get('jenis_surat_tugas'))
                                || collect([
                                    null,
                                    Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                                ])->contains($get('level_tujuan_penugasan'));
                            })
                            ->required(function(Get $get){
                                return !(collect([
                                    null,
                                    Constants::NON_SPPD,
                                ])->contains($get('jenis_surat_tugas'))
                                || collect([
                                    null,
                                    Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                                ])->contains($get('level_tujuan_penugasan')));
                            })
                            ->placeholder("Masukan jumlah hari")
                            ->default(0)
                            ->numeric()
                            ->label("Tambah Hari Akhir Perjalanan"),
                        Select::make("level_tujuan_penugasan")
                            ->afterStateUpdated(function (Set $set) {
                                $set("nama_tempat_tujuan",null);
                                $set("prov_ids",null);
                                $set("kabkot_ids",null);
                                $set("kecamatan_ids",null);
                                $set("desa_kel_ids",null);
                            })
                            ->label("Level Tujuan Penugasan")
                            ->options(Constants::LEVEL_PENUGASAN_OPTIONS)
                            ->live()
                            ->required(),
                        TextInput::make('nama_tempat_tujuan')
                            ->helperText("Contoh: Kantor BPS Kabupaten Kubu Raya; Kantor BPS Provinsi Kalimantan Barat; Pusat Pendidikan dan Pelatihan")
                            ->label("Nama Lokasi Tujuan")
                            ->hidden(function(Get $get){
                                return $get('level_tujuan_penugasan') != Constants::LEVEL_PENUGASAN_NAMA_TEMPAT;
                            })
                            ->required(function(Get $get){
                                return $get('level_tujuan_penugasan') == Constants::LEVEL_PENUGASAN_NAMA_TEMPAT;
                            })
                        ,


                        Select::make("prov_ids")
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set("kabkot_ids",null);
                                $set("kecamatan_ids",null);
                                $set("desa_kel_ids",null);
                            })
                            ->label("Provinsi Tujuan")
                            ->hidden(function(Get $get){
                                return collect([
                                    null,
                                    Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                                ])->contains($get('level_tujuan_penugasan'));
                            })
                            ->required(function(Get $get){
                                return !collect([
                                    null,
                                    Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                                ])->contains($get('level_tujuan_penugasan'));
                            })
                            ->options(function(){
                                return MasterSls::pluck("provinsi","prov_id");
                            })
                            ->searchable(['provinsi']),
                        Select::make("kabkot_ids")
                            ->afterStateUpdated(function (Set $set) {
                                $set("kecamatan_ids",null);
                                $set("desa_kel_ids",null);
                            })
                            ->multiple(function(Get $get){
                                return self::butuhMultipleTujuan($get("jenis_surat_tugas"),$get('level_tujuan_penugasan'),Constants::LEVEL_PENUGASAN_KABUPATEN_KOTA);
                            })
                            ->live()
                            ->required(function(Get $get){
                                return !collect([
                                    null,
                                    Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                                ])->contains($get('level_tujuan_penugasan'));
                            })
                            ->hidden(function(Get $get){
                                return collect([
                                    null,
                                    Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                                ])->contains($get('level_tujuan_penugasan'));
                            })
                            ->label("Kabupaten/Kota Tujuan")
                            ->options(function(Get $get){
                                return MasterSls::
                                    where('prov_id',$get('prov_ids'))
                                    ->pluck("kabkot","kabkot_id");
                            })
                            ->searchable(['kabkot']),
                        Select::make("kecamatan_ids")
                            ->afterStateUpdated(function (Set $set) {
                                $set("desa_kel_ids",null);
                            })
                            ->multiple(function(Get $get){
                                return self::butuhMultipleTujuan($get("jenis_surat_tugas"),$get('level_tujuan_penugasan'),Constants::LEVEL_PENUGASAN_KECAMATAN);
                            })
                            ->live()
                            ->required(function(Get $get){
                                return !collect([
                                    null,
                                    Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                                    Constants::LEVEL_PENUGASAN_KABUPATEN_KOTA,
                                ])->contains($get('level_tujuan_penugasan'));
                            })
                            ->hidden(function(Get $get){
                                return collect([
                                    null,
                                    Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                                    Constants::LEVEL_PENUGASAN_KABUPATEN_KOTA,
                                ])->contains($get('level_tujuan_penugasan'));
                            })
                            ->label("Kecamatan Tujuan")
                            ->options(function(Get $get){
                                return MasterSls::
                                    where('kabkot_id',$get('kabkot_ids'))
                                    ->pluck("kecamatan","kec_id");
                            })
                            ->searchable(['kecamatan']),
                        Select::make("desa_kel_ids")
                            ->multiple(function(Get $get){
                                return self::butuhMultipleTujuan($get("jenis_surat_tugas"),$get('level_tujuan_penugasan'),Constants::LEVEL_PENUGASAN_DESA_KELURAHAN);
                            })
                            ->label("Desa/Kelurahan Tujuan")
                            ->required(function(Get $get){
                                return collect([
                                    Constants::LEVEL_PENUGASAN_DESA_KELURAHAN,
                                ])->contains($get('level_tujuan_penugasan'));
                            })
                            ->hidden(function(Get $get){
                                return !collect([
                                    Constants::LEVEL_PENUGASAN_DESA_KELURAHAN,
                                ])->contains($get('level_tujuan_penugasan'));
                            })
                            ->options(function(Get $get){
                                return MasterSls::
                                    where('kec_id',$get('kecamatan_ids'))
                                    ->pluck("desa_kel","desa_kel_id");
                            })
                            ->searchable(['desa_kel']),
                        Select::make("transportasi")
                            ->hidden(function(Get $get){
                                return collect([
                                    null,
                                    Constants::NON_SPPD,
                                ])->contains($get('jenis_surat_tugas'))
                                || collect([
                                    null,
                                    Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                                ])->contains($get('level_tujuan_penugasan'));
                            })
                            ->options(
                                Constants::JENIS_TRANSPORTASI_OPTIONS
                            )
                            ->searchable()
                            ->required(function(Get $get){
                                return !(collect([
                                    Constants::NON_SPPD,
                                ])->contains($get('jenis_surat_tugas'))
                                || collect([
                                    Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
                                ])->contains($get('level_tujuan_penugasan')));
                            })
                            ,
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
            ->filters(self::formFilterPengajuan())
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(function(){
                        return auth()->user()->hasRole('operator_umum');
                    }),
                Action::make("lihat")
                    ->modalHeading('Pengajuan Surat Tugas')
                    ->disabledForm()
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel("Close")
                    ->mountUsing(function (Form $form,Penugasan $record){
                        $pegawais = Pegawai::whereIn('nip',Penugasan::find($record->id)->satuSurat()->whereHas('riwayatPengajuan',function($query){$query->where('status',Constants::STATUS_PENGAJUAN_DISETUJUI);})->pluck('nip'));
                        $mitras = Mitra::whereIn('id_sobat',Penugasan::find($record->id)->satuSurat()->whereHas('riwayatPengajuan',function($query){$query->where('status',Constants::STATUS_PENGAJUAN_DISETUJUI);})->pluck('id_sobat'));
                        $form->fill([
                            ...$record->toArray(),
                            ...[
                                "id"=>$record->id,
                                "catatan_butuh_perbaikan"=>$record->riwayatPengajuan->catatan_butuh_perbaikan,
                                "nama_pegawai"=>$pegawais->pluck('nip'),
                                "nama_mitra"=>$mitras->pluck('id_sobat'),
                                "prov_ids"=>$record->tujuanSuratTugas->pluck('prov_id'),
                                "kabkot_ids"=>$record->tujuanSuratTugas->pluck('kabkot_id'),
                                "kecamatan_ids"=>$record->tujuanSuratTugas->pluck('kecamatan_id'),
                                "desa_kel_ids"=>$record->tujuanSuratTugas->pluck('desa_kel_id'),
                                // "id"=>$record->id,
                                // "id"=>$record->id,
                            ]
                        ]);
                    })
                    ->form([
                        Hidden::make("id"),
                        Textarea::make("catatan_butuh_perbaikan")
                            ->disabled()
                            ->label("Yang Perlu Diperbaiki:"),
                        ...self::formLihatPengajuan()
                    ])
                ,
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
                    ->requiresConfirmation()
                    ->modalDescription("Apakah anda yakin akan mengubah status ini menjadi 'Dikumpulkan'?")
                    ->action(function (Penugasan $record){
                        if($record->kumpulkan())
                        return self::notify("success","Penugasan berhasil dikumpulkan");
                        return self::notify("danger","Aksi pengumpulan gagal");
                    })
                    ->label("Kumpulkan"),
                Action::make("batalkanPengumpulan")
                    ->visible(function (Penugasan $record){
                        return $record->canBatalkanPengumpulan();
                    ;})
                    ->requiresConfirmation()
                    ->modalDescription("Apakah anda yakin akan mengubah status ini menjadi 'Dicetak'?")
                    ->action(function (Penugasan $record){
                        if($record->batalkanPengumpulan())
                        return self::notify("success","Penugasan berhasil diubah menjadi 'Dicetak'");
                        return self::notify("danger","Aksi perubahan gagal");
                    })
                    ->label("Batalkan Pengumpulan"),
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
    public static function butuhMultipleTujuan($jenis_surat, $level_tujuan_penugasan,$level_tujuan_field){
        if($jenis_surat == Constants::NON_SPPD){
            if($level_tujuan_field == $level_tujuan_penugasan) return true;
        }
        return false;
    }
}
