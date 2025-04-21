<?php

namespace App\Filament\Resources\Sipancong\PengajuanResource\Pages;

use App\Filament\Resources\Sipancong\PengajuanResource;
use App\Filament\Resources\Sipancong\PengajuanResource\Forms\PengajuanForms;
use App\Filament\Resources\Sipancong\PenugasanResource\Widgets\StatsProsesPembayaran;
use App\Models\Sipancong\Pengajuan;
use App\Models\Sipancong\PosisiDokumen;
use App\Models\Sipancong\Subfungsi;
use App\Services\Sipancong\PengajuanServices;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class ListPengajuans extends ListRecords
{
    protected static string $resource = PengajuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Action::make("ajukan")
                ->label("Ajukan Pembayaran")
                ->icon("heroicon-o-paper-airplane")
                ->modalHeading("Pengajuan Pembayaran")
                ->form(PengajuanForms::pengajuanPembayaran())
                ->action(function (array $data) {
                    PengajuanServices::ajukan($data);
                }),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return [
            StatsProsesPembayaran::class
        ];
    }
    public function getHeaderWidgetsColumns(): int|string|array
    {
        return 4;
    }

    public function getTabs(): array
    {
        return [
            'Semua' => Tab::make("Semua"),
            'Pengaju' => Tab::make("Pengaju")
                ->modifyQueryUsing(
                    function () {
                        // dd($this->getFilteredTableQuery()->clone());
                        return $this->whereRaw(PengajuanServices::rawPerluPemeriksaanPengaju());
                    }
                ),
            'PPK' => Tab::make("PPK")
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereRaw(PengajuanServices::rawPerluPemeriksaanPpk())

                ),
            'Bendahara' => Tab::make("Bendahara")
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query
                        ->whereRaw("(" . PengajuanServices::rawPerluPemeriksaanBendahara() . ") OR (" . PengajuanServices::rawPerluProsesBendahara() . ")")
                ),
            'PPSPM' => Tab::make("PPSPM")
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereRaw(PengajuanServices::rawPerluPemeriksaanPpspm())
                ),
            'Selesai' => Tab::make("Selesai")
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->orWhere('status_pembayaran_id', 1)
                        ->orWhere('status_pembayaran_id', 2)
                        ->orWhere('status_pembayaran_id', 5)
                        ->orWhere('status_pembayaran_id', 6)
                        ->orWhere('status_pembayaran_id', 7)
                ),
        ];
    }
}
