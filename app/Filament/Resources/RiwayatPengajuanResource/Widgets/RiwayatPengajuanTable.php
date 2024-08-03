<?php

namespace App\Filament\Resources\RiwayatPengajuanResource\Widgets;

use App\Models\Kegiatan;
use App\Models\MasterSls;
use App\Models\Mitra;
use App\Models\Pegawai;
use App\Models\Penugasan;
use App\Models\RiwayatPengajuan;
use App\Supports\Constants;
use App\Supports\TanggalMerah;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class RiwayatPengajuanTable extends BaseWidget
{
    protected static ?string $heading = 'Riwayat Pengajuan Surat Tugas Anda';
    protected int | string | array $columnSpan = 'full';

    protected function getTableHeaderActions(): array
    {
        return [
            Action::make("tambah_pengajuan")
                    ->closeModalByClickingAway(false)
                    ->label("Pengajuan Surat Tugas")
                    ->icon("fluentui-document-add-20-o")
                    ->form([
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
                            ->label("Mitra")
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
                            ->label("Pegawai")
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
                            ->options(function(){
                                return Kegiatan::pluck('nama','id')->toArray();
                            })
                            ->required()
                            ->searchable(['nama']),
                        DatePicker::make("tgl_pengajuan_tugas")
                            ->native(false)
                            ->required()
                            ->label("Tanggal Pengajuan")
                            ->default(now()->startOfDay())
                            ->live()
                            ,
                        DatePicker::make('tgl_mulai_tugas')
                            ->hidden(function(Get $get){
                                return $get('nips')==null;
                            })
                            ->afterStateUpdated(function (?string $state, ?string $old,Set $set) {
                                $set('tgl_akhir_tugas',null);
                            })
                            ->native(false)
                            ->live()
                            ->minDate(function(Get $get){
                                return $get('tgl_pengajuan_tugas');
                            })
                            ->maxDate(function(Get $get){
                                if($get('tgl_akhir_tugas')) return $get('tgl_akhir_tugas');
                                return now()->addMonth(2);
                            })
                            ->disabledDates(function(Get $get){
                                return array_merge(Penugasan::getDisabledDates($get("nips")),TanggalMerah::getLiburDates());
                            })
                            ->required()
                            ->label("Tanggal Mulai Penugasan"),
                        DatePicker::make('tgl_akhir_tugas')
                            ->hidden(function(Get $get){
                                return $get('tgl_mulai_tugas')==null || $get('nips')==null;
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
                                return $get('level_tujuan_penugasan') == Constants::LEVEL_PENUGASAN_KABUPATEN_KOTA;
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
                                return $get('level_tujuan_penugasan') == Constants::LEVEL_PENUGASAN_KECAMATAN;
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
                                return $get('level_tujuan_penugasan') == Constants::LEVEL_PENUGASAN_DESA_KELURAHAN;
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
                    ])
                    ->action(function (array $data):void {
                        if(Penugasan::ajukan($data)){
                            Notification::make()
                            ->title('Pengajuan berhasil dikirim')
                            ->success()
                            ->send();
                        };
                    })
                    ,
                ];
    }

    public function table(Table $table): Table
    {
        $query = RiwayatPengajuan::query()->with('penugasan')->whereHas("penugasan",function($query){
            $query->whereHas('pegawai',function($query){$query->where('nip',auth()->user()->pegawai?->nip);
            });
        });
        return $table
            ->headerActions(
                $this->getTableHeaderActions()
            )
            ->query(
                $query
            )
            ->columns([
                TextColumn::make("penugasan.kegiatan.nama"),
                TextColumn::make("tgl_perjadin")
                    ->label('Tanggal Perjadin')
                    ->badge()
                    ->state(function (RiwayatPengajuan $record){
                        return $record->penugasan->tgl_perjadin;
                    })
                ,
                TextColumn::make('last_status')
                    ->label("Status")
                    ->color(function($state){
                        if($state == 'Dikirim') return 'primary';
                        if($state == 'Disetujui') return 'success';
                        if($state == 'Dicetak') return 'success';
                        if($state == 'Dicairkan') return 'success';
                        if($state == 'Dibatalkan') return 'secondary';
                        if($state == 'Ditolak') return 'danger';
                        if($state == 'Perlu Revisi') return 'warning';
                    })
                    ->badge()
                ,
                TextColumn::make('last_status_timestamp')
                    ->label('Tanggal Perubahan Status')
                ,
            ]);
    }
}
