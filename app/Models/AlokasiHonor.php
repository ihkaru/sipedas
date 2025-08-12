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
     * Metode terpusat untuk mempersiapkan instance AlokasiHonor.
     * Metode ini menerima ID Sobat sebagai input, dan secara internal akan
     * menerjemahkannya ke primary key `mitra.id`.
     *
     * @param string $idSobat ID Sobat dari mitra.
     * @param string $honorId ID dari honor.
     * @param float  $target  Target yang diberikan.
     * @return self Instance AlokasiHonor yang siap disimpan.
     * @throws \Exception
     */
    public static function createWithRelations(string $idSobat, string $honorId, float $target): self
    {
        // --- AWAL BAGIAN TERJEMAHAN ---
        // Cari mitra berdasarkan ID Sobat. Gagal jika tidak ditemukan.
        $mitra = Mitra::where('id_sobat', $idSobat)->first();
        if (!$mitra) {
            throw new \Exception("Mitra dengan ID Sobat '{$idSobat}' tidak ditemukan.");
        }
        // Ambil primary key-nya. Inilah yang akan disimpan di database.
        $mitraPrimaryKey = $mitra->id;
        // --- AKHIR BAGIAN TERJEMAHAN ---


        $honor = Honor::with('kegiatanManmit')->find($honorId);
        if (!$honor || !$honor->kegiatanManmit?->tgl_mulai_pelaksanaan || !$honor->kegiatanManmit?->tgl_akhir_pelaksanaan) {
            throw new \Exception("Data Kegiatan/jadwal pada Honor ID {$honorId} tidak lengkap atau tidak ditemukan.");
        }

        // Validasi Kemitraan Aktif
        $tahunKegiatan = Carbon::parse($honor->kegiatanManmit->tgl_mulai_pelaksanaan)->year;
        $isMitraAktif = $mitra->kemitraans()->where('tahun', $tahunKegiatan)->where('status', 'AKTIF')->exists();
        if (!$isMitraAktif) {
            throw new \Exception("Mitra {$mitra->nama_1} (ID Sobat: {$idSobat}) tidak memiliki kemitraan AKTIF di tahun {$tahunKegiatan}.");
        }

        // Logika selanjutnya menggunakan primary key yang sudah kita temukan ($mitraPrimaryKey)
        $tanggalMulaiKegiatan = Carbon::parse($honor->tanggal_akhir_kegiatan)->startOfMonth();
        $tanggalAkhirKegiatan = Carbon::parse($honor->tanggal_akhir_kegiatan);
        $tanggalPengajuanSpk = TanggalMerah::getNextWorkDay($tanggalMulaiKegiatan, -1);
        $tanggalPengajuanBast = TanggalMerah::getNextWorkDay($tanggalAkhirKegiatan, -1);

        // Cek SPK menggunakan $mitraPrimaryKey
        $existingSpkId = self::where('mitra_id', $mitraPrimaryKey)
            ->whereHas('honor.kegiatanManmit', function ($query) use ($tanggalMulaiKegiatan) {
                $query->whereYear('tgl_mulai_pelaksanaan', $tanggalMulaiKegiatan->year)
                    ->whereMonth('tgl_mulai_pelaksanaan', $tanggalMulaiKegiatan->month);
            })
            ->whereNotNull('surat_perjanjian_kerja_id')
            ->value('surat_perjanjian_kerja_id');

        $suratPerjanjianKerjaId = $existingSpkId ?: NomorSurat::generateNomorSuratPerjanjianKerja($tanggalPengajuanSpk)->id;

        // Cek BAST menggunakan $mitraPrimaryKey
        $existingBastId = self::where('mitra_id', $mitraPrimaryKey)
            ->where('honor_id', $honor->id)
            ->whereNotNull('surat_bast_id')
            ->value('surat_bast_id');

        $suratBastId = $existingBastId ?: NomorSurat::generateNomorSuratBast($tanggalPengajuanBast)->id;

        $totalHonor = $honor->harga_per_satuan * $target;

        // Saat membuat instance, kita simpan $mitraPrimaryKey ke kolom `mitra_id`
        $alokasi = new self([
            'honor_id' => $honorId,
            'mitra_id' => $mitraPrimaryKey, // <-- DISIMPAN SEBAGAI PRIMARY KEY
            'target_per_satuan_honor' => $target,
            'total_honor' => $totalHonor,
            'surat_perjanjian_kerja_id' => $suratPerjanjianKerjaId,
            'surat_bast_id' => $suratBastId,
            'tanggal_penanda_tanganan_spk_oleh_petugas' => $tanggalPengajuanSpk,
            'tanggal_mulai_perjanjian' => $tanggalMulaiKegiatan,
            'tanggal_akhir_perjanjian' => $tanggalAkhirKegiatan,
        ]);

        return $alokasi;
    }
}
