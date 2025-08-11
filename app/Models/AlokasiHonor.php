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
     * Metode terpusat untuk membuat AlokasiHonor.
     * Tidak ada perubahan di sini, logika ini sudah benar.
     */
    public static function createWithRelations(string $mitraIdSobat, string $honorId, float $target): self
    {
        $mitra = Mitra::where('id_sobat', $mitraIdSobat)->first();
        if (!$mitra) {
            throw new \Exception("Mitra dengan ID Sobat {$mitraIdSobat} tidak ditemukan.");
        }

        $honor = Honor::with('kegiatanManmit')->find($honorId);
        if (!$honor || !$honor->kegiatanManmit?->tgl_mulai_pelaksanaan) {
            throw new \Exception("Data Kegiatan/jadwal pada Honor ID {$honorId} tidak lengkap/tidak ditemukan.");
        }

        $tahunKegiatan = Carbon::parse($honor->kegiatanManmit->tgl_mulai_pelaksanaan)->year;
        $isMitraAktif = $mitra->kemitraans()->where('tahun', $tahunKegiatan)->where('status', 'AKTIF')->exists();
        if (!$isMitraAktif) {
            throw new \Exception("Mitra {$mitra->nama_1} (ID Sobat: {$mitraIdSobat}) tidak memiliki kemitraan AKTIF di tahun {$tahunKegiatan}.");
        }

        $mitraIdPrimaryKey = $mitra->id;

        $tanggalMulaiKegiatan = Carbon::parse($honor->kegiatanManmit->tgl_mulai_pelaksanaan);
        $tanggalAkhirKegiatan = Carbon::parse($honor->kegiatanManmit->tgl_akhir_pelaksanaan);
        $tanggalPengajuanSpk = TanggalMerah::getNextWorkDay($tanggalMulaiKegiatan);
        $tanggalPengajuanBast = TanggalMerah::getNextWorkDay($tanggalAkhirKegiatan, -1);

        $existingSpkId = self::where('mitra_id', $mitraIdPrimaryKey)
            ->whereHas('honor.kegiatanManmit', function ($query) use ($tanggalMulaiKegiatan) {
                $query->whereYear('tgl_mulai_pelaksanaan', $tanggalMulaiKegiatan->year)
                    ->whereMonth('tgl_mulai_pelaksanaan', $tanggalMulaiKegiatan->month);
            })
            ->whereNotNull('surat_perjanjian_kerja_id')
            ->value('surat_perjanjian_kerja_id');

        if ($existingSpkId) {
            $suratPerjanjianKerjaId = $existingSpkId;
        } else {
            $nomorSuratSpk = NomorSurat::generateNomorSuratPerjanjianKerja($tanggalPengajuanSpk);
            $suratPerjanjianKerjaId = $nomorSuratSpk->id;
        }

        $existingBastId = self::where('mitra_id', $mitraIdPrimaryKey)->where('honor_id', $honor->id)->whereNotNull('surat_bast_id')->value('surat_bast_id');
        if ($existingBastId) {
            $suratBastId = $existingBastId;
        } else {
            $nomorSuratBast = NomorSurat::generateNomorSuratBast($tanggalPengajuanBast);
            $suratBastId = $nomorSuratBast->id;
        }

        $totalHonor = $honor->harga_per_satuan * $target;

        $alokasi = new self();
        $alokasi->honor_id = $honorId;
        $alokasi->mitra_id = $mitraIdPrimaryKey;
        $alokasi->target_per_satuan_honor = $target;
        $alokasi->total_honor = $totalHonor;
        $alokasi->surat_perjanjian_kerja_id = $suratPerjanjianKerjaId; // Asumsikan variabel ini ada dari logika di atas
        $alokasi->surat_bast_id = $suratBastId; // Asumsikan variabel ini ada dari logika di atas
        $alokasi->tanggal_mulai_perjanjian = $tanggalMulaiKegiatan; // Asumsikan variabel ini ada dari logika di atas
        $alokasi->tanggal_akhir_perjanjian = $tanggalAkhirKegiatan; // Asumsikan variabel ini ada dari logika di atas
        $alokasi->tanggal_penanda_tanganan_spk_oleh_petugas = $tanggalPengajuanSpk; // Asumsikan variabel ini ada dari logika di atas

        // Dengan cara ini, tidak ada kemungkinan kolom 'id' masuk ke dalam proses.
        // Eloquent akan menghasilkan kueri INSERT tanpa kolom 'id',
        // membiarkan database mengisinya dengan AUTO_INCREMENT.
        // $alokasi->save();

        return $alokasi;
    }
}
