<?php

namespace App\Services;

use App\Models\AlokasiHonor;
use App\Models\Honor;
use App\Models\Mitra;
use App\Models\NomorSurat;
use App\Supports\Constants;
use App\Supports\TanggalMerah;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HonorAllocationService
{
    /**
     * Resolusi Mitra berdasarkan ID atau id_sobat.
     */
    public static function resolveMitra(int|string $identifier): ?Mitra
    {
        if (is_numeric($identifier)) {
            $mitra = Mitra::find($identifier);
            if ($mitra) return $mitra;
        }

        return Mitra::where('id_sobat', (string)$identifier)->first();
    }

    /**
     * Resolusi Honor dengan relasi kegiatanManmit.
     */
    public static function resolveHonor(string $honorId): ?Honor
    {
        return Honor::with('kegiatanManmit')->find($honorId);
    }

    /**
     * Validasi kelayakan komprehensif untuk penetapan alokasi honor mitra.
     * Menggabungkan: Status Kemitraan Aktif + Overlap Jadwal Sensus + Limit SBML Bulanan.
     */
    public static function validateEligibility(
        Mitra $mitra,
        Honor $honor,
        float $target,
        ?int $excludeAlokasiId = null
    ): array {
        if (!$honor->tanggal_akhir_kegiatan) {
            return [
                'eligible' => false,
                'message' => "Honor ID {$honor->id} belum memiliki tanggal_akhir_kegiatan.",
            ];
        }

        $tanggalAkhirKegiatan = Carbon::parse($honor->tanggal_akhir_kegiatan);
        $year = $tanggalAkhirKegiatan->year;

        // 1. Validasi Status Kemitraan Aktif pada tahun bersangkutan
        $isMitraAktif = $mitra->kemitraans()
            ->where('tahun', $year)
            ->where('status', 'AKTIF')
            ->exists();

        if (!$isMitraAktif) {
            return [
                'eligible' => false,
                'message' => "Mitra {$mitra->nama_1} ({$mitra->id_sobat}) tidak memiliki status kemitraan aktif pada tahun {$year}.",
            ];
        }

        $startOfMonth = $tanggalAkhirKegiatan->copy()->startOfMonth();
        $endOfMonth = $tanggalAkhirKegiatan->copy()->endOfMonth();
        $totalHonor = (float) $honor->harga_per_satuan * $target;
        $isSensus = ($honor->kegiatanManmit?->jenis_kegiatan === 'SENSUS');

        // 2. Validasi Bentrok Jadwal & Limit SBML via HonorService
        $serviceValidation = HonorService::validateMitraEligibility(
            $mitra->id,
            $startOfMonth,
            $endOfMonth,
            $totalHonor,
            $isSensus,
            $excludeAlokasiId
        );

        if (!$serviceValidation['eligible']) {
            return [
                'eligible' => false,
                'message' => $serviceValidation['message'],
            ];
        }

        return [
            'eligible' => true,
            'message' => 'Mitra memenuhi syarat untuk dialokasikan.',
        ];
    }

    /**
     * Pemeriksaan kelayakan (Dry-run) tanpa menulis ke database.
     * Sangat berguna bagi coding agent atau UI form sebelum submit.
     */
    public static function checkEligibility(int|string $mitraIdentifier, string $honorId, float $target): array
    {
        $mitra = self::resolveMitra($mitraIdentifier);
        if (!$mitra) {
            throw ValidationException::withMessages([
                'mitra_id' => ["Mitra dengan ID/Sobat '{$mitraIdentifier}' tidak ditemukan."],
            ]);
        }

        $honor = self::resolveHonor($honorId);
        if (!$honor) {
            throw ValidationException::withMessages([
                'honor_id' => ["Honor dengan ID '{$honorId}' tidak ditemukan."],
            ]);
        }

        $validation = self::validateEligibility($mitra, $honor, $target);

        $tanggalAkhir = Carbon::parse($honor->tanggal_akhir_kegiatan);
        $startOfMonth = $tanggalAkhir->copy()->startOfMonth();
        $endOfMonth = $tanggalAkhir->copy()->endOfMonth();
        $totalHonor = (float)$honor->harga_per_satuan * $target;

        $remainingBudget = HonorService::getMitraRemainingBudget($mitra->id, $startOfMonth, $endOfMonth);

        return [
            'eligible' => $validation['eligible'],
            'message' => $validation['message'],
            'mitra' => [
                'id' => $mitra->id,
                'id_sobat' => $mitra->id_sobat,
                'nama' => $mitra->nama_1,
            ],
            'honor' => [
                'id' => $honor->id,
                'jabatan' => $honor->jabatan,
                'jenis_honor' => $honor->jenis_honor,
                'satuan' => $honor->satuan_honor,
                'harga_per_satuan' => (float)$honor->harga_per_satuan,
                'kegiatan_manmit' => [
                    'id' => $honor->kegiatanManmit?->id,
                    'nama' => $honor->kegiatanManmit?->nama,
                    'jenis_kegiatan' => $honor->kegiatanManmit?->jenis_kegiatan,
                ],
            ],
            'kalkulasi' => [
                'target' => $target,
                'total_honor' => $totalHonor,
                'tanggal_mulai_kontrak' => $startOfMonth->toDateString(),
                'tanggal_akhir_kontrak' => $endOfMonth->toDateString(),
            ],
            'sisa_limit_sbml' => $remainingBudget,
        ];
    }

    /**
     * Eksekusi alokasi honor dan penerbitan nomor SPK & BAST secara atomik.
     *
     * @throws ValidationException
     */
    public static function allocate(int|string $mitraIdentifier, string $honorId, float $target): AlokasiHonor
    {
        $mitra = self::resolveMitra($mitraIdentifier);
        if (!$mitra) {
            throw ValidationException::withMessages([
                'mitra_id' => ["Mitra dengan ID/Sobat '{$mitraIdentifier}' tidak ditemukan."],
            ]);
        }

        $honor = self::resolveHonor($honorId);
        if (!$honor) {
            throw ValidationException::withMessages([
                'honor_id' => ["Honor dengan ID '{$honorId}' tidak ditemukan."],
            ]);
        }

        // Validasi Pre-flight SEBELUM generate nomor surat ke DB
        $validation = self::validateEligibility($mitra, $honor, $target);
        if (!$validation['eligible']) {
            throw ValidationException::withMessages([
                'target' => [$validation['message']],
            ]);
        }

        $tanggalAkhirKegiatan = Carbon::parse($honor->tanggal_akhir_kegiatan);
        $tanggalMulaiKontrak  = $tanggalAkhirKegiatan->copy()->startOfMonth();
        $tanggalAkhirKontrak  = $tanggalAkhirKegiatan->copy()->endOfMonth();

        // SPK: ditandatangani H-1 hari kerja sebelum awal bulan kontrak
        $tanggalPengajuanSpk  = TanggalMerah::getNextWorkDay($tanggalMulaiKontrak->copy(), -1);
        // BAST: ditandatangani pada/sebelum hari kerja akhir kegiatan
        $tanggalPengajuanBast = TanggalMerah::getNextWorkDay($tanggalAkhirKegiatan->copy(), -1);

        $totalHonor = (float)$honor->harga_per_satuan * $target;
        $isSensus = ($honor->kegiatanManmit?->jenis_kegiatan === 'SENSUS');

        return DB::transaction(function () use (
            $mitra,
            $honor,
            $target,
            $totalHonor,
            $isSensus,
            $tanggalMulaiKontrak,
            $tanggalAkhirKontrak,
            $tanggalPengajuanSpk,
            $tanggalPengajuanBast
        ) {
            // 1. Resolusi Nomor Surat SPK (Surat Perjanjian Kerja)
            if ($isSensus) {
                // SENSUS: kontrak mandiri per kegiatan
                $existingSpkId = AlokasiHonor::where('mitra_id', $mitra->id)
                    ->where('honor_id', $honor->id)
                    ->whereNotNull('surat_perjanjian_kerja_id')
                    ->value('surat_perjanjian_kerja_id');
            } else {
                // SURVEI: satu SPK per mitra per bulan kalender
                $existingSpkId = AlokasiHonor::where('mitra_id', $mitra->id)
                    ->whereYear('tanggal_mulai_perjanjian', $tanggalMulaiKontrak->year)
                    ->whereMonth('tanggal_mulai_perjanjian', $tanggalMulaiKontrak->month)
                    ->whereHas('honor.kegiatanManmit', function ($q) {
                        $q->where('jenis_kegiatan', '!=', 'SENSUS');
                    })
                    ->whereNotNull('surat_perjanjian_kerja_id')
                    ->value('surat_perjanjian_kerja_id');
            }

            $suratPerjanjianKerjaId = $existingSpkId
                ?: NomorSurat::generateNomorSuratPerjanjianKerja($tanggalPengajuanSpk)->id;

            // 2. Resolusi Nomor Surat BAST (Berita Acara Serah Terima)
            $existingBastId = AlokasiHonor::where('mitra_id', $mitra->id)
                ->where('honor_id', $honor->id)
                ->whereNotNull('surat_bast_id')
                ->value('surat_bast_id');

            $suratBastId = $existingBastId
                ?: NomorSurat::generateNomorSuratBast($tanggalPengajuanBast)->id;

            // 3. Simpan AlokasiHonor
            $alokasi = new AlokasiHonor([
                'honor_id' => $honor->id,
                'mitra_id' => $mitra->id,
                'target_per_satuan_honor' => $target,
                'total_honor' => $totalHonor,
                'surat_perjanjian_kerja_id' => $suratPerjanjianKerjaId,
                'surat_bast_id' => $suratBastId,
                'tanggal_penanda_tanganan_spk_oleh_petugas' => $tanggalPengajuanSpk,
                'tanggal_mulai_perjanjian' => $tanggalMulaiKontrak,
                'tanggal_akhir_perjanjian' => $tanggalAkhirKontrak,
            ]);

            $alokasi->save();

            return $alokasi->load(['mitra', 'honor.kegiatanManmit', 'kontrak', 'bast']);
        });
    }

    /**
     * Eksekusi alokasi massal (batch).
     */
    public static function allocateBatch(array $allocations): array
    {
        $results = [];
        $errors = [];

        foreach ($allocations as $index => $item) {
            try {
                $mitraIdentifier = $item['mitra_id'] ?? $item['id_sobat'] ?? null;
                $honorId = $item['honor_id'] ?? null;
                $target = (float)($item['target_per_satuan_honor'] ?? $item['target'] ?? 0);

                if (!$mitraIdentifier || !$honorId || $target <= 0) {
                    throw new \InvalidArgumentException("Item indeks #{$index} memiliki data tidak lengkap.");
                }

                $alokasi = self::allocate($mitraIdentifier, $honorId, $target);
                $results[] = $alokasi;
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'item' => $item,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'success_count' => count($results),
            'failed_count' => count($errors),
            'results' => $results,
            'errors' => $errors,
        ];
    }
}
