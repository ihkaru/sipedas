<?php

namespace App\Filament\Resources\Sipancong\PengajuanResource\Pages;

use App\Filament\Resources\Sipancong\PengajuanResource;
use App\Filament\Resources\Sipancong\PengajuanResource\Forms\PengajuanForms;
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
    public function getTabs(): array
    {
        return [
            'Semua' => Tab::make(),
            'Pengaju' => Tab::make()
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->orWhere('posisi_dokumen_id', 1)
                        ->orWhere('status_pengajuan_ppk_id', 1)
                        ->orWhere('status_pengajuan_ppk_id', 3)
                        ->orWhere('status_pengajuan_ppk_id', 4)
                        ->orWhere('status_pengajuan_bendahara_id', 1)
                        ->orWhere('status_pengajuan_bendahara_id', 3)
                        ->orWhere('status_pengajuan_bendahara_id', 4)
                        ->orWhere('status_pengajuan_ppspm_id', 1)
                        ->orWhere('status_pengajuan_ppspm_id', 3)
                        ->orWhere('status_pengajuan_ppspm_id', 4)
                ),
            'PPK' => Tab::make()
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->orWhere('posisi_dokumen_id', 2)
                        ->orWhere('status_pengajuan_ppk_id', 1)
                        ->orWhere('status_pengajuan_ppk_id', 3)
                        ->orWhere('status_pengajuan_ppk_id', 4)

                ),
            'Bendahara' => Tab::make()
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query
                        ->orWhere('status_pengajuan_bendahara_id', 1)
                        ->orWhere('status_pengajuan_bendahara_id', 3)
                        ->orWhere('status_pengajuan_bendahara_id', 4)
                        ->orWhere('status_pembayaran_id', 3)
                        ->orWhere('status_pembayaran_id', 4)
                        ->orWhere('status_pembayaran_id', 6)

                ),
            'PPSPM' => Tab::make()
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->orWhere('posisi_dokumen_id', 3)
                        ->orWhere('status_pengajuan_ppspm_id', 1)
                        ->orWhere('status_pengajuan_ppspm_id', 3)
                        ->orWhere('status_pengajuan_ppspm_id', 4)
                ),
        ];
    }
}
