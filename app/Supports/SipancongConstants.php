<?php

namespace App\Supports;

/**
 * Class SipancongConstants
 *
 * Berisi konstanta untuk ID dari tabel referensi Sipancong.
 * Ini membantu menghindari "magic numbers" dalam kode dan membuatnya lebih mudah dibaca.
 */
final class SipancongConstants
{
    // Dari tabel sp_posisi_dokumen
    const POSISI_PENGAJU                  = 1;
    const POSISI_PPK                      = 2;
    const POSISI_PPSPM                    = 3;
    const POSISI_BENDAHARA                = 4;
    const POSISI_SIAP_CETAK               = 5; // Opsional jika akan digunakan
    const POSISI_SELESAI                  = 6;

    // Dari tabel sp_status_pengajuan
    const STATUS_DIAJUKAN                 = 1;
    const STATUS_DISETUJUI_DENGAN_CATATAN = 2;
    const STATUS_DITUNDA                  = 3;
    const STATUS_DITOLAK                  = 4;
    const STATUS_DISETUJUI_TANPA_CATATAN  = 5;

    // Dari tabel sp_status_pembayaran
    const PEMBAYARAN_SUDAH_CMS            = 1;
    const PEMBAYARAN_SUDAH_LS             = 2;
    const PEMBAYARAN_BELUM_DOK_FISIK      = 3;
    const PEMBAYARAN_PROSES_CATAT_SPBY    = 4;
    const PEMBAYARAN_SUDAH_CAIR_DIPROSES  = 5; // Asumsi ini sama dengan Tunai
    const PEMBAYARAN_PROSES_CATAT_LS      = 6;
    const PEMBAYARAN_SUDAH_TUNAI          = 7;

    // Kelompok Status untuk kemudahan pengecekan
    public static function isDisetujui($statusId): bool
    {
        return in_array($statusId, [self::STATUS_DISETUJUI_DENGAN_CATATAN, self::STATUS_DISETUJUI_TANPA_CATATAN]);
    }

    public static function isSelesaiDibayar($statusId): bool
    {
        return in_array($statusId, [self::PEMBAYARAN_SUDAH_CMS, self::PEMBAYARAN_SUDAH_LS, self::PEMBAYARAN_SUDAH_TUNAI, self::PEMBAYARAN_SUDAH_CAIR_DIPROSES]);
    }
}
