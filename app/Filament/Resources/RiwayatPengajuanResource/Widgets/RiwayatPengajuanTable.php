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
        $query = RiwayatPengajuan::query()->with('penugasan')->whereHas("penugasan",function($query){
            $query->whereHas('pegawai',function($query){$query->where('nip',auth()->user()->pegawai?->nip);
            });
        });
        return $table
            ->defaultSort('last_status_timestamp','desc')
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
                        if($state == 'Dibatalkan') return 'danger';
                        if($state == 'Ditolak') return 'danger';
                        if($state == 'Perlu Revisi') return 'warning';
                    })
                    ->badge()
                ,
                TextColumn::make('last_status_timestamp')
                    ->label('Tanggal Perubahan Status')
                ,
            ])
            ->actions([
                Action::make('buat_laporan')
                    ->label(fn (RiwayatPengajuan $record): string => $record->penugasan->laporanPerjadin()->exists() ? 'Lihat Laporan' : 'Buat Laporan')
                    ->icon(fn (RiwayatPengajuan $record): string => $record->penugasan->laporanPerjadin()->exists() ? 'heroicon-o-eye' : 'heroicon-o-document-plus')
                    ->color(fn (RiwayatPengajuan $record): string => $record->penugasan->laporanPerjadin()->exists() ? 'primary' : 'success')
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
