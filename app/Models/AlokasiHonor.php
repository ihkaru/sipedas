<?php

namespace App\Models;

use App\Supports\TanggalMerah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Carbon;

class AlokasiHonor extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'target_per_satuan_honor' => 'decimal:2',
        'total_honor' => 'decimal:2',
        'tanggal_mulai_perjanjian' => 'date',
        'tanggal_akhir_perjanjian' => 'date',
        'tanggal_penanda_tanganan_spk_oleh_petugas' => 'date',
    ];

    /**
     * Relasi ke Mitra yang dialokasikan.
     */
    public function mitra(): BelongsTo
    {
        return $this->belongsTo(Mitra::class);
    }

    /**
     * Relasi ke jenis Honor yang dialokasikan.
     */
    public function honor(): BelongsTo
    {
        return $this->belongsTo(Honor::class, 'honor_id');
    }

    /**
     * Relasi ke nomor surat kontrak (Perjanjian Kerja).
     */
    public function kontrak(): BelongsTo
    {
        return $this->belongsTo(NomorSurat::class, 'surat_perjanjian_kerja_id');
    }

    /**
     * Relasi ke nomor surat BAST.
     */
    public function bast(): BelongsTo
    {
        return $this->belongsTo(NomorSurat::class, 'surat_bast_id');
    }

    /**
     * Relasi shortcut untuk mendapatkan Kegiatan Manmit melalui Honor.
     * AlokasiHonor -> Honor -> KegiatanManmit
     */
    public function kegiatanManmit(): HasOneThrough
    {
        return $this->hasOneThrough(
            KegiatanManmit::class,
            Honor::class,
            'id', // Foreign key on honors table...
            'id', // Foreign key on kegiatan_manmits table...
            'honor_id', // Local key on alokasi_honors table...
            'kegiatan_manmit_id' // Local key on honors table...
        );
    }

    /**
     * Boot method untuk mendaftarkan model event.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Validasi kelayakan mitra sebelum disimpan
            $isSensus = $model->honor?->kegiatanManmit?->jenis_kegiatan === 'SENSUS';
            
            $validation = \App\Services\HonorService::validateMitraEligibility(
                $model->mitra_id,
                $model->tanggal_mulai_perjanjian,
                $model->tanggal_akhir_perjanjian,
                $model->total_honor,
                $isSensus,
                $model->id // Exclude self if updating
            );

            if (!$validation['eligible']) {
                throw new \Exception($validation['message']);
            }
        });
    }

    /**
     * Metode terpusat untuk mempersiapkan instance AlokasiHonor.
     */
    public static function createWithRelations(string $idSobat, string $honorId, float $target): self
    {
        $mitra = Mitra::where('id_sobat', $idSobat)->first();
        if (!$mitra) {
            throw new \Exception("Mitra dengan ID Sobat '{$idSobat}' tidak ditemukan.");
        }

        $honor = Honor::with('kegiatanManmit')->find($honorId);
        if (!$honor || !$honor->tanggal_akhir_kegiatan) {
            throw new \Exception("Data tanggal_akhir_kegiatan pada Honor ID {$honorId} tidak lengkap atau tidak ditemukan.");
        }

        // --- SUMBER KEBENARAN: honor.tanggal_akhir_kegiatan ---
        // Kontrak selalu mencakup satu bulan penuh sesuai bulan dari tanggal_akhir_kegiatan.
        // Ini konsisten dengan logika blade template SPK (Pasal 3: startOfMonth s/d endOfMonth).
        // tgl_mulai/akhir_pelaksanaan dari kegiatan_manmits TIDAK digunakan untuk kontrak.
        $tanggalMulaiKontrak = Carbon::parse($honor->tanggal_akhir_kegiatan)->startOfMonth();
        $tanggalAkhirKontrak = Carbon::parse($honor->tanggal_akhir_kegiatan)->endOfMonth();

        // Tanggal administrasi SPK/BAST
        $tanggalPengajuanSpk = TanggalMerah::getNextWorkDay($tanggalMulaiKontrak, -1);
        $tanggalPengajuanBast = TanggalMerah::getNextWorkDay($tanggalAkhirKontrak, -1);

        $totalHonor = $honor->harga_per_satuan * $target;

        // Cek apakah SPK sudah ada untuk mitra di bulan yang sama (untuk nomor surat yang sama)
        $existingSpkId = self::where('mitra_id', $mitra->id)
            ->where(function($q) use ($tanggalMulaiKontrak) {
                $q->whereYear('tanggal_mulai_perjanjian', $tanggalMulaiKontrak->year)
                  ->whereMonth('tanggal_mulai_perjanjian', $tanggalMulaiKontrak->month);
            })
            ->whereNotNull('surat_perjanjian_kerja_id')
            ->value('surat_perjanjian_kerja_id');

        $suratPerjanjianKerjaId = $existingSpkId ?: NomorSurat::generateNomorSuratPerjanjianKerja($tanggalPengajuanSpk)->id;

        $existingBastId = self::where('mitra_id', $mitra->id)
            ->where('honor_id', $honor->id)
            ->whereNotNull('surat_bast_id')
            ->value('surat_bast_id');

        $suratBastId = $existingBastId ?: NomorSurat::generateNomorSuratBast($tanggalPengajuanBast)->id;

        return new self([
            'honor_id' => $honorId,
            'mitra_id' => $mitra->id,
            'target_per_satuan_honor' => $target,
            'total_honor' => $totalHonor,
            'surat_perjanjian_kerja_id' => $suratPerjanjianKerjaId,
            'surat_bast_id' => $suratBastId,
            'tanggal_penanda_tanganan_spk_oleh_petugas' => $tanggalPengajuanSpk,
            'tanggal_mulai_perjanjian' => $tanggalMulaiKontrak,
            'tanggal_akhir_perjanjian' => $tanggalAkhirKontrak,
        ]);
    }
}
