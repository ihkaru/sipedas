<?php

namespace App\Filament\Resources\PenugasanResource\Widgets;

use App\Exports\PenugasanDisetujuiExport;
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
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

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
            ->whereHas('riwayatPengajuan', function ($query) {
                $query->whereIn('status', [
                    Constants::STATUS_PENGAJUAN_DISETUJUI,
                    Constants::STATUS_PENGAJUAN_DICETAK,
                    Constants::STATUS_PENGAJUAN_DIKUMPULKAN,
                    Constants::STATUS_PENGAJUAN_DICAIRKAN,
                ]);
            })
            ->with(['riwayatPengajuan', 'pegawai', 'mitra', 'kegiatan', 'suratTugas'])
            ->orderBy('tgl_mulai_tugas', 'desc');
    }
    public function table(Table $table): Table
    {
        $table = PenugasanResource::table($table);
        return $table
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        $query = $this->getFilteredTableQuery();
                        $filename = 'Surat_Tugas_Disetujui_' . date('Y-m-d_His') . '.xlsx';
                        return Excel::download(
                            new PenugasanDisetujuiExport($query),
                            $filename
                        );
                    }),
            ])
            ->query(
                $this->getTableQuery()->latest('penugasans.created_at')
            )
            ->columns([
                TextColumn::make('tertugas')
                    ->label("Tertugas")
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->whereHas('pegawai', fn($p) => $p->where('nama', 'like', "%{$search}%"))
                              ->orWhereHas('mitra', fn($m) => $m->where('nama_1', 'like', "%{$search}%"));
                        });
                    }),
                TextColumn::make('tujuan_penugasan')
                    ->label("Lokasi Penugasan"),
                TextColumn::make('kegiatan.nama')
                    ->label("Kegiatan")
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tgl_pengajuan_tugas')
                    ->date("Y-m-d")
                    ->sortable()
                    ->badge()
                    ->label("Tanggal Diajukan"),
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
                    ->url(function (Penugasan $record) {
                        $record->cetak();
                        if($record->suratTugasBersamaDisetujui()->count()>1){
                            return route("cetak.penugasan-bersama",['id'=>$record->id]);
                        }
                        return route("cetak.penugasan",['id'=>$record->id]);
                    })
                    ->openUrlInNewTab(),
                Action::make("lihat")
                    ->modalHeading('Pengajuan Surat Tugas')
                    ->disabledForm()
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel("Close")
                    ->mountUsing(function ($form, Penugasan $record){
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
                                "catatan_butuh_perbaikan"=>$record->riwayatPengajuan?->catatan_butuh_perbaikan
                                ]
                        ]);
                    })
                    ->form(self::$resource::formLihatPengajuan())
                ,
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('export_selected')
                        ->label('Export Terpilih')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $filename = 'Surat_Tugas_Disetujui_Terpilih_' . date('Y-m-d_His') . '.xlsx';
                            return Excel::download(
                                new PenugasanDisetujuiExport($records),
                                $filename
                            );
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);

            ;

    }
}
