<?php

namespace App\Filament\Resources\RiwayatPengajuanResource\Widgets;

use App\Filament\Resources\PenugasanResource;

use App\Models\Penugasan;
use App\Models\RiwayatPengajuan;
use App\Supports\Constants;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class RiwayatPengajuanTable extends BaseWidget
{
    protected static ?string $heading = 'Riwayat Surat Tugas Anda';
    protected int | string | array $columnSpan = 'full';
    protected static $resource = PenugasanResource::class;

    protected function getTableHeaderActions(): array
    {
        return [
            Action::make("tambah_pengajuan")
                    ->closeModalByClickingAway(false)
                    ->label("Pengajuan Surat Tugas")
                    ->icon("fluentui-document-add-20-o")
                    ->form(PenugasanResource::formPengajuan())
                    ->modalDescription(fn()=>new HtmlString("<span style='color:black;'> <b>Setelah disetujui</b>, pegawai silakan <b>mencetak sendiri</b> surat tugas dan <b>diserahkan ke TU</b> untuk ditandatangani </span>"))
                    ->action(function (array $data): void {
                        if (Penugasan::ajukan($data)) {
                            Notification::make()
                                ->title('Pengajuan berhasil dikirim')
                                ->success()
                                ->send();
                        }
                    }),
            Action::make("buat_laporan_header")
                    ->label("Pengajuan Laporan Perjadin")
                    ->icon("heroicon-o-document-plus")
                    ->color("success")
                    ->url(fn (): string => \App\Filament\Pages\LaporanPerjadinPage::getUrl()),
        ];
    }

    public function table(Table $table): Table
    {
        $query = RiwayatPengajuan::query()
            ->with([
                'penugasan.kegiatan',
                'penugasan.suratTugas',
                'penugasan.suratPerjadin',
                'penugasan.tujuanSuratTugas',
                'penugasan.provinsi',
                'penugasan.kabkot',
                'penugasan.kecamatan',
                'penugasan.desa',
                'penugasan.pegawai',
            ])
            ->whereHas("penugasan", function ($query) {
                $query->whereHas('pegawai', function ($query) {
                    $query->where('nip', auth()->user()->pegawai?->nip);
                });
            });

        return $table
            ->defaultSort('last_status_timestamp', 'desc')
            ->searchPlaceholder('Cari no. ST, kegiatan, lokasi, tanggal, status...')
            ->searchDebounce('500ms')
            ->headerActions(
                $this->getTableHeaderActions()
            )
            ->query(
                $query
            )
            ->columns([
                TextColumn::make('penugasan.suratTugas.nomor_surat_tugas')
                    ->label('No. Surat Tugas')
                    ->state(function (RiwayatPengajuan $record): string {
                        $st = $record->penugasan?->suratTugas;
                        if (!$st) {
                            return $record->penugasan?->surat_tugas_id ? "ID: {$record->penugasan->surat_tugas_id}" : '-';
                        }
                        return $st->nomor_surat_tugas ?? '-';
                    })
                    ->description(function (RiwayatPengajuan $record): ?string {
                        $spd = $record->penugasan?->suratPerjadin?->nomor_surat_perjadin;
                        return $spd ? "SPD: {$spd}" : null;
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $cleanNum = ltrim(preg_replace('/[^0-9]/', '', $search), '0');
                        return $query->whereHas('penugasan', function ($q) use ($search, $cleanNum) {
                            $q->whereHas('suratTugas', function ($st) use ($search, $cleanNum) {
                                $st->where('nomor', 'like', "%{$search}%")
                                   ->orWhere('sub_nomor', 'like', "%{$search}%")
                                   ->orWhere('tahun', 'like', "%{$search}%");
                                if (!empty($cleanNum)) {
                                    $st->orWhere('nomor', $cleanNum);
                                }
                            })->orWhereHas('suratPerjadin', function ($spd) use ($search, $cleanNum) {
                                $spd->where('nomor', 'like', "%{$search}%")
                                    ->orWhere('sub_nomor', 'like', "%{$search}%")
                                    ->orWhere('tahun', 'like', "%{$search}%");
                                if (!empty($cleanNum)) {
                                    $spd->orWhere('nomor', $cleanNum);
                                }
                            });
                        });
                    }),
                TextColumn::make("penugasan.kegiatan.nama")
                    ->label('Kegiatan')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('penugasan', function ($q) use ($search) {
                            $q->whereHas('kegiatan', function ($q2) use ($search) {
                                $q2->where('nama', 'like', "%{$search}%");
                            });
                        });
                    }),
                TextColumn::make("penugasan.tujuan_penugasan")
                    ->label('Lokasi Penugasan')
                    ->state(function (RiwayatPengajuan $record): string {
                        return $record->penugasan?->tujuan_penugasan ?? '-';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('penugasan', function ($q) use ($search) {
                            $q->whereHas('tujuanSuratTugas', function ($t) use ($search) {
                                $t->where('nama_tempat_tujuan', 'like', "%{$search}%");
                            })->orWhereHas('provinsi', function ($p) use ($search) {
                                $p->where('provinsi', 'like', "%{$search}%");
                            })->orWhereHas('kabkot', function ($k) use ($search) {
                                $k->where('kabkot', 'like', "%{$search}%");
                            })->orWhereHas('kecamatan', function ($kc) use ($search) {
                                $kc->where('kecamatan', 'like', "%{$search}%");
                            })->orWhereHas('desa', function ($d) use ($search) {
                                $d->where('desa_kel', 'like', "%{$search}%");
                            });
                        });
                    })
                    ->toggleable(),
                TextColumn::make("tgl_perjadin")
                    ->label('Tanggal Perjadin')
                    ->badge()
                    ->state(function (RiwayatPengajuan $record){
                        return $record->penugasan?->tgl_perjadin;
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('penugasan', function ($q) use ($search) {
                            $q->where('tgl_mulai_tugas', 'like', "%{$search}%")
                              ->orWhere('tgl_akhir_tugas', 'like', "%{$search}%");
                        });
                    })
                ,
                TextColumn::make('last_status')
                    ->label("Status")
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
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $matchingStatusKeys = collect(Constants::STATUS_PENGAJUAN_OPTIONS)
                            ->filter(fn ($label, $key) => 
                                str_contains(strtolower($label), strtolower($search)) || 
                                str_contains(strtolower($key), strtolower($search))
                            )
                            ->keys()
                            ->toArray();

                        if (empty($matchingStatusKeys)) {
                            return $query->where('status', 'like', "%{$search}%");
                        }

                        return $query->whereIn('status', $matchingStatusKeys);
                    })
                ,
                TextColumn::make('last_status_timestamp')
                    ->label('Tanggal Perubahan Status')
                    ->searchable()
                    ->sortable()
                ,
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(Constants::STATUS_PENGAJUAN_OPTIONS),
            ])
            ->actions([
                Action::make('buat_laporan')
                    ->label(fn (RiwayatPengajuan $record): string => $record->penugasan?->laporanPerjadin()->exists() ? 'Lihat Laporan' : 'Buat Laporan')
                    ->icon(fn (RiwayatPengajuan $record): string => $record->penugasan?->laporanPerjadin()->exists() ? 'heroicon-o-eye' : 'heroicon-o-document-plus')
                    ->color(fn (RiwayatPengajuan $record): string => $record->penugasan?->laporanPerjadin()->exists() ? 'primary' : 'success')
                    ->visible(fn (RiwayatPengajuan $record): bool => 
                        in_array($record->status, [
                            Constants::STATUS_PENGAJUAN_DISETUJUI,
                            Constants::STATUS_PENGAJUAN_DICETAK,
                            Constants::STATUS_PENGAJUAN_DIKUMPULKAN,
                            Constants::STATUS_PENGAJUAN_DICAIRKAN,
                        ])
                    )
                    ->url(fn (RiwayatPengajuan $record): string => \App\Filament\Pages\LaporanPerjadinPage::getUrl(['penugasanId' => $record->penugasan_id]))
            ]);
    }
}
