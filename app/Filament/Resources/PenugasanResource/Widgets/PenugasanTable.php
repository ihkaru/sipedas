<?php

namespace App\Filament\Resources\PenugasanResource\Widgets;

use App\DTO\PenugasanCreation;
use App\Filament\Resources\PenugasanResource;
use App\Models\Kegiatan;
use App\Models\MasterSls;
use App\Models\Pegawai;
use App\Models\Penugasan;
use App\Supports\Constants;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
class PenugasanTable extends BaseWidget
{
    protected static ?string $heading = 'Pengajuan Surat Tugas';
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
                            ->label("Pegawai")
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
                            ->relationship('kegiatan','id')
                            ->searchDebounce(100)
                            ->getOptionLabelFromRecordUsing(fn(Kegiatan $record)=>$record->nama)
                            ->required()
                            ->searchable(['nama']),
                        DatePicker::make('tgl_mulai_tugas')
                            ->required()
                            ->label("Tanggal Mulai Penugasan"),
                        DatePicker::make('tgl_selesai_tugas')
                            ->required()
                            ->label("Tanggal Selesai Penugasan"),
                        TextInput::make("tbh_awal_perjalan")
                            ->required()
                            ->placeholder("Masukan tanggal")
                            ->numeric()
                            ->label("Tambah Hari Awal Perjalanan"),
                        TextInput::make("tbh_akhir_perjalan")
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
                        Select::make("kec_id")
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
    protected function getTableQuery(): Builder
    {
        return Penugasan::query()->orderBy('tgl_mulai_tugas','desc');
    }
    public function table(Table $table): Table
    {
        $table = PenugasanResource::table($table);
        return $table
            // ->headerActions(
            //     $this->getTableHeaderActions()
            // )
            ->query(
                $this->getTableQuery()->latest('created_at')
            )
            ->columns([
                TextColumn::make('tertugas'),
                TextColumn::make('jenisPetugas')
                    ->label("Pegawai/Mitra"),
                TextColumn::make('pengaju.nama')
                    ->sortable(),
                TextColumn::make('kegiatan.nama')
                    ->sortable(),
                TextColumn::make('tgl_perjadin')
                    ->badge()
                    ->label('Tanggal Perjadin'),
                TextColumn::make('riwayatPengajuan.last_status')
                    ->label("Status")
                    // ->color('danger')
                    ->color(function($state){
                        if($state == 'Dikirim') return 'primary';
                        if($state == 'Disetujui') return 'success';
                        if($state == 'Dicetak') return 'success';
                        if($state == 'Dicairkan') return 'success';
                        if($state == 'Dibatalkan') return 'danger';
                        if($state == 'Ditolak') return 'danger';
                        if($state == 'Perlu Revisi') return 'warning';
                    })
                    ->badge()
            ])

            ;

    }
}
