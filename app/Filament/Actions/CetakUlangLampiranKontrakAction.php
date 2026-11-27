<?php

namespace App\Filament\Actions;

use App\Services\LampiranKontrakService;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;

class CetakUlangLampiranKontrakAction
{
    /**
     * @return array<int, string>
     */
    public static function monthOptions(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function yearOptions(): array
    {
        $options = [];
        foreach (range(now()->year + 1, now()->year - 5) as $year) {
            $options[$year] = (string) $year;
        }

        return $options;
    }

    public static function make(
        ?Closure $defaultMonth = null,
        ?Closure $defaultYear = null,
        string $name = 'cetak_ulang_lampiran_kontrak',
        string $label = 'Cetak Ulang Lampiran Kontrak',
    ): Action {
        return Action::make($name)
            ->label($label)
            ->icon('heroicon-o-printer')
            ->color('info')
            ->modalHeading('Cetak Ulang Semua Lampiran Kontrak Mitra')
            ->modalDescription('Mencetak hanya halaman Lampiran 1 (Daftar Kegiatan) dari seluruh kontrak SPK pada bulan terpilih berdasarkan Tanggal Akhir Kegiatan.')
            ->modalSubmitActionLabel('Cetak Lampiran')
            ->modalIcon('heroicon-o-printer')
            ->form([
                Select::make('bulan')
                    ->label('Bulan (Tanggal Akhir Kegiatan)')
                    ->options(self::monthOptions())
                    ->default(fn () => (int) ($defaultMonth ? $defaultMonth() : now()->month))
                    ->required()
                    ->live(),

                Select::make('tahun')
                    ->label('Tahun')
                    ->options(self::yearOptions())
                    ->default(fn () => (int) ($defaultYear ? $defaultYear() : now()->year))
                    ->required()
                    ->live(),

                Select::make('jenis_kegiatan')
                    ->label('Jenis Kegiatan')
                    ->options(LampiranKontrakService::jenisKegiatanOptions())
                    ->default(LampiranKontrakService::JENIS_ALL)
                    ->required()
                    ->live(),

                Select::make('format')
                    ->label('Format Output')
                    ->options([
                        'pdf' => 'File PDF (.pdf) — Siap Unduh / Print',
                        'html' => 'Tampilan Cetak Browser (HTML)',
                    ])
                    ->default('pdf')
                    ->required(),

                Placeholder::make('preview_ringkasan')
                    ->label('Pratinjau Dokumen')
                    ->content(function (Get $get): HtmlString {
                        $bulan = (int) ($get('bulan') ?? now()->month);
                        $tahun = (int) ($get('tahun') ?? now()->year);
                        $jenis = (string) ($get('jenis_kegiatan') ?? LampiranKontrakService::JENIS_ALL);

                        $summary = LampiranKontrakService::getSummary($tahun, $bulan, $jenis);
                        $namaBulan = self::monthOptions()[$bulan] ?? (string) $bulan;
                        $labelJenis = LampiranKontrakService::jenisKegiatanLabel($jenis);

                        if ($summary['total_lampiran'] === 0) {
                            return new HtmlString(
                                '<div class="rounded-lg border border-amber-300 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700 p-3 text-sm text-amber-800 dark:text-amber-200">'
                                .'Tidak ditemukan kontrak SPK untuk <b>'.e($labelJenis).'</b> pada periode <b>'.e($namaBulan).' '.$tahun.'</b>.'
                                .'</div>'
                            );
                        }

                        $totalHonorFormatted = 'Rp '.number_format($summary['total_honor'], 0, ',', '.');

                        return new HtmlString(
                            '<div class="rounded-lg border border-sky-300 bg-sky-50 dark:bg-sky-900/20 dark:border-sky-700 p-3 text-sm text-sky-900 dark:text-sky-100">'
                            .'Siap mencetak <b>'.$summary['total_lampiran'].' Lampiran SPK</b> '
                            .'('.$summary['total_mitra'].' mitra, '.$summary['total_alokasi'].' alokasi kegiatan, total '.$totalHonorFormatted.') '
                            .'untuk <b>'.e($labelJenis).'</b> periode <b>'.e($namaBulan).' '.$tahun.'</b>.'
                            .'</div>'
                        );
                    }),
            ])
            ->action(function (array $data, mixed $livewire): void {
                $bulan = (int) $data['bulan'];
                $tahun = (int) $data['tahun'];
                $jenis = LampiranKontrakService::normalizeJenisKegiatan($data['jenis_kegiatan'] ?? null);

                $summary = LampiranKontrakService::getSummary($tahun, $bulan, $jenis);
                $namaBulan = self::monthOptions()[$bulan] ?? (string) $bulan;

                if ($summary['total_lampiran'] === 0) {
                    Notification::make()
                        ->title('Tidak Ada Lampiran Kontrak')
                        ->body("Tidak ditemukan kontrak SPK untuk filter yang dipilih pada {$namaBulan} {$tahun}.")
                        ->warning()
                        ->send();

                    return;
                }

                $params = [
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                    'jenis_kegiatan' => $jenis,
                ];

                if (($data['format'] ?? 'pdf') === 'pdf') {
                    $params['format'] = 'pdf';
                }

                $url = route('cetak.kontrak.lampiran', $params);

                if (is_object($livewire) && method_exists($livewire, 'js')) {
                    $livewire->js('window.open('.Js::from($url).', "_blank")');
                }

                Notification::make()
                    ->title('Halaman Cetak Lampiran Dibuka')
                    ->body("{$summary['total_lampiran']} lampiran SPK periode {$namaBulan} {$tahun} siap dicetak.")
                    ->success()
                    ->actions([
                        Action::make('buka_halaman_cetak')
                            ->label('Buka Halaman Cetak')
                            ->url($url)
                            ->openUrlInNewTab(),
                    ])
                    ->send();
            });
    }
}
