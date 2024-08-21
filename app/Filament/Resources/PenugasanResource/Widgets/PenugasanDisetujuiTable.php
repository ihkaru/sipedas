<?php

namespace App\Filament\Resources\PenugasanResource\Widgets;

use App\Filament\Resources\PenugasanResource;
use App\Models\Kegiatan;
use App\Models\MasterSls;
use App\Models\Mitra;
use App\Models\Pegawai;
use App\Models\Penugasan;
use App\Supports\Constants;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PenugasanDisetujuiTable extends BaseWidget
{
    protected static ?string $heading = 'Surat Tugas Disetujui';
    protected int | string | array $columnSpan = 'full';
    protected static $resource = PenugasanResource::class;


    public function canViewAny(): bool {
        return true;
    }

    protected function getTableQuery(): Builder
    {
        return Penugasan::query()
            ->whereHas('riwayatPengajuan',function($query){
                $query->where('status',Constants::STATUS_PENGAJUAN_DISETUJUI);
                $query->orWhere('status',Constants::STATUS_PENGAJUAN_DICETAK);
                $query->orWhere('status',Constants::STATUS_PENGAJUAN_DIKUMPULKAN);
                $query->orWhere('status',Constants::STATUS_PENGAJUAN_DICAIRKAN);
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
                TextColumn::make('tertugas')
                    ->label("Tertugas"),
                TextColumn::make('tujuan_penugasan')
                    ->label("Lokasi Penugasan"),
                TextColumn::make('kegiatan.nama')
                    ->sortable(),
                TextColumn::make('tgl_pengajuan_tugas')
                    ->date("Y-m-d")
                    ->sortable()
                    ->badge()
                    ->label("Tanggal pDiajukan"),
                TextColumn::make('tgl_perjadin')
                    ->sortable()
                    ->badge()
                    ->label('Tanggal Perjadin'),
            ])
            ->actions([
                Action::make('pdf')
                    ->label('PDF')
                    ->color('success')
                    ->icon('fluentui-arrow-download-48')
                    ->action(function (Penugasan $record) {
                        $record->cetak();
                        if($record->suratTugasBersamaDisetujui()->count()>1){
                            return redirect()->to(route("cetak.penugasan-bersama",['id'=>$record->id]));
                        }
                        return redirect()->to(route("cetak.penugasan",['id'=>$record->id]));
                    }),
                Action::make("lihat")
                    ->modalHeading('Pengajuan Surat Tugas')
                    ->disabledForm()
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel("Close")
                    ->mountUsing(function (Form $form,Penugasan $record){
                        // dd($record->jenis_peserta);
                        $pegawais = Pegawai::whereIn('nip',Penugasan::find($record->id)->satuSurat()->whereHas('riwayatPengajuan',function($query){$query->where('status',Constants::STATUS_PENGAJUAN_DISETUJUI);})->pluck('nip'));
                        $mitras = Mitra::whereIn('id_sobat',Penugasan::find($record->id)->satuSurat()->whereHas('riwayatPengajuan',function($query){$query->where('status',Constants::STATUS_PENGAJUAN_DISETUJUI);})->pluck('id_sobat'));
                        $form->fill([
                            ...$record->toArray(),
                            ...[
                                "nama_pegawai"=>$pegawais->pluck('nip'),
                                "nama_mitra"=>$mitras->pluck('id_sobat'),
                                "prov_ids"=>$record->tujuanSuratTugas->pluck('prov_id'),
                                "kabkot_ids"=>$record->tujuanSuratTugas->pluck('kabkot_id'),
                                "kecamatan_ids"=>$record->tujuanSuratTugas->pluck('kecamatan_id'),
                                "desa_kel_ids"=>$record->tujuanSuratTugas->pluck('desa_kel_id'),
                                "id"=>$record->id,
                                // "jenis_peserta"=>$record->jenis_peserta,
                                "catatan_butuh_perbaikan"=>$record->riwayatPengajuan->catatan_butuh_perbaikan
                                ]
                        ]);
                    })
                    ->form(self::$resource::formLihatPengajuan())
                ,
            ])

            ;

    }
}
