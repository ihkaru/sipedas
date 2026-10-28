<?php

namespace App\Filament\Resources\PenugasanResource\Widgets;

use App\Exports\PenugasanDisetujuiExport;
use App\Filament\Resources\PenugasanResource;
use App\Models\Mitra;
use App\Models\Pegawai;
use App\Models\Penugasan;
use App\Supports\Constants;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class PenugasanDisetujuiTable extends BaseWidget
{
    protected static ?string $heading = 'Surat Tugas Disetujui';

    protected int|string|array $columnSpan = 'full';

    protected static $resource = PenugasanResource::class;

    public function canViewAny(): bool
    {
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
            ->with([
                'riwayatPengajuan',
                'pegawai',
                'mitra',
                'kegiatan',
                'suratTugas',
                'tujuanSuratTugas.provinsi',
                'tujuanSuratTugas.kabkot',
                'tujuanSuratTugas.kecamatan',
                'tujuanSuratTugas.desa',
                'satuSurat.riwayatPengajuan',
            ])
            ->orderBy('tgl_mulai_tugas', 'desc');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                $this->getTableQuery()->latest('penugasans.created_at')
            )
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([5, 10, 25])
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        $query = $this->getFilteredTableQuery();
                        $filename = 'Surat_Tugas_Disetujui_'.date('Y-m-d_His').'.xlsx';

                        return Excel::download(
                            new PenugasanDisetujuiExport($query),
                            $filename
                        );
                    }),
            ])
            ->columns([
                TextColumn::make('tertugas')
                    ->label('Tertugas')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->whereHas('pegawai', fn ($p) => $p->where('nama', 'like', "%{$search}%")->orWhere('nip', 'like', "%{$search}%"))
                                ->orWhereHas('mitra', fn ($m) => $m->where('nama_1', 'like', "%{$search}%")->orWhere('id_sobat', 'like', "%{$search}%"));
                        });
                    }),
                TextColumn::make('tujuan_penugasan')
                    ->label('Lokasi Penugasan'),
                TextColumn::make('kegiatan.nama')
                    ->label('Kegiatan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tgl_pengajuan_tugas')
                    ->date('Y-m-d')
                    ->sortable()
                    ->badge()
                    ->label('Tanggal Diajukan'),
                TextColumn::make('tgl_perjadin')
                    ->sortable()
                    ->badge()
                    ->label('Tanggal Perjadin'),
            ])
            ->filters([
                SelectFilter::make('pegawai')
                    ->label('Pegawai')
                    ->relationship('pegawai', 'nama', fn (Builder $query) => $query->orderBy('nama'))
                    ->getOptionLabelFromRecordUsing(fn (Pegawai $record): string => "{$record->nama} ({$record->nip})")
                    ->searchable(['nama', 'nip'])
                    ->multiple(),
                SelectFilter::make('mitra')
                    ->label('Mitra Statistik')
                    ->relationship('mitra', 'nama_1', fn (Builder $query) => $query->orderBy('nama_1'))
                    ->getOptionLabelFromRecordUsing(fn (Mitra $record): string => $record->id_sobat ? "{$record->nama_1} ({$record->id_sobat})" : (string) $record->nama_1)
                    ->searchable(['nama_1', 'id_sobat'])
                    ->multiple(),
                SelectFilter::make('kegiatan')
                    ->label('Nama Kegiatan')
                    ->relationship('kegiatan', 'nama', fn (Builder $query) => $query->orderBy('nama'))
                    ->searchable(['nama'])
                    ->multiple(),
                SelectFilter::make('jenis_petugas')
                    ->label('Jenis Petugas')
                    ->options([
                        'pegawai' => 'Pegawai Organik',
                        'mitra' => 'Mitra Statistik',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (($data['value'] ?? null) === 'pegawai') {
                            return $query->whereNotNull('penugasans.nip');
                        }
                        if (($data['value'] ?? null) === 'mitra') {
                            return $query->whereNotNull('penugasans.id_sobat');
                        }

                        return $query;
                    }),
                SelectFilter::make('tahun')
                    ->label('Tahun Tugas')
                    ->options(function () {
                        $years = Penugasan::selectRaw('DISTINCT YEAR(tgl_mulai_tugas) as year')
                            ->whereNotNull('tgl_mulai_tugas')
                            ->orderByDesc('year')
                            ->pluck('year', 'year')
                            ->toArray();

                        return $years ?: [now()->year => now()->year];
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['value'])) {
                            return $query->whereYear('penugasans.tgl_mulai_tugas', $data['value']);
                        }

                        return $query;
                    }),
                DateRangeFilter::make('tgl_mulai_tugas')
                    ->label('Rentang Tanggal Tugas'),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Action::make('pdf')
                    ->label('PDF')
                    ->color('success')
                    ->icon('fluentui-arrow-download-48')
                    ->url(function (Penugasan $record) {
                        $isBersama = $record->surat_tugas_id && (
                            $record->relationLoaded('satuSurat')
                                ? $record->satuSurat->filter(fn ($p) => in_array($p->riwayatPengajuan?->status, [
                                    Constants::STATUS_PENGAJUAN_DISETUJUI,
                                    Constants::STATUS_PENGAJUAN_DICETAK,
                                    Constants::STATUS_PENGAJUAN_DIKUMPULKAN,
                                    Constants::STATUS_PENGAJUAN_DICAIRKAN,
                                ]))->count() > 1
                                : $record->isSuratTugasBersama()
                        );

                        if ($isBersama) {
                            return route('cetak.penugasan-bersama', ['id' => $record->id]);
                        }

                        return route('cetak.penugasan', ['id' => $record->id]);
                    })
                    ->openUrlInNewTab(),
                Action::make('lihat')
                    ->modalHeading('Pengajuan Surat Tugas')
                    ->disabledForm()
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->mountUsing(function ($form, Penugasan $record) {
                        $pegawais = Pegawai::whereIn('nip', $record->satuSurat()->whereHas('riwayatPengajuan', function ($query) {
                            $query->where('status', Constants::STATUS_PENGAJUAN_DISETUJUI);
                        })->pluck('nip'));
                        $mitras = Mitra::whereIn('id_sobat', $record->satuSurat()->whereHas('riwayatPengajuan', function ($query) {
                            $query->where('status', Constants::STATUS_PENGAJUAN_DISETUJUI);
                        })->pluck('id_sobat'));
                        $form->fill([
                            ...$record->toArray(),
                            ...[
                                'nama_pegawai' => $pegawais->pluck('nip'),
                                'nama_mitra' => $mitras->pluck('id_sobat'),
                                'prov_ids' => $record->tujuanSuratTugas->pluck('prov_id'),
                                'kabkot_ids' => $record->tujuanSuratTugas->pluck('kabkot_id'),
                                'kecamatan_ids' => $record->tujuanSuratTugas->pluck('kecamatan_id'),
                                'desa_kel_ids' => $record->tujuanSuratTugas->pluck('desa_kel_id'),
                                'id' => $record->id,
                                'catatan_butuh_perbaikan' => $record->riwayatPengajuan?->catatan_butuh_perbaikan,
                            ],
                        ]);
                    })
                    ->form(self::$resource::formLihatPengajuan()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('export_selected')
                        ->label('Export Terpilih')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $filename = 'Surat_Tugas_Disetujui_Terpilih_'.date('Y-m-d_His').'.xlsx';

                            return Excel::download(
                                new PenugasanDisetujuiExport($records),
                                $filename
                            );
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
