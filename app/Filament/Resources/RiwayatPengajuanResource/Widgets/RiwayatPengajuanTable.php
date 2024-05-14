<?php

namespace App\Filament\Resources\RiwayatPengajuanResource\Widgets;

use App\DTO\PenugasanCreation;
use App\Models\Kegiatan;
use App\Models\MasterSls;
use App\Models\Pegawai;
use App\Models\Penugasan;
use App\Models\RiwayatPengajuan;
use App\Supports\Constants;
use App\Supports\TanggalMerah;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Tables;
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
                    ->label("Pengajuan Surat Tugas")
                    ->icon("fluentui-document-add-20-o")
                    ->mountUsing(function (Form $form){
                        $form->fill([

                        ]);
                    })
                    ->form([
                        Select::make("jenis_surat_tugas")
                            ->options(
                                Constants::JENIS_SURAT_TUGAS_OPTIONS
                            )
                            ->searchable()
                            ->required()
                            ,
                        Select::make("nips")
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
                        DatePicker::make('tgl_mulai_tugas')
                            ->hidden(function(Get $get){
                                return $get('nips')==null;
                            })
                            ->afterStateUpdated(function (?string $state, ?string $old,Set $set) {
                                $set('tgl_selesai_tugas',null);
                            })
                            ->native(false)
                            ->live()
                            ->minDate(now()->subMonth(2))
                            ->maxDate(function(Get $get){
                                if($get('tgl_selesai_tugas')) return $get('tgl_selesai_tugas');
                                return now()->addMonth(2);
                            })
                            ->disabledDates(function(Get $get){
                                return array_merge(Penugasan::getDisabledDates($get("nips")),TanggalMerah::getLiburDates());
                            })
                            ->required()
                            ->label("Tanggal Mulai Penugasan"),
                        DatePicker::make('tgl_selesai_tugas')
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
                        TextInput::make("tbh_awal_perjalan")
                            ->default(0)
                            ->required()
                            ->placeholder("Masukan tanggal")
                            ->numeric()
                            ->label("Tambah Hari Awal Perjalanan"),
                        TextInput::make("tbh_akhir_perjalan")
                            ->default(0)
                            ->required()
                            ->placeholder("Masukan tanggal")
                            ->numeric()
                            ->label("Tambah Hari Akhir Perjalanan"),
                        Select::make("prov_id")
                            ->label("Provinsi")
                            ->required()
                            ->options(function(){
                                return MasterSls::pluck("provinsi","prov_id");
                            })
                            ->searchable(['provinsi']),
                        Select::make("kabkot_id")
                            ->required()
                            ->label("Kabupaten/Kota")
                            ->options(function(){
                                return MasterSls::pluck("kabkot","kabkot_id");
                            })
                            ->searchable(['kabkot']),
                        Select::make("kec_id")
                            ->required()
                            ->label("Kecamatan")
                            ->options(function(){
                                return MasterSls::pluck("kecamatan","kec_id");
                            })
                            ->searchable(['kecamatan']),
                        Select::make("desa_kel_id")
                            ->label("Desa/Kelurahan")
                            ->options(function(){
                                return MasterSls::pluck("desa_kel","desa_kel_id");
                            })
                            ->searchable(['desa_kel']),
                        Select::make("transportasi")
                            ->options(
                                Constants::JENIS_TRANSPORTASI_OPTIONS
                            )
                            ->searchable()
                            ->required()
                            ,
                    ])
                    ->action(function (array $data):void {
                        if(Penugasan::ajukan(PenugasanCreation::create($data))){
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
                    ->badge()
                ,
                TextColumn::make('last_status_timestamp')
                    ->label('Tanggal Perubahan Status')
                ,
            ]);
    }
}
