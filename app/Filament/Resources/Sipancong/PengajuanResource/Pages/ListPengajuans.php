<?php

namespace App\Filament\Resources\Sipancong\PengajuanResource\Pages;

use App\Exports\PengajuanExport;
use App\Filament\Resources\Sipancong\PengajuanResource;
use App\Filament\Resources\Sipancong\PengajuanResource\Forms\PengajuanForms;
use App\Filament\Resources\Sipancong\PenugasanResource\Widgets\StatsProsesPembayaran;
use App\Models\Sipancong\Pengajuan;

use App\Services\Sipancong\PengajuanServices;
use App\Supports\SipancongConstants as Constants;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ListPengajuans extends ListRecords {
    // trait HasResizableColumn tidak ada di core Filament,
    // pastikan kamu sudah menginstall package-nya dengan benar.
    // use HasResizableColumn;

    protected static string $resource = PengajuanResource::class;

    protected function getHeaderActions(): array {
        return [
            Action::make("ajukan")
                ->label("Ajukan Pembayaran")
                ->icon("heroicon-o-paper-airplane")
                ->modalHeading("Pengajuan Pembayaran")
                ->form(PengajuanForms::pengajuanPembayaran())
                ->action(function (array $data) {
                    PengajuanServices::ajukan($data);
                }),

            Action::make("export")
                ->label("Export Excel")
                ->icon("heroicon-o-arrow-down-tray")
                ->color("success")
                ->action(function () {
                    // Ambil query dengan semua filter/tab yang sudah diterapkan
                    $query = $this->getFilteredTableQuery();

                    $filename = 'pengajuan_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

                    Notification::make()
                        ->success()
                        ->title("Export Berhasil")
                        ->body("File {$filename} sedang diunduh...")
                        ->send();

                    return Excel::download(
                        new PengajuanExport($query),
                        $filename
                    );
                }),



            // Dropdown untuk Terima Massal
            ActionGroup::make([
                Action::make("bulk_approve_ppk")
                    ->label(fn() => "Terima Semua PPK (" . PengajuanServices::countPendingForRole('ppk') . ")")
                    ->icon("heroicon-o-check-circle")
                    ->color("warning")
                    ->form([
                        Select::make('tahun')
                            ->label('Tahun Pengajuan')
                            ->options(
                                collect(range(now()->year - 2, now()->year + 1))
                                    ->mapWithKeys(fn($year) => [$year => $year])
                            )
                            ->default(now()->year)
                            ->required(),
                    ])
                    ->modalHeading("Terima Semua Pengajuan PPK")
                    ->modalDescription(fn() => "Anda akan menyetujui " . PengajuanServices::countPendingForRole('ppk') . " pengajuan (Total Pending) yang menunggu aksi PPK. Pilih tahun untuk memfilter data yang akan diproses.")
                    ->modalSubmitActionLabel("Ya, Terima Semua")
                    ->hidden(fn(): bool => !auth()->user()->hasAnyRole(["super_admin", "Admin", "ppk"]))
                    ->action(function (array $data) {
                        $count = PengajuanServices::bulkApprovePpk($data['tahun']);
                        Notification::make()
                            ->success()
                            ->title("Berhasil Menyetujui {$count} Pengajuan")
                            ->body("Semua pengajuan tahun {$data['tahun']} telah dipindahkan ke PPSPM.")
                            ->send();
                    }),

                Action::make("bulk_approve_revisi_ppk")
                    ->label(fn() => "Terima Semua Revisi PPK (" . PengajuanServices::countPendingRevisiPpk() . ")")
                    ->icon("heroicon-o-arrow-path")
                    ->color("danger") // Warna merah/danger untuk membedakan
                    ->form([
                        Select::make('tahun')
                            ->label('Tahun Pengajuan')
                            ->options(
                                collect(range(now()->year - 2, now()->year + 1))
                                    ->mapWithKeys(fn($year) => [$year => $year])
                            )
                            ->default(now()->year)
                            ->required(),
                    ])
                    ->modalHeading("Terima Semua Revisi PPK (Bypass)")
                    ->modalDescription(fn() => "Anda akan menarik paksa " . PengajuanServices::countPendingRevisiPpk() . " pengajuan yang statusnya 'Perlu Perbaikan' (Ditolak) dari meja Pengaju. Pengajuan akan langsung disetujui tanpa menunggu pengaju mengirim ulang. Lanjutkan?")
                    ->modalSubmitActionLabel("Ya, Terima Paksa")
                    ->hidden(fn(): bool => !auth()->user()->hasAnyRole(["super_admin", "Admin", "ppk"]))
                    ->action(function (array $data) {
                        $count = PengajuanServices::bulkApproveRevisiPpk($data['tahun']);
                        Notification::make()
                            ->success()
                            ->title("Berhasil Menyetujui {$count} Pengajuan Revisi")
                            ->body("Semua pengajuan revisi tahun {$data['tahun']} telah ditarik dan dipindahkan ke PPSPM.")
                            ->send();
                    }),

                Action::make("bulk_approve_ppspm")
                    ->label(fn() => "Terima Semua PPSPM (" . PengajuanServices::countPendingForRole('ppspm') . ")")
                    ->icon("heroicon-o-check-circle")
                    ->color("info")
                    ->form([
                        Select::make('tahun')
                            ->label('Tahun Pengajuan')
                            ->options(
                                collect(range(now()->year - 2, now()->year + 1))
                                    ->mapWithKeys(fn($year) => [$year => $year])
                            )
                            ->default(now()->year)
                            ->required(),
                    ])
                    ->modalHeading("Terima Semua Pengajuan PPSPM")
                    ->modalDescription(fn() => "Anda akan menyetujui " . PengajuanServices::countPendingForRole('ppspm') . " pengajuan (Total Pending) yang menunggu aksi PPSPM. Pilih tahun untuk memfilter data yang akan diproses.")
                    ->modalSubmitActionLabel("Ya, Terima Semua")
                    ->hidden(fn(): bool => !auth()->user()->hasAnyRole(["super_admin", "Admin", "ppspm"]))
                    ->action(function (array $data) {
                        $count = PengajuanServices::bulkApprovePpspm($data['tahun']);
                        Notification::make()
                            ->success()
                            ->title("Berhasil Menyetujui {$count} Pengajuan")
                            ->body("Semua pengajuan tahun {$data['tahun']} telah dipindahkan ke Bendahara.")
                            ->send();
                    }),

                Action::make("bulk_approve_revisi_ppspm")
                    ->label(fn() => "Terima Semua Revisi PPSPM (" . PengajuanServices::countPendingRevisiPpspm() . ")")
                    ->icon("heroicon-o-arrow-path")
                    ->color("danger") // Warna merah
                    ->form([
                        Select::make('tahun')
                            ->label('Tahun Pengajuan')
                            ->options(
                                collect(range(now()->year - 2, now()->year + 1))
                                    ->mapWithKeys(fn($year) => [$year => $year])
                            )
                            ->default(now()->year)
                            ->required(),
                    ])
                    ->modalHeading("Terima Semua Revisi PPSPM (Bypass)")
                    ->modalDescription(fn() => "Anda akan menarik paksa " . PengajuanServices::countPendingRevisiPpspm() . " pengajuan yang statusnya 'Perlu Perbaikan' (Ditolak) dari meja Pengaju. Pengajuan akan langsung disetujui tanpa menunggu pengaju mengirim ulang. Lanjutkan?")
                    ->modalSubmitActionLabel("Ya, Terima Paksa")
                    ->hidden(fn(): bool => !auth()->user()->hasAnyRole(["super_admin", "Admin", "ppspm"]))
                    ->action(function (array $data) {
                        $count = PengajuanServices::bulkApproveRevisiPpspm($data['tahun']);
                        Notification::make()
                            ->success()
                            ->title("Berhasil Menyetujui {$count} Pengajuan Revisi")
                            ->body("Semua pengajuan revisi tahun {$data['tahun']} telah ditarik dan dipindahkan ke Bendahara.")
                            ->send();
                    }),

                Action::make("bulk_approve_bendahara")
                    ->label(fn() => "Terima Semua Bendahara (" . PengajuanServices::countPendingForRole('bendahara') . ")")
                    ->icon("heroicon-o-check-circle")
                    ->color("primary")
                    ->form([
                        Select::make('tahun')
                            ->label('Tahun Pengajuan')
                            ->options(
                                collect(range(now()->year - 2, now()->year + 1))
                                    ->mapWithKeys(fn($year) => [$year => $year])
                            )
                            ->default(now()->year)
                            ->required(),
                    ])
                    ->modalHeading("Terima Semua Verifikasi Bendahara")
                    ->modalDescription(fn() => "Anda akan menyetujui " . PengajuanServices::countPendingForRole('bendahara') . " pengajuan (Total Pending) yang menunggu verifikasi Bendahara. Pilih tahun untuk memfilter data yang akan diproses.")
                    ->modalSubmitActionLabel("Ya, Terima Semua")
                    ->hidden(fn(): bool => !auth()->user()->hasAnyRole(["super_admin", "Admin", "bendahara"]))
                    ->action(function (array $data) {
                        $count = PengajuanServices::bulkApproveBendahara($data['tahun']);
                        Notification::make()
                            ->success()
                            ->title("Berhasil Menyetujui {$count} Pengajuan")
                            ->body("Semua pengajuan tahun {$data['tahun']} telah diverifikasi Bendahara.")
                            ->send();
                    }),

                Action::make("bulk_approve_revisi_bendahara")
                    ->label(fn() => "Terima Semua Revisi Bendahara (" . PengajuanServices::countPendingRevisiBendahara() . ")")
                    ->icon("heroicon-o-arrow-path")
                    ->color("danger") // Warna merah
                    ->form([
                        Select::make('tahun')
                            ->label('Tahun Pengajuan')
                            ->options(
                                collect(range(now()->year - 2, now()->year + 1))
                                    ->mapWithKeys(fn($year) => [$year => $year])
                            )
                            ->default(now()->year)
                            ->required(),
                    ])
                    ->modalHeading("Terima Semua Revisi Bendahara (Bypass)")
                    ->modalDescription(fn() => "Anda akan menarik paksa " . PengajuanServices::countPendingRevisiBendahara() . " pengajuan yang statusnya 'Perlu Perbaikan' (Ditolak Bendahara) dari meja Pengaju. Pengajuan akan langsung disetujui (verifikasi ok) tanpa menunggu pengaju mengirim ulang. Lanjutkan?")
                    ->modalSubmitActionLabel("Ya, Terima Paksa")
                    ->hidden(fn(): bool => !auth()->user()->hasAnyRole(["super_admin", "Admin", "bendahara"]))
                    ->action(function (array $data) {
                        $count = PengajuanServices::bulkApproveRevisiBendahara($data['tahun']);
                        Notification::make()
                            ->success()
                            ->title("Berhasil Menyetujui {$count} Pengajuan Revisi")
                            ->body("Semua pengajuan revisi tahun {$data['tahun']} telah ditarik dan diverifikasi oleh Bendahara.")
                            ->send();
                    }),
            ])
                ->label("Terima Massal")
                ->icon("heroicon-o-check-badge")
                ->color("warning")
                ->button()
        ];
    }

    protected function getHeaderWidgets(): array {
        return [
            StatsProsesPembayaran::class
        ];
    }

    public function getTabs(): array {
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
