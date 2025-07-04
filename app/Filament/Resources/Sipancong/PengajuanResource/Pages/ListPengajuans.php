<?php

namespace App\Filament\Resources\Sipancong\PengajuanResource\Pages;

use App\Filament\Resources\Sipancong\PengajuanResource;
use App\Filament\Resources\Sipancong\PengajuanResource\Forms\PengajuanForms;
use App\Filament\Resources\Sipancong\PenugasanResource\Widgets\StatsProsesPembayaran;
use App\Models\Sipancong\Pengajuan;
use App\Services\Sipancong\PengajuanFixer;
use App\Services\Sipancong\PengajuanServices;
use App\Supports\SipancongConstants as Constants;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPengajuans extends ListRecords
{
    // trait HasResizableColumn tidak ada di core Filament,
    // pastikan kamu sudah menginstall package-nya dengan benar.
    // use HasResizableColumn;

    protected static string $resource = PengajuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
                ->hidden(fn(): bool => !auth()->user()->hasRole(["super_admin", "Admin", "operator_umum"]))
                ->action(function () {
                    // Pastikan class PengajuanFixer ada dan memiliki metode statis fix()
                    if (class_exists(PengajuanFixer::class) && method_exists(PengajuanFixer::class, 'fix')) {
                        PengajuanFixer::fix();
                        Notification::make()
                            ->success()
                            ->title("Perbaikan Konsistensi Pengajuan Selesai")
                            ->send();
                    } else {
                        Notification::make()
                            ->danger()
                            ->title("Aksi Gagal")
                            ->body("Class PengajuanFixer tidak ditemukan.")
                            ->send();
                    }
                })
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatsProsesPembayaran::class
        ];
    }

    public function getTabs(): array
    {
        $user = auth()->user();

        // Buat query dasar langsung dari Model. Ini aman dari masalah lifecycle.
        $baseQuery = Pengajuan::query();

        // Filter untuk Pengaju, hanya tampilkan miliknya kecuali untuk Admin
        $queryPengaju = ($user->hasRole(['Super Admin', 'super_admin', 'operator_umum']))
            ? PengajuanServices::rawPerluPerbaikanPengaju()
            : PengajuanServices::rawPerluPerbaikanPengaju() . " AND nip_pengaju = '{$user->pegawai?->nip}'";

        return [
            'Semua' => Tab::make(),
            'Perlu Perbaikan' => Tab::make("Perlu Perbaikan")
                ->modifyQueryUsing(fn(Builder $query) => $query->whereRaw($queryPengaju))
                ->badge(
                    // Clone query dasar dan terapkan kondisi untuk menghitung badge
                    (clone $baseQuery)->whereRaw($queryPengaju)->count()
                )
                ->badgeColor('warning'),

            'PPK' => Tab::make("PPK")
                ->modifyQueryUsing(fn(Builder $query) => $query->whereRaw(PengajuanServices::rawPerluPemeriksaanPpk()))
                ->badge(
                    (clone $baseQuery)->whereRaw(PengajuanServices::rawPerluPemeriksaanPpk())->count()
                )
                ->badgeColor('info'),
            // ->hidden(fn(): bool => !auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'ppk'])),

            'PPSPM' => Tab::make("PPSPM")
                ->modifyQueryUsing(fn(Builder $query) => $query->whereRaw(PengajuanServices::rawPerluPemeriksaanPpspm()))
                ->badge(
                    (clone $baseQuery)->whereRaw(PengajuanServices::rawPerluPemeriksaanPpspm())->count()
                )
                ->badgeColor('info'),
            // ->hidden(fn(): bool => !auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'ppspm'])),

            'Bendahara' => Tab::make("Bendahara")
                ->modifyQueryUsing(fn(Builder $query) => $query->whereRaw(PengajuanServices::rawPerluAksiBendahara()))
                ->badge(
                    (clone $baseQuery)->whereRaw(PengajuanServices::rawPerluAksiBendahara())->count()
                )
                ->badgeColor('primary'),
            // ->hidden(fn(): bool => !auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'bendahara'])),

            'Selesai' => Tab::make("Selesai")
                ->modifyQueryUsing(fn(Builder $query) => $query->where('posisi_dokumen_id', Constants::POSISI_SELESAI))
                ->badge(
                    (clone $baseQuery)->where('posisi_dokumen_id', Constants::POSISI_SELESAI)->count()
                ),
        ];
    }
}
