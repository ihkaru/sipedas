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
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
class PenugasanTable extends BaseWidget
{
    protected static ?string $heading = 'Pengajuan Surat Tugas';
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
                ->modalDescription(fn() => new HtmlString("<span style='color:black;'> <b>Setelah disetujui</b>, pegawai silakan <b>mencetak sendiri</b> surat tugas dan <b>diserahkan ke TU</b> untuk ditandatangani </span>"))
                ->action(function (array $data): void {
                    if (Penugasan::ajukan($data)) {
                        Notification::make()
                            ->title('Pengajuan berhasil dikirim')
                            ->success()
                            ->send();
                    }
                }),
        ];
    }
    protected function getTableQuery(): Builder
    {
        return Penugasan::query()
            ->with([
                'riwayatPengajuan',
                'pegawai',
                'mitra',
                'pengaju',
                'kegiatan',
                'tujuanSuratTugas',
                'suratTugas',
                'plh',
            ])
            ->orderBy('tgl_mulai_tugas', 'desc');
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
                    ->label("Tertugas")
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->whereHas('pegawai', fn($p) => $p->where('nama', 'like', "%{$search}%"))
                              ->orWhereHas('mitra', fn($m) => $m->where('nama_1', 'like', "%{$search}%"));
                        });
                    }),
                TextColumn::make('jenisPetugas')
                    ->label("Pegawai/Mitra"),
                TextColumn::make('pengaju.nama')
                    ->label("Pengaju")
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kegiatan.nama')
                    ->label("Kegiatan")
                    ->searchable()
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
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([5, 10, 25]);

    }
}
