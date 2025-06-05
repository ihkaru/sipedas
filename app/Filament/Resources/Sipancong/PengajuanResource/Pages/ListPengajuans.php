<?php

namespace App\Filament\Resources\Sipancong\PengajuanResource\Pages;

use App\Filament\Resources\Sipancong\PengajuanResource;
use App\Filament\Resources\Sipancong\PengajuanResource\Forms\PengajuanForms;
use App\Filament\Resources\Sipancong\PenugasanResource\Widgets\StatsProsesPembayaran;
use App\Models\Sipancong\Pengajuan;
use App\Services\Sipancong\PengajuanFixer;
use App\Services\Sipancong\PengajuanServices;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListPengajuans extends ListRecords
{
    use HasResizableColumn;
    protected static string $resource = PengajuanResource::class;
    public function table(Table $table): Table
    {
        return self::$resource::table($table)
            // ->recordClasses("border-green-600")
            ->modifyQueryUsing(fn(Builder $query) => $query->orderBy("created_at", "asc"));
    }
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
            Action::make("fix")
                ->requiresConfirmation()
                ->label("Perbaiki Konsistensi")
                ->icon("heroicon-o-cog-6-tooth")
                ->hidden(function () {
                    return !auth()->user()->hasRole("operator_umum");
                })
                ->action(function () {
                    PengajuanFixer::fix();
                    Notification::make()
                        ->success()
                        ->title("Perbaikan Konsistensi Pengajuan Selesai")
                        ->send();
                })
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
                    function (Builder $query) {
                        // dd($this->getFilteredTableQuery()->clone());
                        return $query->orderBy("updated_at", "desc")->whereRaw(PengajuanServices::rawPerluPemeriksaanPengaju());
                    }
                ),
            'PPK' => Tab::make("PPK")
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->orderBy("updated_at", "desc")->whereRaw(PengajuanServices::rawPerluPemeriksaanPpk())
                ),
            'PPSPM' => Tab::make("PPSPM")
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->orderBy("updated_at", "desc")->whereRaw(PengajuanServices::rawPerluPemeriksaanPpspm())
                ),
            'Bendahara' => Tab::make("Bendahara")
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query
                        ->orderBy("updated_at", "desc")->whereRaw("(" . PengajuanServices::rawPerluPemeriksaanBendahara() . ") OR (" . PengajuanServices::rawPerluProsesBendahara() . ")")
                ),

            'Selesai' => Tab::make("Selesai")
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->orWhere('status_pembayaran_id', 1)
                        ->orWhere('status_pembayaran_id', 2)
                        ->orWhere('status_pembayaran_id', 5)
                        ->orWhere('status_pembayaran_id', 6)
                        ->orWhere('status_pembayaran_id', 7)
                        ->orderBy('updated_at', 'desc')
                ),
        ];
    }
}
