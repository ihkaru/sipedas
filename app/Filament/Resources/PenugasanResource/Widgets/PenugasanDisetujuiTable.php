<?php

namespace App\Filament\Resources\PenugasanResource\Widgets;

use App\DTO\PenugasanCreation;
use App\Filament\Resources\PenugasanResource;
use App\Models\Kegiatan;
use App\Models\MasterSls;
use App\Models\Pegawai;
use App\Models\Penugasan;
use App\Models\RiwayatPengajuan;
use App\Supports\Constants;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Blade;

class PenugasanDisetujuiTable extends BaseWidget
{
    protected static ?string $heading = 'Surat Tugas Disetujui';
    protected int | string | array $columnSpan = 'full';

    public function canViewAny(): bool {
        return true;
    }

    protected function getTableQuery(): Builder
    {
        return Penugasan::query()
            ->whereHas('riwayatPengajuan',function($query){
                $query->where('status',Constants::STATUS_PENGAJUAN_DISETUJUI);
            })
            ->orderBy('tgl_mulai_tugas','desc');
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
                TextColumn::make('tujuan_penugasan')
                    ->label("Lokasi Penugasan"),
                TextColumn::make('kegiatan.nama')
                    ->sortable(),
                TextColumn::make('tgl_perjadin')
                    ->badge()
                    ->label('Tanggal Perjadin'),
            ])
            ->actions([
                Action::make('pdf')
                    ->label('PDF')
                    ->color('success')
                    ->icon('fluentui-arrow-download-48')
                    ->action(function (Penugasan $record) {
                        // dd($record);
                        return redirect()->to(route("cetak.penugasan",['id'=>$record->id]));
                    }),
                Action::make("lihat")
                    ->modalHeading('Pengajuan Surat Tugas')
                    ->disabledForm()
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel("Close")
                    ->mountUsing(function (Form $form,Penugasan $record){
                        $pegawais = Pegawai::whereIn('nip',Penugasan::find($record->id)->satuSurat()->whereHas('riwayatPengajuan',function($query){$query->where('status',Constants::STATUS_PENGAJUAN_DISETUJUI);})->pluck('nip'));
                        $form->fill([
                            ...$record->toArray(),
                            ...[
                                "nama_pegawai"=>$pegawais->pluck('nip'),
                                "nip_pegawai"=>$pegawais->pluck('nip'),
                                "id"=>$record->id,
                                "catatan_butuh_perbaikan"=>$record->riwayatPengajuan->catatan_butuh_perbaikan
                                ]
                        ]);
                    })
                    ->form([
                        Hidden::make("id"),
                        Select::make("jenis_surat_tugas")
                            ->options(
                                Constants::JENIS_SURAT_TUGAS_OPTIONS
                            )
                            ->searchable()
                            ->disabled()
                            ,
                        Select::make("nama_pegawai")
                            ->label("Pegawai")
                            ->relationship('satuSurat.pegawai','nip')
                            ->getOptionLabelFromRecordUsing(fn(Pegawai $record)=>$record->nama)
                            ->searchable(['nama'])
                            ->multiple()
                            ->disabled(),
                        Select::make("nip_pegawai")
                            ->label("NIP Pegawai")
                            ->relationship('satuSurat.pegawai','nip')
                            ->getOptionLabelFromRecordUsing(fn(Pegawai $record)=>$record->nip)
                            ->searchable(['nip'])
                            ->multiple()
                            ->disabled(),
                        Select::make("plh_id")
                            ->label("Penyetuju")
                            ->relationship('plh','nip')
                            ->getOptionLabelFromRecordUsing(fn(Pegawai $record)=>$record->nama)
                            ->searchable(['nip'])
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
                ,
            ])

            ;

    }
}
