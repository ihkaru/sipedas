<?php

namespace App\Filament\Resources\RiwayatPengajuanResource\Widgets;

use App\Filament\Resources\PenugasanResource;

use App\Models\Penugasan;
use App\Models\RiwayatPengajuan;
use Filament\Actions\StaticAction;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
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
                    ->form(self::$resource::formPengajuan())
                    ->modalDescription(fn()=>new HtmlString("<span style='color:black;'> <b>Setelah disetujui</b>, pegawai silakan <b>mencetak sendiri</b> surat tugas dan <b>diserahkan ke TU</b> untuk ditandatangani </span>"))
                    ->action(function (array $data, array $arguments, Action $action,Set $set):void {
                        if (Penugasan::ajukan($data)) {
                            Notification::make()
                                ->title('Pengajuan berhasil dikirim')
                                ->success()
                                ->send();
                        }

                        // if ($arguments['another'] ?? false) {
                        //     $action->fillForm([
                        //         'mitras' => null,
                        //         'nips' => null,
                        //         'nama_tempat_tujuan' => null,
                        //         'kecamatan_ids' => null,
                        //         'desa_kel_ids' => null,
                        //     ]);
                        //     // Emit a Livewire event to reset specific fields
                        //     $action->dispatch('reset-fields', [
                        //         'mitras', 'nips', 'nama_tempat_tujuan', 'kecamatan_ids', 'desa_kel_ids'
                        //     ]);
                        //     $action->halt();
                        // } else {
                        //     $action->close();
                        // }
                    })
                    // ->extraModalFooterActions(fn (Action $action): array => [
                    //     $action->makeModalSubmitAction('createAnother', arguments: ['another' => true]),
                    // ])
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
            ]);
    }
}
